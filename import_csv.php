<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HistoricalDataImport;

try {
    $filePath = __DIR__ . '/data_lm_jan.csv';
    echo "Importing " . $filePath . "\n";
    Excel::import(new HistoricalDataImport(2026, 1), $filePath);
    echo "Import successful!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
