<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

if (!Role::where('name', 'Sub Bidang UID')->exists()) {
    Role::create(['name' => 'Sub Bidang UID']);
    echo "Role 'Sub Bidang UID' created successfully.";
} else {
    echo "Role already exists.";
}
