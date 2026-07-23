<?php

use App\Models\MasterUnit;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$locations = [
    // UP3 Bandung
    'ULP Bandung Utara' => ['lat' => -6.8856, 'lng' => 107.6111],
    'ULP Bandung Selatan' => ['lat' => -6.9535, 'lng' => 107.6186],
    'ULP Bandung Barat' => ['lat' => -6.9200, 'lng' => 107.5750],
    'ULP Bandung Timur' => ['lat' => -6.9350, 'lng' => 107.6650],
    'ULP Ujung Berung' => ['lat' => -6.9150, 'lng' => 107.6980],
    'ULP Priangan' => ['lat' => -6.9147, 'lng' => 107.6098],

    // UP3 Majalaya
    'ULP Majalaya' => ['lat' => -7.0381, 'lng' => 107.7554],
    'ULP Ciparay' => ['lat' => -7.0398, 'lng' => 107.7126],
    'ULP Rancaekek' => ['lat' => -6.9634, 'lng' => 107.7600],
    'ULP Banjaran' => ['lat' => -7.0450, 'lng' => 107.5878],

    // UP3 Sumedang
    'ULP Sumedang Kota' => ['lat' => -6.8570, 'lng' => 107.9209],
    'ULP Tanjungsari' => ['lat' => -6.8835, 'lng' => 107.8090],
    'ULP Jatinangor' => ['lat' => -6.9285, 'lng' => 107.7725],

    // UP3 Cimahi
    'ULP Cimahi Kota' => ['lat' => -6.8741, 'lng' => 107.5458],
    'ULP Cimahi Selatan' => ['lat' => -6.9015, 'lng' => 107.5350],
    'ULP Padalarang' => ['lat' => -6.8402, 'lng' => 107.4721],
    'ULP Lembang' => ['lat' => -6.8149, 'lng' => 107.6186],
    'ULP Cililin' => ['lat' => -6.9450, 'lng' => 107.4560],

    // UP3 Garut
    'ULP Garut Kota' => ['lat' => -7.2148, 'lng' => 107.9042],
    'ULP Tarogong' => ['lat' => -7.1950, 'lng' => 107.8920],
    'ULP Cibatu' => ['lat' => -7.0980, 'lng' => 107.9950],
    'ULP Pameungpeuk' => ['lat' => -7.6496, 'lng' => 107.6974],

    // UP3 Tasikmalaya
    'ULP Tasikmalaya Kota' => ['lat' => -7.3274, 'lng' => 108.2232],
    'ULP Singaparna' => ['lat' => -7.3503, 'lng' => 108.1082],
    'ULP Rajapolah' => ['lat' => -7.2280, 'lng' => 108.1880],
    'ULP Karangnunggal' => ['lat' => -7.6401, 'lng' => 108.1408],
    'ULP Banjar' => ['lat' => -7.3752, 'lng' => 108.5327],

    // UP3 Cianjur
    'ULP Cianjur Kota' => ['lat' => -6.8228, 'lng' => 107.1394],
    'ULP Cipanas' => ['lat' => -6.7357, 'lng' => 107.0392],
    'ULP Ciranjang' => ['lat' => -6.8290, 'lng' => 107.2660],
    'ULP Sukanagara' => ['lat' => -7.1080, 'lng' => 107.1120],
    'ULP Tanggeung' => ['lat' => -7.3320, 'lng' => 107.1260],

    // UP3 Sukabumi
    'ULP Sukabumi Kota' => ['lat' => -6.9237, 'lng' => 106.9287],
    'ULP Cibadak' => ['lat' => -6.8851, 'lng' => 106.7797],
    'ULP Cicurug' => ['lat' => -6.7860, 'lng' => 106.7830],
    'ULP Pelabuhan Ratu' => ['lat' => -7.0267, 'lng' => 106.5517],

    // UP3 Purwakarta
    'ULP Purwakarta Kota' => ['lat' => -6.5398, 'lng' => 107.4452],
    'ULP Plered' => ['lat' => -6.6450, 'lng' => 107.3910],
    'ULP Sadang' => ['lat' => -6.5180, 'lng' => 107.4640],
    'ULP Subang' => ['lat' => -6.5684, 'lng' => 107.7618],

    // UP3 Bekasi
    'ULP Bekasi Kota' => ['lat' => -6.2383, 'lng' => 106.9756],
    'ULP Babelan' => ['lat' => -6.1680, 'lng' => 107.0320],
    'ULP Medan Satria' => ['lat' => -6.1960, 'lng' => 106.9890],
    'ULP Mustika Jaya' => ['lat' => -6.3020, 'lng' => 107.0350],
    'ULP Bantar Gebang' => ['lat' => -6.3150, 'lng' => 106.9850],
    'ULP Pondok Gede' => ['lat' => -6.2820, 'lng' => 106.9200],

    // UP3 Bogor
    'ULP Bogor Kota' => ['lat' => -6.5971, 'lng' => 106.8060],
    'ULP Bogor Barat' => ['lat' => -6.5750, 'lng' => 106.7620],
    'ULP Bogor Timur' => ['lat' => -6.6180, 'lng' => 106.8280],
    'ULP Leuwiliang' => ['lat' => -6.5735, 'lng' => 106.6263],
    'ULP Pakuan' => ['lat' => -6.6020, 'lng' => 106.8100],

    // UP3 Depok
    'ULP Depok Kota' => ['lat' => -6.4025, 'lng' => 106.7942],
    'ULP Cimanggis' => ['lat' => -6.3760, 'lng' => 106.8620],
    'ULP Sawangan' => ['lat' => -6.4070, 'lng' => 106.7580],
    'ULP Bojong Gede' => ['lat' => -6.4880, 'lng' => 106.7950],
    'ULP Cibinong' => ['lat' => -6.4716, 'lng' => 106.8488],

    // UP3 Gunung Putri
    'ULP Gunung Putri' => ['lat' => -6.4380, 'lng' => 106.8920],
    'ULP Cileungsi' => ['lat' => -6.4020, 'lng' => 106.9680],
    'ULP Jonggol' => ['lat' => -6.4760, 'lng' => 107.0540],

    // UP3 Cirebon
    'ULP Cirebon Kota' => ['lat' => -6.7155, 'lng' => 108.5524],
    'ULP Sumber' => ['lat' => -6.7620, 'lng' => 108.4780],
    'ULP Jatibarang' => ['lat' => -6.4760, 'lng' => 108.3090],
    'ULP Ciledug' => ['lat' => -6.9030, 'lng' => 108.7360],
    'ULP Indramayu' => ['lat' => -6.3262, 'lng' => 108.3200],
    'ULP Haurgeulis' => ['lat' => -6.4580, 'lng' => 107.9400],

    // UP3 Karawang
    'ULP Karawang Kota' => ['lat' => -6.3050, 'lng' => 107.2974],
    'ULP Kosambi' => ['lat' => -6.3520, 'lng' => 107.3850],
    'ULP Cikampek' => ['lat' => -6.4060, 'lng' => 107.4560],
    'ULP Rengasdengklok' => ['lat' => -6.1550, 'lng' => 107.2950],
];

$updatedCount = 0;
foreach ($locations as $name => $coords) {
    $unit = MasterUnit::where('name', $name)->first();
    if ($unit) {
        $unit->latitude = $coords['lat'];
        $unit->longitude = $coords['lng'];
        $unit->save();
        $updatedCount++;
    }
}

echo "Successfully updated " . $updatedCount . " ULP locations with real coordinates!\n";
