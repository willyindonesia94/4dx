<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BreakdownLm;
use Carbon\Carbon;

$breakdowns = BreakdownLm::whereNull('bulan')->orWhereNull('tahun')->get();
$count = 0;

foreach ($breakdowns as $b) {
    $start = Carbon::parse($b->periode_start);
    $end = Carbon::parse($b->periode_end);
    
    $bulan = null;
    $tahun = null;
    
    if ($start->diffInDays($end) >= 20) {
        // Target bulanan (01 - 31) -> ambil dari bulan mulai
        $bulan = $start->month;
        $tahun = $start->year;
    } else {
        // Target mingguan. Cari titik tengah minggu tersebut.
        $mid = $start->copy()->addDays(3);
        $bulan = $mid->month;
        $tahun = $mid->year;
    }
    
    $b->bulan = $bulan;
    $b->tahun = $tahun;
    $b->save();
    $count++;
}
echo "Updated $count records";
