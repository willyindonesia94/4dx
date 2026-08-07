<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updated = \App\Models\User::where('matrix_group_id', 'JARINGAN')->update(['matrix_group_id' => 'Jaringan (Asman)']);
echo "Updated $updated users.";
