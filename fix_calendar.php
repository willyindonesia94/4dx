<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterPeriode;
use App\Models\BreakdownLm;
use App\Models\SesiWig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$tahun = 2026;

DB::beginTransaction();
try {
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $periode = MasterPeriode::where('tahun', $tahun)->where('bulan', $bulan)->first();
        if (!$periode) continue;
        
        $newWeeks = MasterPeriode::calculateDefaultWeeks($tahun, $bulan);
        
        $oldDates = [
            'm1' => ['start' => $periode->start_m1, 'end' => $periode->end_m1],
            'm2' => ['start' => $periode->start_m2, 'end' => $periode->end_m2],
            'm3' => ['start' => $periode->start_m3, 'end' => $periode->end_m3],
            'm4' => ['start' => $periode->start_m4, 'end' => $periode->end_m4],
            'm5' => ['start' => $periode->start_m5, 'end' => $periode->end_m5],
        ];

        // Update master_periodes
        $periode->update([
            'start_m1' => $newWeeks['target_m1'] ? $newWeeks['target_m1']['start'] : null,
            'end_m1'   => $newWeeks['target_m1'] ? $newWeeks['target_m1']['end'] : null,
            'start_m2' => $newWeeks['target_m2'] ? $newWeeks['target_m2']['start'] : null,
            'end_m2'   => $newWeeks['target_m2'] ? $newWeeks['target_m2']['end'] : null,
            'start_m3' => $newWeeks['target_m3'] ? $newWeeks['target_m3']['start'] : null,
            'end_m3'   => $newWeeks['target_m3'] ? $newWeeks['target_m3']['end'] : null,
            'start_m4' => $newWeeks['target_m4'] ? $newWeeks['target_m4']['start'] : null,
            'end_m4'   => $newWeeks['target_m4'] ? $newWeeks['target_m4']['end'] : null,
            'start_m5' => $newWeeks['target_m5'] ? $newWeeks['target_m5']['start'] : null,
            'end_m5'   => $newWeeks['target_m5'] ? $newWeeks['target_m5']['end'] : null,
        ]);

        // Cascade changes
        for ($i = 1; $i <= 5; $i++) {
            $m = "m{$i}";
            $oldStart = $oldDates[$m]['start'];
            $oldEnd = $oldDates[$m]['end'];
            $newStart = $newWeeks["target_{$m}"] ? $newWeeks["target_{$m}"]['start'] : null;
            $newEnd = $newWeeks["target_{$m}"] ? $newWeeks["target_{$m}"]['end'] : null;

            // If old date exists and was changed
            if ($oldStart && $oldEnd) {
                if ($newStart && $newEnd && ($oldStart !== $newStart || $oldEnd !== $newEnd)) {
                    // Update BreakdownLm
                    BreakdownLm::where('periode_start', $oldStart)
                        ->where('periode_end', $oldEnd)
                        ->update([
                            'periode_start' => $newStart,
                            'periode_end' => $newEnd
                        ]);
                    
                    // Update SesiWig tanggal_pelaksanaan (which usually maps to end of week)
                    SesiWig::where('tipe_sesi', 'mingguan')
                        ->where('minggu_ke', $i)
                        ->whereMonth('tanggal_pelaksanaan', Carbon::parse($oldEnd)->month)
                        ->update(['tanggal_pelaksanaan' => $newEnd]);
                } else if (!$newStart && !$newEnd) {
                    // This week was removed (e.g. M5 was nullified)
                    // We technically should delete or leave it, but let's just let it be or throw warning.
                    echo "Warning: Week $m in month $bulan was removed. Old dates: $oldStart - $oldEnd\n";
                }
            }
        }
    }
    DB::commit();
    echo "Calendar regenerated and cascaded successfully!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
