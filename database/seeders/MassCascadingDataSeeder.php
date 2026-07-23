<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Target;
use App\Models\Metric;
use App\Models\Location;
use App\Models\User;
use App\Models\Realization;
use Carbon\Carbon;

class MassCascadingDataSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::whereHas('roles', function($q) { $q->where('name', 'superadmin'); })->first();
        if (!$superadmin) return;

        $uid = Location::where('type', 'UID')->first();
        if (!$uid) return;

        // 1. Create ULPs for other UP3s if they don't exist
        $up3Bogor = Location::firstOrCreate(['name' => 'UP3 Bogor', 'type' => 'UP3', 'parent_id' => $uid->id]);
        $up3Cirebon = Location::firstOrCreate(['name' => 'UP3 Cirebon', 'type' => 'UP3', 'parent_id' => $uid->id]);
        $up3Tasik = Location::firstOrCreate(['name' => 'UP3 Tasikmalaya', 'type' => 'UP3', 'parent_id' => $uid->id]);

        $additionalUlps = [
            'UP3 Bogor' => ['ULP Bogor Kota', 'ULP Cibinong', 'ULP Depok Kota'],
            'UP3 Cirebon' => ['ULP Cirebon Kota', 'ULP Sumber', 'ULP Jatibarang'],
            'UP3 Tasikmalaya' => ['ULP Tasikmalaya Kota', 'ULP Singaparna', 'ULP Banjar']
        ];

        foreach ($additionalUlps as $up3Name => $ulps) {
            $up3 = Location::where('name', $up3Name)->first();
            foreach ($ulps as $ulpName) {
                Location::firstOrCreate([
                    'name' => $ulpName,
                    'type' => 'ULP',
                    'parent_id' => $up3->id
                ]);
            }
        }

        $allUp3s = Location::where('type', 'UP3')->get();
        $allUlps = Location::where('type', 'ULP')->get();

        // 2. Define the Target Scenarios
        $scenarios = [
            'Jaringan' => [
                'lag' => 'Durasi Padam Pelanggan (SAIDI)',
                'lead' => 'Eksekusi Titik ROW (Right of Way) Pohon',
                'wig_name' => 'Menurunkan Durasi Padam Pelanggan (SAIDI) Jabar ke 500 Menit',
                'wig_target' => 500,
                'sub_wig_template' => 'Menurunkan SAIDI {up3} menjadi 500 Menit',
                'lead_template' => 'Eksekusi ROW Pohon {ulp} 50 Titik/Minggu',
                'lead_target' => 50,
                'realization_range' => [5, 15] // daily randomized value
            ],
            'TE' => [
                'lag' => 'Persentase Penurunan Saldo Tunggakan',
                'lead' => 'Kunjungan Langsung Penagihan & Pemutusan',
                'wig_name' => 'Zero Tunggakan se-Jawa Barat',
                'wig_target' => 100,
                'sub_wig_template' => 'Zero Tunggakan di wilayah {up3}',
                'lead_template' => 'Kunjungan Langsung 30 Pelanggan/Hari di {ulp}',
                'lead_target' => 30,
                'realization_range' => [20, 35]
            ],
            'PP' => [
                'lag' => 'Rata-rata Durasi Penyambungan Baru (SLA Pasang)',
                'lead' => 'Penyelesaian Berkas Survey Lapangan < 24 Jam',
                'wig_name' => 'SLA Pasang Baru Jabar Maksimal 3 Hari Kerja',
                'wig_target' => 3,
                'sub_wig_template' => 'SLA Pasang Baru {up3} Maksimal 3 Hari Kerja',
                'lead_template' => 'Survey Lapangan < 24 Jam di {ulp} (100%)',
                'lead_target' => 100,
                'realization_range' => [80, 100]
            ],
            'KSA' => [
                'lag' => 'Persentase Realisasi Efisiensi Anggaran (Opex)',
                'lead' => 'Tingkat Kepatuhan Pengisian Logbook Kerja Staf',
                'wig_name' => 'Efisiensi Opex Jabar 100%',
                'wig_target' => 100,
                'sub_wig_template' => 'Efisiensi Opex {up3} 100%',
                'lead_template' => 'Kepatuhan Logbook Staf {ulp} (100%)',
                'lead_target' => 100,
                'realization_range' => [75, 100]
            ],
        ];

        foreach ($scenarios as $div => $data) {
            $lagMetric = Metric::where('name', $data['lag'])->first();
            $leadMetric = Metric::where('name', $data['lead'])->first();

            if (!$lagMetric || !$leadMetric) continue;

            // Create or get WIG Utama
            $wig = Target::firstOrCreate([
                'name' => $data['wig_name'],
                'metric_id' => $lagMetric->id,
                'location_id' => $uid->id,
                'type' => 'WIG Utama'
            ], [
                'target_value' => $data['wig_target'],
                'period' => '2026',
                'created_by' => $superadmin->id
            ]);

            // Create Sub-WIGs for all UP3s
            foreach ($allUp3s as $up3) {
                $subWigName = str_replace('{up3}', $up3->name, $data['sub_wig_template']);
                $subWig = Target::firstOrCreate([
                    'name' => $subWigName,
                    'metric_id' => $lagMetric->id,
                    'location_id' => $up3->id,
                    'parent_id' => $wig->id,
                    'type' => 'Sub-WIG'
                ], [
                    'target_value' => $data['wig_target'],
                    'period' => '2026',
                    'created_by' => $superadmin->id
                ]);

                // Create Lead Measures for all ULPs under this UP3
                $ulps = Location::where('type', 'ULP')->where('parent_id', $up3->id)->get();
                foreach ($ulps as $ulp) {
                    $leadName = str_replace('{ulp}', $ulp->name, $data['lead_template']);
                    $leadMeasure = Target::firstOrCreate([
                        'name' => $leadName,
                        'metric_id' => $leadMetric->id,
                        'location_id' => $ulp->id,
                        'parent_id' => $subWig->id,
                        'type' => 'Lead Measure'
                    ], [
                        'target_value' => $data['lead_target'],
                        'period' => 'Mingguan/Harian',
                        'created_by' => $superadmin->id
                    ]);

                    // Generate dummy realizations for the past 7 days
                    for ($i = 6; $i >= 0; $i--) {
                        $date = Carbon::now()->subDays($i)->format('Y-m-d');
                        $value = rand($data['realization_range'][0], $data['realization_range'][1]);

                        Realization::firstOrCreate([
                            'target_id' => $leadMeasure->id,
                            'report_date' => $date
                        ], [
                            'realization_value' => $value,
                            'created_by' => $superadmin->id
                        ]);
                    }
                }
            }
        }
    }
}
