<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indramayu = \App\Models\MasterUnit::where('name', 'UP3 Indramayu')->first();
$bw = \App\Models\BreakdownLm::where('unit_id', $indramayu->id)->get();
print_r($bw->toArray());
