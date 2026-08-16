<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$units = App\Models\MasterUnit::whereIn('type', ['UP2D', 'UP2K'])->get();
echo "UP2D/UP2K Units: \n";
foreach($units as $u) {
    echo "- " . $u->name . " (ID: " . $u->id . ", Type: " . $u->type . ")\n";
}

$allUnits = App\Models\MasterUnit::all();
echo "\nAll units count: " . $allUnits->count() . "\n";
