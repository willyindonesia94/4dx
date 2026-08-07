<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BreakdownLm;
use App\Models\MasterPeriode;
use Carbon\Carbon;

$breakdowns = BreakdownLm::all();
$monthlyTargets = [];
$weeklyTargets = [];
$deletedCount = 0;
$updatedBoundsCount = 0;

foreach ($breakdowns as $b) {
    if (!$b->bulan || !$b->tahun) continue;
    
    $start = Carbon::parse($b->periode_start);
    $end = Carbon::parse($b->periode_end);
    $isMonthly = $start->diffInDays($end) >= 20;
    
    $key = $b->lm_id . '_' . $b->unit_id . '_' . $b->bulan . '_' . $b->tahun;
    
    if ($isMonthly) {
        if (!isset($monthlyTargets[$key])) {
            $monthlyTargets[$key] = $b;
        } else {
            if ($b->id > $monthlyTargets[$key]->id) {
                $monthlyTargets[$key]->delete();
                $monthlyTargets[$key] = $b;
            } else {
                $b->delete();
            }
            $deletedCount++;
        }
    } else {
        $weeklyKey = $key . '_' . $b->periode_start;
        if (!isset($weeklyTargets[$weeklyKey])) {
            $weeklyTargets[$weeklyKey] = $b;
        } else {
            if ($b->id > $weeklyTargets[$weeklyKey]->id) {
                $weeklyTargets[$weeklyKey]->delete();
                $weeklyTargets[$weeklyKey] = $b;
            } else {
                $b->delete();
            }
            $deletedCount++;
        }
    }
}

foreach ($monthlyTargets as $b) {
    $master = MasterPeriode::where('tahun', $b->tahun)->where('bulan', $b->bulan)->first();
    if ($master) {
        $cStart = Carbon::create($b->tahun, $b->bulan, 1);
        $cEnd = $cStart->copy()->endOfMonth();
        
        $newStart = $master->start_m1 ?: $cStart->format('Y-m-d');
        $newEnd = $master->start_m5 ?: ($master->start_m4 ?: $cEnd->format('Y-m-d'));
        
        if ($b->periode_start != $newStart || $b->periode_end != $newEnd) {
            $b->periode_start = $newStart;
            $b->periode_end = $newEnd;
            $b->save();
            $updatedBoundsCount++;
        }
    }
}

echo "Deleted $deletedCount duplicates. Updated bounds for $updatedBoundsCount monthly targets.";
