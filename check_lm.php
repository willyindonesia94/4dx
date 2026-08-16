<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lm = App\Models\MasterLm::find(22);
echo "LM: " . $lm->judul_lm . "\n";
$breakdowns = App\Models\BreakdownLm::where('lm_id', 22)->get();
foreach($breakdowns as $b) {
    echo "Unit: " . $b->unit_id . ", Target: " . $b->angka_target . "\n";
}
