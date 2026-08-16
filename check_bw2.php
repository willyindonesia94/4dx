<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bw = \App\Models\BreakdownWig::whereHas('unit', function($q) { $q->where('type', 'UID'); })->orderBy('id', 'desc')->first();
echo json_encode($bw);
