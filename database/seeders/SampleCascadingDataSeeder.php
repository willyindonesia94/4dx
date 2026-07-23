<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Target;
use App\Models\Metric;
use App\Models\Location;
use App\Models\User;
use App\Models\Realization;
use Carbon\Carbon;

class SampleCascadingDataSeeder extends Seeder
{
    public function run(): void
    {
        $uid = Location::where('type', 'UID')->first();
        $up3 = Location::where('type', 'UP3')->first(); // UP3 Bandung
        $ulp = Location::where('type', 'ULP')->first(); // ULP Bandung Utara

        $superadmin = User::whereHas('roles', function($q) { $q->where('name', 'superadmin'); })->first();
        if (!$uid || !$up3 || !$ulp || !$superadmin) return;

        // --- 1. Divisi Jaringan / Teknik ---
        $lagJaringan = Metric::where('name', 'Durasi Padam Pelanggan (SAIDI)')->first();
        $leadJaringan = Metric::where('name', 'Eksekusi Titik ROW (Right of Way) Pohon')->first();

        if ($lagJaringan && $leadJaringan) {
            $wigJaringan = Target::create([
                'name' => 'Menurunkan Durasi Padam Pelanggan (SAIDI) se-Jawa Barat ke angka 500 Menit/Pelanggan',
                'metric_id' => $lagJaringan->id,
                'location_id' => $uid->id,
                'target_value' => 500,
                'type' => 'WIG Utama',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $subWigJaringan = Target::create([
                'name' => 'Menurunkan SAIDI UP3 Bandung menjadi 500 Menit',
                'metric_id' => $lagJaringan->id,
                'location_id' => $up3->id,
                'parent_id' => $wigJaringan->id,
                'target_value' => 500,
                'type' => 'Sub-WIG',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $leadTargetJaringan = Target::create([
                'name' => 'Eksekusi 50 Titik ROW Pohon di area rawan setiap minggu',
                'metric_id' => $leadJaringan->id,
                'location_id' => $ulp->id,
                'parent_id' => $subWigJaringan->id,
                'target_value' => 50,
                'type' => 'Lead Measure',
                'period' => 'Minggu 1',
                'created_by' => $superadmin->id
            ]);

            // Dummy Realizations
            Realization::create([
                'target_id' => $leadTargetJaringan->id,
                'report_date' => Carbon::now()->subDays(2),
                'realization_value' => 15,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetJaringan->id,
                'report_date' => Carbon::now()->subDays(1),
                'realization_value' => 25,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetJaringan->id,
                'report_date' => Carbon::now(),
                'realization_value' => 10,
                'created_by' => $superadmin->id
            ]);
        }

        // --- 2. Transaksi Energi (TE) ---
        $lagTe = Metric::where('name', 'Persentase Penurunan Saldo Tunggakan')->first();
        $leadTe = Metric::where('name', 'Kunjungan Langsung Penagihan & Pemutusan')->first();

        if ($lagTe && $leadTe) {
            $wigTe = Target::create([
                'name' => 'Mencapai Penurunan Saldo Tunggakan Jabar hingga 100% (Zero Tunggakan)',
                'metric_id' => $lagTe->id,
                'location_id' => $uid->id,
                'target_value' => 100,
                'type' => 'WIG Utama',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $subWigTe = Target::create([
                'name' => 'Zero Tunggakan di wilayah UP3 Bandung',
                'metric_id' => $lagTe->id,
                'location_id' => $up3->id,
                'parent_id' => $wigTe->id,
                'target_value' => 100,
                'type' => 'Sub-WIG',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $leadTargetTe = Target::create([
                'name' => 'Kunjungan Langsung ke 30 Pelanggan Menunggak Setiap Hari',
                'metric_id' => $leadTe->id,
                'location_id' => $ulp->id,
                'parent_id' => $subWigTe->id,
                'target_value' => 30,
                'type' => 'Lead Measure',
                'period' => 'Minggu 1',
                'created_by' => $superadmin->id
            ]);

            // Dummy Realizations
            Realization::create([
                'target_id' => $leadTargetTe->id,
                'report_date' => Carbon::now()->subDays(2),
                'realization_value' => 28,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetTe->id,
                'report_date' => Carbon::now()->subDays(1),
                'realization_value' => 32,
                'created_by' => $superadmin->id
            ]);
        }

        // --- 3. Pelayanan Pelanggan (PP) ---
        $lagPp = Metric::where('name', 'Rata-rata Durasi Penyambungan Baru (SLA Pasang)')->first();
        $leadPp = Metric::where('name', 'Penyelesaian Berkas Survey Lapangan < 24 Jam')->first();

        if ($lagPp && $leadPp) {
            $wigPp = Target::create([
                'name' => 'Mempercepat SLA Pasang Baru Maksimal 3 Hari Kerja',
                'metric_id' => $lagPp->id,
                'location_id' => $uid->id,
                'target_value' => 3,
                'type' => 'WIG Utama',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $subWigPp = Target::create([
                'name' => 'SLA Pasang Baru UP3 Bandung Maksimal 3 Hari Kerja',
                'metric_id' => $lagPp->id,
                'location_id' => $up3->id,
                'parent_id' => $wigPp->id,
                'target_value' => 3,
                'type' => 'Sub-WIG',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $leadTargetPp = Target::create([
                'name' => 'Menyelesaikan 100% Berkas Survey Lapangan di Bawah 24 Jam',
                'metric_id' => $leadPp->id,
                'location_id' => $ulp->id,
                'parent_id' => $subWigPp->id,
                'target_value' => 100,
                'type' => 'Lead Measure',
                'period' => 'Minggu 1',
                'created_by' => $superadmin->id
            ]);

            // Dummy Realizations
            Realization::create([
                'target_id' => $leadTargetPp->id,
                'report_date' => Carbon::now()->subDays(2),
                'realization_value' => 85,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetPp->id,
                'report_date' => Carbon::now()->subDays(1),
                'realization_value' => 95,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetPp->id,
                'report_date' => Carbon::now(),
                'realization_value' => 100,
                'created_by' => $superadmin->id
            ]);
        }

        // --- 4. Keuangan, SDM & ADM (KSA) ---
        $lagKsa = Metric::where('name', 'Persentase Realisasi Efisiensi Anggaran (Opex)')->first();
        $leadKsa = Metric::where('name', 'Tingkat Kepatuhan Pengisian Logbook Kerja Staf')->first();

        if ($lagKsa && $leadKsa) {
            $wigKsa = Target::create([
                'name' => 'Mencapai Efisiensi Anggaran Opex 100% Sesuai RKA 2026',
                'metric_id' => $lagKsa->id,
                'location_id' => $uid->id,
                'target_value' => 100,
                'type' => 'WIG Utama',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $subWigKsa = Target::create([
                'name' => 'Efisiensi Anggaran Opex UP3 Bandung 100%',
                'metric_id' => $lagKsa->id,
                'location_id' => $up3->id,
                'parent_id' => $wigKsa->id,
                'target_value' => 100,
                'type' => 'Sub-WIG',
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            $leadTargetKsa = Target::create([
                'name' => 'Mengawasi 100% Staf Mengisi Logbook Kerja Mingguan',
                'metric_id' => $leadKsa->id,
                'location_id' => $ulp->id,
                'parent_id' => $subWigKsa->id,
                'target_value' => 100,
                'type' => 'Lead Measure',
                'period' => 'Minggu 1',
                'created_by' => $superadmin->id
            ]);

            // Dummy Realizations
            Realization::create([
                'target_id' => $leadTargetKsa->id,
                'report_date' => Carbon::now()->subDays(2),
                'realization_value' => 80,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetKsa->id,
                'report_date' => Carbon::now()->subDays(1),
                'realization_value' => 90,
                'created_by' => $superadmin->id
            ]);
            Realization::create([
                'target_id' => $leadTargetKsa->id,
                'report_date' => Carbon::now(),
                'realization_value' => 95,
                'created_by' => $superadmin->id
            ]);
        }
    }
}
