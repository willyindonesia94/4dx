<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Metric;

class StandardMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Jaringan / Teknik
        $jaringan = Division::where('name', 'Jaringan')->first();
        if ($jaringan) {
            Metric::firstOrCreate([
                'name' => 'Durasi Padam Pelanggan (SAIDI)',
                'division_id' => $jaringan->id,
            ], [
                'type' => 'Lagging',
                'unit' => 'Menit/Pelanggan',
                'polarity' => 'Negative',
            ]);

            Metric::firstOrCreate([
                'name' => 'Eksekusi Titik ROW (Right of Way) Pohon',
                'division_id' => $jaringan->id,
            ], [
                'type' => 'Leading',
                'unit' => 'Titik/Minggu',
                'polarity' => 'Positive',
            ]);
        }

        // 2. Transaksi Energi (TE)
        $te = Division::where('name', 'Transaksi Energi')->first();
        if ($te) {
            Metric::firstOrCreate([
                'name' => 'Persentase Penurunan Saldo Tunggakan',
                'division_id' => $te->id,
            ], [
                'type' => 'Lagging',
                'unit' => '%',
                'polarity' => 'Positive',
            ]);

            Metric::firstOrCreate([
                'name' => 'Kunjungan Langsung Penagihan & Pemutusan',
                'division_id' => $te->id,
            ], [
                'type' => 'Leading',
                'unit' => 'Pelanggan/Hari',
                'polarity' => 'Positive',
            ]);
        }

        // 3. Pelayanan Pelanggan (PP) -> 'Niaga dan Pelayanan Pelanggan'
        $pp = Division::where('name', 'Niaga dan Pelayanan Pelanggan')->first();
        if ($pp) {
            Metric::firstOrCreate([
                'name' => 'Rata-rata Durasi Penyambungan Baru (SLA Pasang)',
                'division_id' => $pp->id,
            ], [
                'type' => 'Lagging',
                'unit' => 'Hari Kerja',
                'polarity' => 'Negative',
            ]);

            Metric::firstOrCreate([
                'name' => 'Penyelesaian Berkas Survey Lapangan < 24 Jam',
                'division_id' => $pp->id,
            ], [
                'type' => 'Leading',
                'unit' => '%',
                'polarity' => 'Positive',
            ]);
        }

        // 4. Keuangan, SDM & ADM (KSA) -> 'Keuangan dan Umum'
        $ksa = Division::where('name', 'Keuangan dan Umum')->first();
        if ($ksa) {
            Metric::firstOrCreate([
                'name' => 'Persentase Realisasi Efisiensi Anggaran (Opex)',
                'division_id' => $ksa->id,
            ], [
                'type' => 'Lagging',
                'unit' => '%',
                'polarity' => 'Positive',
            ]);

            Metric::firstOrCreate([
                'name' => 'Tingkat Kepatuhan Pengisian Logbook Kerja Staf',
                'division_id' => $ksa->id,
            ], [
                'type' => 'Leading',
                'unit' => '%/Minggu',
                'polarity' => 'Positive',
            ]);
        }
    }
}
