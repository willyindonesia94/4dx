<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tahun = 2026;
$bulan = 1;

$carbonStart = \Carbon\Carbon::create($tahun, $bulan, 1);
$carbonEnd = $carbonStart->copy()->endOfMonth();
$masterWeeks = \App\Models\MasterPeriode::getWeekDates($tahun, $bulan);
$startBulan = $masterWeeks['target_m1'] ? $masterWeeks['target_m1']['start'] : $carbonStart->format('Y-m-d');
$endWeek = isset($masterWeeks['target_m5']) && $masterWeeks['target_m5'] ? 'target_m5' : 'target_m4';
$endBulan = $masterWeeks[$endWeek] ? $masterWeeks[$endWeek]['end'] : $carbonEnd->format('Y-m-d');
$weeks = [
    'bulanan' => ['start' => $startBulan, 'end' => $endBulan]
];
if ($masterWeeks['target_m1']) $weeks['minggu_1'] = $masterWeeks['target_m1'];
if ($masterWeeks['target_m2']) $weeks['minggu_2'] = $masterWeeks['target_m2'];
if ($masterWeeks['target_m3']) $weeks['minggu_3'] = $masterWeeks['target_m3'];
if ($masterWeeks['target_m4']) $weeks['minggu_4'] = $masterWeeks['target_m4'];
if ($masterWeeks['target_m5']) $weeks['minggu_5'] = $masterWeeks['target_m5'];

echo json_encode($weeks, JSON_PRETTY_PRINT);
