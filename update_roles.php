<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('roles')->where('name', 'Divisi UID')->update(['name' => 'Bidang UID']);
DB::table('roles')->where('name', 'Divisi UP3')->update(['name' => 'Bidang UP3']);

$u1 = DB::table('users')->where('role_name', 'Divisi UID')->update(['role_name' => 'Bidang UID']);
$u2 = DB::table('users')->where('role_name', 'Divisi UP3')->update(['role_name' => 'Bidang UP3']);

$lm1 = DB::table('master_lms')->where('tujuan_unit_role', 'Divisi UID')->update(['tujuan_unit_role' => 'Bidang UID']);
$lm2 = DB::table('master_lms')->where('tujuan_unit_role', 'Divisi UP3')->update(['tujuan_unit_role' => 'Bidang UP3']);

echo "Updated $u1 users from Divisi UID to Bidang UID.\n";
echo "Updated $u2 users from Divisi UP3 to Bidang UP3.\n";
echo "Updated $lm1 LMs from Divisi UID to Bidang UID.\n";
echo "Updated $lm2 LMs from Divisi UP3 to Bidang UP3.\n";
