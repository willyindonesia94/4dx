<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
DB::statement("ALTER TABLE master_units MODIFY COLUMN type ENUM('UID', 'UP3', 'ULP', 'UP2D', 'UP2K') NOT NULL");
echo "Column altered\n";
