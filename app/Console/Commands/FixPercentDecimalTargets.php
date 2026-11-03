<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BreakdownLm;
use App\Models\MasterLm;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\DB;

class FixPercentDecimalTargets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'target:fix-percent-decimal {--dry-run : Only report records without modifying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix percentage targets that were imported as Excel decimal (<= 1.0) and recalculate UP3/UID averages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info("Scanning for Breakdown LM targets with satuan '%' and target <= 1.0...");

        $anomalies = BreakdownLm::where('satuan_id', 1)
            ->where('angka_target', '>', 0)
            ->where('angka_target', '<=', 1.0)
            ->with(['unit', 'lm'])
            ->get();

        if ($anomalies->isEmpty()) {
            $this->info("No anomalous percentage targets found. Everything is clean!");
            return 0;
        }

        $this->warn("Found {$anomalies->count()} record(s) with decimal percentage targets.");

        $tableData = [];
        $affectedMonths = [];

        foreach ($anomalies as $b) {
            $tableData[] = [
                'ID' => $b->id,
                'Unit' => $b->unit ? $b->unit->name : "Unit #{$b->unit_id}",
                'Type' => $b->unit ? $b->unit->type : '-',
                'LM' => $b->lm ? substr($b->lm->judul_lm, 0, 35) . '...' : "LM #{$b->lm_id}",
                'Bulan/Tahun' => "{$b->bulan}/{$b->tahun}",
                'Old Target' => $b->angka_target,
                'New Target' => round($b->angka_target * 100, 2),
            ];

            $monthKey = "{$b->bulan}_{$b->tahun}_{$b->lm_id}";
            $affectedMonths[$monthKey] = [
                'bulan' => $b->bulan,
                'tahun' => $b->tahun,
                'lm_id' => $b->lm_id,
            ];
        }

        $this->table(['ID', 'Unit', 'Type', 'LM', 'Bulan/Tahun', 'Old Target', 'New Target'], $tableData);

        if ($isDryRun) {
            $this->info("[Dry Run] No records updated.");
            return 0;
        }

        DB::beginTransaction();
        try {
            foreach ($anomalies as $b) {
                $b->update([
                    'angka_target' => round($b->angka_target * 100, 2)
                ]);
            }

            // Recalculate UP3 & UID averages for affected LM and periods
            $uidUnit = MasterUnit::where('type', 'UID')->first();
            $up3Units = MasterUnit::where('type', 'UP3')->with('children')->get();

            foreach ($affectedMonths as $meta) {
                $bulan = $meta['bulan'];
                $tahun = $meta['tahun'];
                $lmId = $meta['lm_id'];

                $periodes = BreakdownLm::where('lm_id', $lmId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->select('periode_start', 'periode_end')
                    ->distinct()
                    ->get();

                foreach ($periodes as $periode) {
                    $pStart = $periode->periode_start;
                    $pEnd = $periode->periode_end;

                    foreach ($up3Units as $up3) {
                        $ulpIds = $up3->children->pluck('id')->toArray();
                        $childTargets = BreakdownLm::where('lm_id', $lmId)
                            ->where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->where('periode_start', $pStart)
                            ->where('periode_end', $pEnd)
                            ->whereIn('unit_id', $ulpIds)
                            ->pluck('angka_target');

                        if ($childTargets->count() > 0) {
                            $up3Target = round($childTargets->avg(), 2);
                            BreakdownLm::where('lm_id', $lmId)
                                ->where('unit_id', $up3->id)
                                ->where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->where('periode_start', $pStart)
                                ->where('periode_end', $pEnd)
                                ->update(['angka_target' => $up3Target]);
                        }
                    }

                    if ($uidUnit) {
                        $up3Ids = $up3Units->pluck('id')->toArray();
                        $up3Targets = BreakdownLm::where('lm_id', $lmId)
                            ->where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->where('periode_start', $pStart)
                            ->where('periode_end', $pEnd)
                            ->whereIn('unit_id', $up3Ids)
                            ->pluck('angka_target');

                        if ($up3Targets->count() > 0) {
                            $uidTarget = round($up3Targets->avg(), 2);
                            BreakdownLm::where('lm_id', $lmId)
                                ->where('unit_id', $uidUnit->id)
                                ->where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->where('periode_start', $pStart)
                                ->where('periode_end', $pEnd)
                                ->update(['angka_target' => $uidTarget]);
                        }
                    }
                }
            }

            DB::commit();
            $this->info("Successfully updated {$anomalies->count()} record(s) and recalculated UP3/UID averages!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to update records: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
