<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$matrix = App\Models\SesiWigMatrix::where('minggu_ke', 1)->get();
foreach($matrix as $m) {
    echo "SW ID: " . $m->id . " SesiWig ID: " . $m->sesi_wig_id . " Tipe: " . $m->tipe_sesi . " Start: " . $m->tanggal_mulai_minggu . " End: " . $m->tanggal_selesai_minggu . "\n";
}
