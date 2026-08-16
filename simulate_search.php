<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$searchName = "INDRAMAYU";
$searchName = trim($searchName);

$unitWig = \App\Models\MasterUnit::where("name", $searchName)->first();
if (!$unitWig) {
    echo "1 failed\n";
    $unitWig = \App\Models\MasterUnit::where("name", "UP3 " . ucwords(strtolower($searchName)))->first();
}
if (!$unitWig) {
    echo "2 failed\n";
    $unitWig = \App\Models\MasterUnit::where("name", "ULP " . ucwords(strtolower($searchName)))->first();
}
if (!$unitWig) {
    echo "3 failed\n";
    $unitWig = \App\Models\MasterUnit::where("name", "like", "%" . $searchName . "%")->first();
}

if ($unitWig) {
    echo "Found: {$unitWig->name} (Type: {$unitWig->type})\n";
} else {
    echo "Not found\n";
}
