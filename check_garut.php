<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unit = \App\Models\MasterUnit::where('name', 'UP3 Garut')->first();
$bw = \App\Models\BreakdownWig::where('unit_id', $unit->id)->get();
echo "Breakdown WIG count for UP3 Garut: " . $bw->count() . "\n";

$unit2 = \App\Models\MasterUnit::where('name', 'UP3 Indramayu')->first();
$bw2 = \App\Models\BreakdownWig::where('unit_id', $unit2->id)->get();
echo "Breakdown WIG count for UP3 Indramayu: " . $bw2->count() . "\n";
