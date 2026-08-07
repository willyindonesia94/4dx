<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('DROP TABLE IF EXISTS sesi_wig_komitmens');
DB::table('migrations')->where('migration', 'like', '%create_sesi_wig_komitmens_table%')->delete();
echo "Dropped table and migration record.";
