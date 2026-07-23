<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterUnit;
use App\Models\BreakdownLm;
use App\Models\Realisasi;

class GenerateDummyUlpData extends Command
{
    protected $signature = 'app:generate-dummy-ulp';
    protected $description = 'Generate dummy target and realisasi data for ULPs based on UP3 data for Jan and Mar';

    public function handle()
    {
        $this->info('Starting ULP dummy data generation...');

        $up3s = MasterUnit::where('type', 'UP3')->get();
        $monthsToProcess = [1, 3]; // January and March

        $totalTargetsInserted = 0;
        $totalRealisasisInserted = 0;

        foreach ($up3s as $up3) {
            $ulps = MasterUnit::where('parent_id', $up3->id)->get();
            $ulpCount = $ulps->count();

            if ($ulpCount === 0) {
                continue;
            }

            // 1. Process BreakdownLms (Targets)
            $breakdowns = BreakdownLm::where('unit_id', $up3->id)
                ->where(function($q) use ($monthsToProcess) {
                    foreach ($monthsToProcess as $month) {
                        $q->orWhereMonth('periode_start', $month);
                    }
                })->get();

            foreach ($breakdowns as $breakdown) {
                // Remove existing dummy data first to avoid duplicates if run multiple times
                BreakdownLm::whereIn('unit_id', $ulps->pluck('id'))
                    ->where('lm_id', $breakdown->lm_id)
                    ->where('periode_start', $breakdown->periode_start)
                    ->delete();

                $targetPerUlp = $breakdown->angka_target / $ulpCount;

                foreach ($ulps as $ulp) {
                    BreakdownLm::create([
                        'lm_id' => $breakdown->lm_id,
                        'unit_id' => $ulp->id,
                        'periode_start' => $breakdown->periode_start,
                        'periode_end' => $breakdown->periode_end,
                        'angka_target' => $targetPerUlp,
                        'satuan_id' => $breakdown->satuan_id,
                        'bidang' => $breakdown->bidang,
                    ]);
                    $totalTargetsInserted++;
                }
            }

            // 2. Process Realisasis
            $realisasis = Realisasi::where('unit_id', $up3->id)
                ->where(function($q) use ($monthsToProcess) {
                    foreach ($monthsToProcess as $month) {
                        $q->orWhereMonth('tanggal_input', $month);
                    }
                })->get();

            foreach ($realisasis as $realisasi) {
                // Remove existing dummy realisasis
                Realisasi::whereIn('unit_id', $ulps->pluck('id'))
                    ->where('lm_id', $realisasi->lm_id)
                    ->where('tanggal_input', $realisasi->tanggal_input)
                    ->where('keterangan_tambahan', 'like', '%Dummy%') // Saftey measure
                    ->delete();

                $realisasiPerUlp = $realisasi->angka_realisasi / $ulpCount;

                foreach ($ulps as $ulp) {
                    Realisasi::create([
                        'lm_id' => $realisasi->lm_id,
                        'unit_id' => $ulp->id,
                        'user_id' => $realisasi->user_id,
                        'angka_realisasi' => $realisasiPerUlp,
                        'tanggal_input' => $realisasi->tanggal_input,
                        'keterangan_tambahan' => 'Dummy ULP from UP3',
                        'bukti_file' => null,
                    ]);
                    $totalRealisasisInserted++;
                }
            }
        }

        $this->info("Successfully inserted $totalTargetsInserted breakdown targets and $totalRealisasisInserted realisasis for ULPs.");
        return 0;
    }
}
