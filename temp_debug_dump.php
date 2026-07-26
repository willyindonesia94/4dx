<?php
$data = App\Models\BreakdownLm::with("unit")->get();
foreach($data as $d) {
    echo $d->unit->name . " | " . $d->periode_start . " | " . $d->angka_target . "\n";
}

