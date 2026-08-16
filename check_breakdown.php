<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unit = \App\Models\MasterUnit::where('name', 'UP3 Indramayu')->first();
if ($unit) {
    $bw = \App\Models\BreakdownWig::where('unit_id', $unit->id)->get();
    echo "Breakdown WIG count for UP3 Indramayu: " . $bw->count() . "\n";
    foreach ($bw as $b) {
        echo "WIG ID: {$b->wig_id} | Target: {$b->target_tahunan}\n";
    }
}
