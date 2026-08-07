<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tahun = 2026;
$bulan = 1;
echo json_encode(\App\Models\MasterPeriode::calculateDefaultWeeks($tahun, $bulan), JSON_PRETTY_PRINT);
