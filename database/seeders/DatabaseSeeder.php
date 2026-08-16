<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use App\Models\MasterWig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            'Super Admin',
            'Perencanaan UID',
            'General Manager UID',
            'Bidang UID',
            'Sub Bidang UID',
            'Perencanaan UP3',
            'UP2D',
            'UP2K',
            'Manager UP3',
            'Bidang UP3',
            'Manager ULP',
            'Staff ULP',
            'Team Leader ULP'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Create Default Master Satuans
        $satuans = ['%', 'Jam', 'Rupiah', 'Titik', 'Pelanggan', 'Kali'];
        $satuanIds = [];
        foreach ($satuans as $satuan) {
            $s = MasterSatuan::firstOrCreate(['name' => $satuan]);
            $satuanIds[$satuan] = $s->id;
        }

        // 3. Create Sample Units
        $uidJabar = MasterUnit::firstOrCreate(['name' => 'PLN UID Jawa Barat', 'type' => 'UID', 'parent_id' => null]);
        $up3Bandung = MasterUnit::firstOrCreate(['name' => 'UP3 Bandung', 'type' => 'UP3', 'parent_id' => $uidJabar->id]);
        $ulpUtara = MasterUnit::firstOrCreate(['name' => 'ULP Bandung Utara', 'type' => 'ULP', 'parent_id' => $up3Bandung->id]);

        // 4. Create Default Users for testing
        $superAdminUser = User::firstOrCreate([
            'email' => 'superadmin@pln.co.id'
        ], [
            'name' => 'Super Administrator',
            'password' => Hash::make('password'),
            'role_name' => 'Super Admin',
            'unit_id' => $uidJabar->id,
            'matrix_group_id' => 'ALL'
        ]);
        $superAdminUser->assignRole('Super Admin');

        $divisiUid = User::firstOrCreate([
            'email' => 'divisi.jaringan@uid.pln.co.id'
        ], [
            'name' => 'Divisi Jaringan UID',
            'password' => Hash::make('password'),
            'role_name' => 'Divisi UID',
            'unit_id' => $uidJabar->id,
            'matrix_group_id' => 'JARINGAN'
        ]);
        $divisiUid->assignRole('Divisi UID');

        $tlUlp = User::firstOrCreate([
            'email' => 'tl.teknik.utara@ulp.pln.co.id'
        ], [
            'name' => 'TL Teknik ULP Utara',
            'password' => Hash::make('password'),
            'role_name' => 'Team Leader ULP',
            'unit_id' => $ulpUtara->id,
            'matrix_group_id' => 'JARINGAN'
        ]);
        $tlUlp->assignRole('Team Leader ULP');

        // 5. Seed 5 Default WIGs
        $wigs = [
            ['judul' => 'WIG 1 - PENJUALAN', 'angka_target' => 1500000000.00, 'satuan' => 'Rupiah', 'divisi' => 'NIAGA'],
            ['judul' => 'WIG 2 - PIUTANG', 'angka_target' => 0.00, 'satuan' => 'Rupiah', 'divisi' => 'KEUANGAN'],
            ['judul' => 'WIG 3 - SUSUT', 'angka_target' => 5.00, 'satuan' => '%', 'divisi' => 'TRANSAKSI ENERGI'],
            ['judul' => 'WIG 4 - KEANDALAN', 'angka_target' => 200.00, 'satuan' => 'Jam', 'divisi' => 'JARINGAN'], // SAIDI
            ['judul' => 'WIG 5 - KESELAMATAN', 'angka_target' => 0.00, 'satuan' => 'Kali', 'divisi' => 'K3L'], // Zero Accident
        ];

        foreach ($wigs as $w) {
            MasterWig::firstOrCreate(
                ['judul' => $w['judul']],
                [
                    'unit_pemilik_id' => $uidJabar->id,
                    'angka_target' => $w['angka_target'],
                    'satuan_id' => $satuanIds[$w['satuan']],
                    'divisi' => $w['divisi'],
                ]
            );
        }

        // 6. Seed Lead Measures based on WIGs
        $wigPenjualan = MasterWig::where('judul', 'WIG 1 - PENJUALAN')->first();
        $wigPiutang = MasterWig::where('judul', 'WIG 2 - PIUTANG')->first();
        $wigSusut = MasterWig::where('judul', 'WIG 3 - SUSUT')->first();
        $wigKeandalan = MasterWig::where('judul', 'WIG 4 - KEANDALAN')->first();
        $wigKeselamatan = MasterWig::where('judul', 'WIG 5 - KESELAMATAN')->first();

        $lms = [
            // PENJUALAN
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-1 Intimasi Pelanggan'],
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-2 Penyambungan Pelanggan TM dan TR'],
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-3 Penyambungan non perluasan dengan fasa yang sama'],
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-4 Pencapaian Close Won 33 KVA ke Atas'],
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-5 Peningkatan Kali Transaksi PLN Mobile'],
            ['wig_id' => $wigPenjualan->id, 'judul' => 'LM-6 Tindak Lanjut Rating Negative Service O2O'],
            // PIUTANG
            ['wig_id' => $wigPiutang->id, 'judul' => 'LM-1 Pelunasan Pelanggan Potensial'],
            ['wig_id' => $wigPiutang->id, 'judul' => 'LM-2 Penyelesaian Tagihan Kogol 3 dan 4 sebelum tgl 20 tiap bulan'],
            ['wig_id' => $wigPiutang->id, 'judul' => 'LM-3 Pemutusan Pelanggan Menunggak'],
            // SUSUT
            ['wig_id' => $wigSusut->id, 'judul' => 'LM-1 Melaksanakan Smart P2TL'],
            ['wig_id' => $wigSusut->id, 'judul' => 'LM-2 Melaksanakan Pemeriksaan Pelanggan TM dan TR Potensial'],
            ['wig_id' => $wigSusut->id, 'judul' => 'LM-3 Melaksanakan Penggantian kWh meter'],
            // KEANDALAN
            ['wig_id' => $wigKeandalan->id, 'judul' => 'LM-1 Assesment SKTM'],
            ['wig_id' => $wigKeandalan->id, 'judul' => 'LM-2 ROW Pohon (Pangkas/Tebang)'],
            ['wig_id' => $wigKeandalan->id, 'judul' => 'LM-3 Perbaikan Pentanahan Proteksi Petir'],
            ['wig_id' => $wigKeandalan->id, 'judul' => 'LM-4 Pemasangan Proteksi Binatang'],
            ['wig_id' => $wigKeandalan->id, 'judul' => 'LM-5 Perbaikan Aset RC Eksisting'],
            // KESELAMATAN
            ['wig_id' => $wigKeselamatan->id, 'judul' => 'LM-1 MELAKSANAKAN SAFETY PATROL'],
            ['wig_id' => $wigKeselamatan->id, 'judul' => 'LM-2 Pelaksanaan Pengawasan Pekerjaan menggunakan CCV'],
            ['wig_id' => $wigKeselamatan->id, 'judul' => 'LM-3 Pengecekan Kesesuaian Working Permit'],
            ['wig_id' => $wigKeselamatan->id, 'judul' => 'LM-4 Edukasi K2/K3 Kepada Masyarakat'],
        ];

        foreach ($lms as $lmData) {
            \App\Models\MasterLm::firstOrCreate(
                ['judul_lm' => $lmData['judul'], 'wig_id' => $lmData['wig_id']],
                [
                    'tujuan_unit_role' => 'TL ULP',
                    'periode_start' => now()->startOfMonth(),
                    'periode_end' => now()->endOfYear(),
                    'angka_target' => 100, // placeholder
                    'satuan_id' => $satuanIds['%'],
                    'polaritas' => 'positif',
                ]
            );
        }
    }
}
