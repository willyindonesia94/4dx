<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wig = \App\Models\MasterWig::where('judul', 'like', 'WIG-1%')->first();
$uid = \App\Models\MasterUnit::where('type', 'UID')->first();

if ($wig && $uid) {
    $bw = \App\Models\BreakdownWig::where('wig_id', $wig->id)->where('unit_id', $uid->id)->first();
    if ($bw) {
        $bw->target_jan = 286.97;
        $bw->target_feb = 554.84;
        $bw->target_mar = 844.69;
        $bw->target_apr = 1105.02;
        $bw->target_mei = 1409.64;
        $bw->target_jun = 1701.43;
        $bw->target_jul = 2004.17;
        $bw->target_agu = 2308.39;
        $bw->target_sep = 2604.30;
        $bw->target_okt = 2917.82;
        $bw->target_nov = 3215.11;
        $bw->target_des = 3512.28;
        $bw->target_tahunan = 3512.00; // as shown in ui
        $bw->save();
        echo "Successfully updated Breakdown WIG-1 for UID Jabar.\n";
    } else {
        echo "Breakdown WIG not found.\n";
    }
} else {
    echo "WIG or UID not found.\n";
}
