<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$recent = \App\Models\BreakdownWig::orderBy('updated_at', 'desc')->take(10)->get();
echo "Recent updates in BreakdownWig:\n";
foreach($recent as $r) {
    echo "ID: {$r->id} | Unit: {$r->unit_id} | WIG: {$r->wig_id} | Updated: {$r->updated_at}\n";
}
