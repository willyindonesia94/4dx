<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$units = MasterUnit::where('type', 'UP3')->get();
$defaultPassword = Hash::make('password');

$count = 0;

foreach ($units as $unit) {
    // Ex: "UP3 Bandung" -> "up3bandung", "UP3 Gunung Putri" -> "up3gunungputri"
    $cleanName = strtolower(str_replace(' ', '', $unit->name));
    
    $usersToCreate = [
        [
            'name' => 'Admin ' . $unit->name,
            'email' => 'admin.' . $cleanName . '@pln.co.id',
            'role_name' => 'Admin UP3',
            'matrix_group_id' => 'ALL'
        ],
        [
            'name' => 'Manager ' . $unit->name,
            'email' => 'manager.' . $cleanName . '@pln.co.id',
            'role_name' => 'Manager UP3',
            'matrix_group_id' => 'ALL'
        ],
        [
            'name' => 'Asman Jaringan ' . $unit->name,
            'email' => 'jaringan.' . $cleanName . '@pln.co.id',
            'role_name' => 'Divisi UP3',
            'matrix_group_id' => 'JARINGAN'
        ],
        [
            'name' => 'Niaga ' . $unit->name,
            'email' => 'niaga.' . $cleanName . '@pln.co.id',
            'role_name' => 'Divisi UP3',
            'matrix_group_id' => 'Niaga dan Pemasaran (Asman)'
        ],
        [
            'name' => 'Transaksi Energi ' . $unit->name,
            'email' => 'te.' . $cleanName . '@pln.co.id',
            'role_name' => 'Divisi UP3',
            'matrix_group_id' => 'Transaksi Energi (Asman)'
        ],
        [
            'name' => 'K3L ' . $unit->name,
            'email' => 'k3l' . $cleanName . '@pln.co.id',
            'role_name' => 'Divisi UP3',
            'matrix_group_id' => 'K3L (TL UP3)'
        ]
    ];

    foreach ($usersToCreate as $u) {
        $user = User::firstOrCreate(
            ['email' => $u['email']],
            [
                'name' => $u['name'],
                'password' => $defaultPassword,
                'role_name' => $u['role_name'],
                'unit_id' => $unit->id,
                'matrix_group_id' => $u['matrix_group_id'],
            ]
        );
        if ($user->wasRecentlyCreated) {
            $count++;
        }
    }
}

echo "Berhasil membuat $count user baru!\n";
