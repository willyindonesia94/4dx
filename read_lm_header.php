<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = storage_path('app/private/logs/uploaded_lm.xlsx');
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
print_r($rows[0]);
print_r($rows[1]);
print_r($rows[2]);
