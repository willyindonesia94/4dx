<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SesiWig;
use App\Models\MasterPeriode;
use App\Models\BreakdownLm;
use App\Models\SesiWigKomitmen;

class AutoFillKomitmen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-fill-komitmen {sesi_wig_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengisi form komitmen dengan default target mingguan untuk user yang belum menyimpan komitmen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sesiId = $this->argument('sesi_wig_id');
        $sesi = SesiWig::find($sesiId);

        if (!$sesi) {
            $this->error("Sesi WIG dengan ID {$sesiId} tidak ditemukan!");
            return;
        }

        $this->info("Menjalankan auto-fill komitmen untuk sesi: " . $sesi->nama_sesi);

        $masterPeriode = MasterPeriode::where('tahun', $sesi->tahun)->where('bulan', $sesi->bulan)->first();
        $targetStart = null;
        if ($masterPeriode) {
            $col = 'start_m' . $sesi->minggu_ke;
            $targetStart = $masterPeriode->$col;
        }

        if (!$targetStart) {
            $this->error("Gagal menentukan tanggal mulai minggu ke-{$sesi->minggu_ke} untuk bulan {$sesi->bulan} tahun {$sesi->tahun}.");
            return;
        }

        $this->info("Target Start Mingguan: " . $targetStart);

        $breakdowns = BreakdownLm::where('periode_start', '<=', $targetStart)
            ->where('periode_end', '>=', $targetStart)
            ->whereRaw('DATEDIFF(periode_end, periode_start) <= 7')
            ->get();

        $count = 0;
        foreach ($breakdowns as $b) {
            $exists = SesiWigKomitmen::where('sesi_wig_id', $sesi->id)
                ->where('lm_id', $b->lm_id)
                ->where('unit_id', $b->unit_id)
                ->exists();

            if (!$exists) {
                SesiWigKomitmen::create([
                    'sesi_wig_id' => $sesi->id,
                    'lm_id' => $b->lm_id,
                    'unit_id' => $b->unit_id,
                    'komitmen' => $b->angka_target
                ]);
                $count++;
            }
        }

        $this->info("Selesai! Berhasil mengisi otomatis {$count} komitmen yang sebelumnya kosong.");
    }
}
