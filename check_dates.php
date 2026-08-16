<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$realisasis = App\Models\Realisasi::where('lm_id', 22)->where('unit_id', 1)->get();
foreach($realisasis as $r) {
    echo "ID: " . $r->id . " Date: " . $r->tanggal_input . " Value: " . $r->angka_realisasi . "\n";
}
