<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BreakdownLm;
use App\Models\MasterPeriode;
use Carbon\Carbon;

$breakdowns = BreakdownLm::all();
$deletedCount = 0;
$updatedCount = 0;

foreach ($breakdowns as $b) {
    if (!$b->periode_start || !$b->periode_end) continue;
    
    $start = Carbon::parse($b->periode_start);
    $end = Carbon::parse($b->periode_end);
    $diff = $start->diffInDays($end);
    
    // We should determine the month. If it's a weird date like 05 Jan to 01 Feb, we use the start date's month
    // But wait, what if it was meant for February? Usually start date determines it.
    $year = $start->year;
    $month = $start->month;
    
    $weeks = MasterPeriode::getWeekDates($year, $month);
    
    // Check if it's monthly
    if ($diff >= 20) {
        $newStart = Carbon::create($year, $month, 1)->format('Y-m-d');
        $newEnd = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        
        if ($b->periode_start !== $newStart || $b->periode_end !== $newEnd) {
            // Check for duplicate monthly
            $exists = BreakdownLm::where('lm_id', $b->lm_id)
                    ->where('unit_id', $b->unit_id)
                    ->where('bidang', $b->bidang)
                    ->where('periode_start', $newStart)
                    ->where('periode_end', $newEnd)
                    ->where('id', '!=', $b->id)
                    ->first();
            
            if ($exists) {
                $b->delete();
                $deletedCount++;
            } else {
                $b->periode_start = $newStart;
                $b->periode_end = $newEnd;
                $b->save();
                $updatedCount++;
            }
        }
    } else {
        // It's weekly
        // We guess the week number based on the start day of the month
        $day = $start->day;
        
        // Match with master weeks directly by finding the closest start date
        // Or simply by sequence in month: 1st week is usually day 1-7, 2nd is 8-14, etc.
        $weekNum = 1;
        if ($day >= 8 && $day <= 14) $weekNum = 2;
        elseif ($day >= 15 && $day <= 21) $weekNum = 3;
        elseif ($day >= 22 && $day <= 28) $weekNum = 4;
        elseif ($day >= 29) $weekNum = 5;
        
        $targetKey = "target_m" . $weekNum;
        if (isset($weeks[$targetKey]) && $weeks[$targetKey]) {
            $newStart = $weeks[$targetKey]['start'];
            $newEnd = $weeks[$targetKey]['end'];
            
            if ($b->periode_start !== $newStart || $b->periode_end !== $newEnd) {
                // Check if a record with the new date already exists to prevent duplicate
                $exists = BreakdownLm::where('lm_id', $b->lm_id)
                    ->where('unit_id', $b->unit_id)
                    ->where('bidang', $b->bidang)
                    ->where('periode_start', $newStart)
                    ->where('periode_end', $newEnd)
                    ->where('id', '!=', $b->id)
                    ->first();
                    
                if ($exists) {
                    $b->delete();
                    $deletedCount++;
                } else {
                    $b->periode_start = $newStart;
                    $b->periode_end = $newEnd;
                    $b->save();
                    $updatedCount++;
                }
            }
        } else {
            // Week 5 might not exist in some months. If it's week 5 and doesn't exist, maybe delete?
            if ($weekNum == 5 && !isset($weeks['target_m5'])) {
                $b->delete();
                $deletedCount++;
            }
        }
    }
}
echo "Script Execution Complete.\n";
echo "Records Updated to Master Periode: $updatedCount\n";
echo "Duplicates/Orphans Deleted: $deletedCount\n";
