<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterUnit;
use App\Models\MasterBidang;

// Create UP2D Unit
$up2d = MasterUnit::firstOrCreate(
    ['name' => 'UP2D Jawa Barat'],
    [
        'type' => 'UP2D',
        'parent_id' => 1 // UID Jawa Barat
    ]
);

// Create UP2K Unit
$up2k = MasterUnit::firstOrCreate(
    ['name' => 'UP2K Jawa Barat'],
    [
        'type' => 'UP2K',
        'parent_id' => 1 // UID Jawa Barat
    ]
);

// Create Matrix Groups (Bidang)
$bidangUp2d = MasterBidang::firstOrCreate(['name' => 'UP2D'], ['level' => 'UP3']);
$bidangUp2k = MasterBidang::firstOrCreate(['name' => 'UP2K'], ['level' => 'UP3']);

echo "Units and Bidangs created successfully!\n";
