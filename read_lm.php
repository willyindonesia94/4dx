<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = storage_path('app/private/logs/uploaded_lm.xlsx');
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

$output = "";
foreach (array_slice($rows, 0, 100) as $index => $row) {
    if (isset($row[2]) && str_contains(strtolower($row[2]), 'indramayu')) {
        $output .= "Row " . ($index + 1) . ": " . implode(" | ", $row) . "\n";
    }
}
file_put_contents('indramayu_lm_rows.txt', $output);
echo "Done";
