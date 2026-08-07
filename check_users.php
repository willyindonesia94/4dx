<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::whereHas('unit', function($q) {
    $q->where('type', 'UP3');
})->select('id', 'name', 'email', 'matrix_group_id', 'unit_id')->get();

echo json_encode($users, JSON_PRETTY_PRINT);
