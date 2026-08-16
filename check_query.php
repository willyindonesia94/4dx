<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unit1 = \App\Models\MasterUnit::where("name", "like", "%UP3 Indramayu%")->first();
$unit2 = \App\Models\MasterUnit::where("name", "like", "%UP3 Indramayu %")->first();

echo "Without space: " . ($unit1 ? $unit1->name : 'null') . "\n";
echo "With space: " . ($unit2 ? $unit2->name : 'null') . "\n";
