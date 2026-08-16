<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = storage_path('app/private/logs/uploaded_lm.xlsx');
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

foreach ($rows as $index => $row) {
    // Column 8 is UNIT
    if (isset($row[8]) && str_contains(strtolower($row[8]), 'indramayu')) {
        echo "Row " . ($index + 1) . ":\n";
        print_r($row);
    }
}
