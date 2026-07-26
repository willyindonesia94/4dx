<?php
$wig1 = App\Models\BreakdownWig::where("wig_id", 1)->where("unit_id", 1)->first();
if($wig1) {
    $wig1->target_tahunan = 3512.00;
    $wig1->target_jan = 286.97;
    $wig1->target_feb = 554.84;
    $wig1->target_mar = 844.69;
    $wig1->target_apr = 1105.02;
    $wig1->target_mei = 1409.64;
    $wig1->target_jun = 1701.43;
    $wig1->target_jul = 2004.17;
    $wig1->target_agu = 2308.39;
    $wig1->target_sep = 2604.30;
    $wig1->target_okt = 2917.82;
    $wig1->target_nov = 3215.11;
    $wig1->target_des = 3512.28;
    $wig1->save();
}

