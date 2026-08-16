<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unit1 = \App\Models\MasterUnit::where("name", "like", "%" . trim("INDRAMAYU") . "%")->first();
$shortName = trim(str_ireplace(['UP3', 'ULP'], '', "INDRAMAYU"));
$unit2 = \App\Models\MasterUnit::where("name", "like", "%" . $shortName . "%")->first();

echo "Unit1: " . ($unit1 ? $unit1->name : 'null') . "\n";
echo "Unit2: " . ($unit2 ? $unit2->name : 'null') . "\n";
