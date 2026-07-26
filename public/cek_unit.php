<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$units = \App\Models\MasterUnit::where('type', 'up3')->pluck('name')->toArray();
$wigs = \App\Models\MasterWig::pluck('judul')->toArray();

echo "UNITS:\n";
print_r($units);
echo "\nWIGS:\n";
print_r($wigs);
