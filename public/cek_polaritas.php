<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$wigs = \App\Models\MasterWig::all(['id', 'judul', 'polaritas']);
foreach($wigs as $w) {
    echo $w->judul . " -> Polaritas: " . $w->polaritas . " (Type: " . gettype($w->polaritas) . ")\n";
}
