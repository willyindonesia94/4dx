<?php

use App\Models\Target;

$badWigIds = [1, 2, 3, 6, 9, 12, 15, 18, 21, 24, 27];

echo "Before delete:\n";
echo "WIG Utama: " . Target::where('type', 'WIG Utama')->count() . "\n";
echo "Sub-WIG: " . Target::where('type', 'Sub-WIG')->count() . "\n";
echo "Lead Measure: " . Target::where('type', 'Lead Measure')->count() . "\n";
echo "Realization: " . \App\Models\Realization::count() . "\n";

Target::whereIn('id', $badWigIds)->delete();

echo "\nAfter delete:\n";
echo "WIG Utama: " . Target::where('type', 'WIG Utama')->count() . "\n";
echo "Sub-WIG: " . Target::where('type', 'Sub-WIG')->count() . "\n";
echo "Lead Measure: " . Target::where('type', 'Lead Measure')->count() . "\n";
echo "Realization: " . \App\Models\Realization::count() . "\n";

