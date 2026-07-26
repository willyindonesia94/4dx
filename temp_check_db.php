<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$up3Bandung = \App\Models\MasterUnit::where('name', 'like', '%Bandung%')->where('type', 'UP3')->first();
$realisasis = \App\Models\Realisasi::where('lm_id', 1)->where('unit_id', $up3Bandung->id)->get();

echo "UP3 Bandung (ID: {$up3Bandung->id})\n";
foreach($realisasis as $r) {
    echo "ID: {$r->id} | Tgl: {$r->tanggal_input} | Angka: {$r->angka_realisasi}\n";
}
