<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$newWigs = \App\Models\MasterWig::where('created_at', '>=', date('Y-m-d'))->get();
echo "New WIGs today: " . count($newWigs) . "\n";
foreach($newWigs as $w) echo "- " . $w->judul . "\n";
