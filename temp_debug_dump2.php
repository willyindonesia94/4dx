<?php
$data = App\Models\BreakdownLm::with("lm", "unit")->where("unit_id", 1)->get();
foreach($data as $d) {
    echo $d->lm->judul_lm . " | " . $d->unit->name . " | " . $d->periode_start . " | " . $d->angka_target . "\n";
}

