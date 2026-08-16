<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$t = App\Models\BreakdownLm::where('lm_id', 22)->where('unit_id', 1)->first();
echo "Target UID Jabar: " . ($t->angka_target ?? 'KOSONG') . "\n";
$r = App\Models\Realisasi::where('lm_id', 22)->where('unit_id', 1)->first();
echo "Realisasi UID Jabar: " . ($r->angka_realisasi ?? 'KOSONG') . "\n";
