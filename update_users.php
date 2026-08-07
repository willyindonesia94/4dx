<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::whereHas('unit', function($q) {
    $q->where('type', 'UP3');
})->get();

$updated = 0;
foreach ($users as $u) {
    $name = strtolower($u->name);
    
    // We only want to update if it's currently ALL, or just fix it anyway
    if (str_contains($name, 'jaringan')) {
        $u->matrix_group_id = 'JARINGAN';
        $u->save();
        $updated++;
    } elseif (str_contains($name, 'niaga')) {
        $u->matrix_group_id = 'Niaga dan Pemasaran (Asman)';
        $u->save();
        $updated++;
    } elseif (str_contains($name, 'transaksi energi')) {
        $u->matrix_group_id = 'Transaksi Energi (Asman)';
        $u->save();
        $updated++;
    } elseif (str_contains($name, 'k3l')) {
        $u->matrix_group_id = 'K3L (TL UP3)';
        $u->save();
        $updated++;
    }
}

echo "Updated $updated UP3 users' matrix_group_id to match their bidang.";
