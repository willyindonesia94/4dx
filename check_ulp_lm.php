<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ulp = \App\Models\MasterUnit::where('name', 'ULP Indramayu')->first();
if ($ulp) {
    $count = \App\Models\BreakdownLm::where('unit_id', $ulp->id)->count();
    echo "BreakdownLm for ULP Indramayu: $count\n";
} else {
    echo "ULP Indramayu not found\n";
}
