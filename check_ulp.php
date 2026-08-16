<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bw = \App\Models\BreakdownWig::where('unit_id', 79)->get();
echo "ULP Indramayu count: " . $bw->count() . "\n";
foreach($bw as $b) {
    echo "  {$b->id} created at {$b->created_at} updated at {$b->updated_at}\n";
}
