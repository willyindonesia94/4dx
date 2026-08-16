<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$units = \App\Models\MasterUnit::where('name', 'like', '%Indramayu%')->get();
foreach ($units as $u) {
    echo "ID: {$u->id} | Name: '{$u->name}' (Length: " . strlen($u->name) . ")\n";
}
