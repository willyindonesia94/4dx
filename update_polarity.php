<?php
$targets = App\Models\Target::all();
$count = 0;
foreach ($targets as $target) {
    if ($target->metric && $target->metric->polarity === 'Negative') {
        $target->polarity = 'Minimize';
    } else {
        $target->polarity = 'Maximize';
    }
    $target->save();
    $count++;
}
echo "Berhasil memperbarui skala pencapaian untuk $count target.\n";
