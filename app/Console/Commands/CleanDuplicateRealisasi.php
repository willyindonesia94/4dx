<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Realisasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanDuplicateRealisasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'realisasi:clean-duplicates {--dry-run : Only report duplicates without deleting them} {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate daily realization entries on the same date for the same ULP and LM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info("Scanning for duplicate realisasi records...");

        $groups = Realisasi::selectRaw('lm_id, unit_id, DATE(tanggal_input) as tgl, count(*) as count')
            ->groupBy('lm_id', 'unit_id', DB::raw('DATE(tanggal_input)'))
            ->having('count', '>', 1)
            ->get();

        if ($groups->isEmpty()) {
            $this->info("No duplicate realisasi records found. Everything is clean!");
            return 0;
        }

        $this->warn("Found {$groups->count()} group(s) of duplicates.");

        $totalDeleted = 0;
        $tableData = [];

        foreach ($groups as $g) {
            $records = Realisasi::with(['lm', 'unit', 'user'])
                ->where('lm_id', $g->lm_id)
                ->where('unit_id', $g->unit_id)
                ->whereDate('tanggal_input', $g->tgl)
                ->get();

            // Determine which record to keep:
            // 1. Non-zero realization first
            // 2. Real uploaded photo/document (starts with 'bukti_realisasi/') over 'Upload Massal' or empty
            // 3. Most recently updated
            $sorted = $records->sort(function ($a, $b) {
                $aIsZero = (float)$a->angka_realisasi == 0.0;
                $bIsZero = (float)$b->angka_realisasi == 0.0;
                if ($aIsZero !== $bIsZero) {
                    return $aIsZero ? 1 : -1;
                }

                $aHasFile = !empty($a->bukti_file) && str_starts_with($a->bukti_file, 'bukti_realisasi/');
                $bHasFile = !empty($b->bukti_file) && str_starts_with($b->bukti_file, 'bukti_realisasi/');
                if ($aHasFile !== $bHasFile) {
                    return $aHasFile ? -1 : 1;
                }

                return strcmp($b->updated_at, $a->updated_at);
            })->values();

            $keep = $sorted->first();
            $duplicates = $sorted->slice(1);

            foreach ($duplicates as $del) {
                $tableData[] = [
                    'Date' => $g->tgl,
                    'Unit' => $del->unit->name ?? "Unit {$g->unit_id}",
                    'LM' => $del->lm->judul_lm ?? "LM {$g->lm_id}",
                    'Kept ID' => $keep->id . " (Angka: {$keep->angka_realisasi})",
                    'Deleted ID' => $del->id . " (Angka: {$del->angka_realisasi})",
                    'Reason' => $del->tanggal_input === $keep->tanggal_input ? 'Exact double click' : 'Same-day duplicate',
                ];

                if (!$isDryRun) {
                    // Only delete file if it's different from the kept file
                    if ($del->bukti_file && $del->bukti_file !== $keep->bukti_file && str_starts_with($del->bukti_file, 'bukti_realisasi/')) {
                        Storage::disk('public')->delete($del->bukti_file);
                    }
                    $del->delete();
                    $totalDeleted++;
                }
            }
        }

        $this->table(['Date', 'Unit', 'Lead Measure', 'Kept Record', 'Duplicate Record', 'Details'], $tableData);

        if ($isDryRun) {
            $this->warn("DRY RUN COMPLETED: " . count($tableData) . " duplicate records would be deleted. Run without --dry-run to delete them.");
        } else {
            $this->info("SUCCESS: {$totalDeleted} duplicate realisasi records have been permanently cleaned up.");
        }

        return 0;
    }
}
