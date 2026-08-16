<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bw = \App\Models\BreakdownWig::where('wig_id', 1)->with('unit')->get();
echo "WIG 1 Breakdowns:\n";
foreach($bw as $b) {
    echo "Unit: {$b->unit->name} (ID: {$b->unit_id}) | Updated: {$b->updated_at}\n";
}
