<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$units = \App\Models\MasterUnit::where('type', 'UP3')->get();
foreach ($units as $u) {
    $c = \App\Models\BreakdownWig::where('unit_id', $u->id)->count();
    echo "{$u->name}: $c\n";
}
