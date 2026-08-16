<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = collect([
    [
        'primary' => 'Tenaga Listrik',
        'km' => '4DX',
        '20' => '2026',
        'no' => 'WIG-1',
        'type' => '4DX',
        'indikator_kinerja_2026' => 'WIG 1 - PENJUALAN',
        'polaritas' => '3',
        'satuan' => 'GWh',
        'unit' => 'INDRAMAYU',
        'target_januari' => 100,
        'target_desember' => 1200
    ]
]);

try {
    $importer = new \App\Imports\WigMassImport();
    $importer->collection($rows);
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

$bw = \App\Models\BreakdownWig::where('unit_id', 87)->get();
echo "UP3 Indramayu count: " . $bw->count() . "\n";
