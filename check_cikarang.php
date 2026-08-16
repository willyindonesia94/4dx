<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cikarang = \App\Models\MasterUnit::where('name', 'UP3 Cikarang')->first();
$bw = \App\Models\BreakdownWig::where('unit_id', $cikarang->id)->where('wig_id', 1)->first();
print_r($bw->toArray());
