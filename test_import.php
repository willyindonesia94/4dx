<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = collect([
    [
        'no' => '1',
        'judul_wig' => 'WIG 1 - Penjualan',
        'satuan' => 'GWh',
        'unit' => 'BANDUNG',
        'target_januari' => 10
    ],
    [
        'no' => null,
        'judul_wig' => null,
        'satuan' => null,
        'unit' => 'INDRAMAYU',
        'target_januari' => 20
    ],
    [
        'no' => null,
        'judul_wig' => null,
        'satuan' => null,
        'unit' => 'CIKARANG',
        'target_januari' => 30
    ]
]);

try {
    $importer = new \App\Imports\WigMassImport();
    $importer->collection($rows);
    echo "Import success\n";
} catch (\Exception $e) {
    echo "Import failed: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

