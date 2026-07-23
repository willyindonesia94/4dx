<?php

use App\Models\MasterUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set existing users to random new unit id or null to prevent foreign key errors (though we might not have foreign keys configured tightly)
User::query()->update(['unit_id' => null]);

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
MasterUnit::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

$uid = MasterUnit::create(['name' => 'UID Jawa Barat', 'type' => 'UID', 'parent_id' => null]);

$up3s = [
    'UP3 Bandung' => ['ULP Bandung Utara', 'ULP Bandung Selatan', 'ULP Bandung Barat', 'ULP Bandung Timur', 'ULP Ujung Berung', 'ULP Priangan'],
    'UP3 Majalaya' => ['ULP Majalaya', 'ULP Ciparay', 'ULP Rancaekek', 'ULP Banjaran'],
    'UP3 Sumedang' => ['ULP Sumedang Kota', 'ULP Tanjungsari', 'ULP Jatinangor'],
    'UP3 Cimahi' => ['ULP Cimahi Kota', 'ULP Cimahi Selatan', 'ULP Padalarang', 'ULP Lembang', 'ULP Cililin'],
    'UP3 Garut' => ['ULP Garut Kota', 'ULP Tarogong', 'ULP Cibatu', 'ULP Pameungpeuk'],
    'UP3 Tasikmalaya' => ['ULP Tasikmalaya Kota', 'ULP Singaparna', 'ULP Rajapolah', 'ULP Karangnunggal', 'ULP Banjar'],
    'UP3 Cianjur' => ['ULP Cianjur Kota', 'ULP Cipanas', 'ULP Ciranjang', 'ULP Sukanagara', 'ULP Tanggeung'],
    'UP3 Sukabumi' => ['ULP Sukabumi Kota', 'ULP Cibadak', 'ULP Cicurug', 'ULP Pelabuhan Ratu'],
    'UP3 Purwakarta' => ['ULP Purwakarta Kota', 'ULP Plered', 'ULP Sadang', 'ULP Subang'],
    'UP3 Bekasi' => ['ULP Bekasi Kota', 'ULP Babelan', 'ULP Medan Satria', 'ULP Mustika Jaya', 'ULP Bantar Gebang', 'ULP Pondok Gede'],
    'UP3 Bogor' => ['ULP Bogor Kota', 'ULP Bogor Barat', 'ULP Bogor Timur', 'ULP Leuwiliang', 'ULP Pakuan'],
    'UP3 Depok' => ['ULP Depok Kota', 'ULP Cimanggis', 'ULP Sawangan', 'ULP Bojong Gede', 'ULP Cibinong'],
    'UP3 Gunung Putri' => ['ULP Gunung Putri', 'ULP Cileungsi', 'ULP Jonggol'],
    'UP3 Cirebon' => ['ULP Cirebon Kota', 'ULP Sumber', 'ULP Jatibarang', 'ULP Ciledug', 'ULP Indramayu', 'ULP Haurgeulis'],
    'UP3 Karawang' => ['ULP Karawang Kota', 'ULP Kosambi', 'ULP Cikampek', 'ULP Rengasdengklok'],
];

foreach($up3s as $up3Name => $ulps) {
    $up3 = MasterUnit::create(['name' => $up3Name, 'type' => 'UP3', 'parent_id' => $uid->id]);
    foreach($ulps as $ulpName) {
        MasterUnit::create(['name' => $ulpName, 'type' => 'ULP', 'parent_id' => $up3->id]);
    }
}

echo "Seeded " . count($up3s) . " UP3s and their ULPs successfully!\n";
