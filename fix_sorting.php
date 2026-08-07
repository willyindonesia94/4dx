<?php
$path = 'resources/views/cascading/lm.blade.php';
$content = file_get_contents($path);

$search = "->sortBy(function(\$b) { return \$b->unit->name ?? ''; })";
$replace = "->sortBy(function(\$b) { 
    \$isMonthly = \\Carbon\\Carbon::parse(\$b->periode_start)->diffInDays(\\Carbon\\Carbon::parse(\$b->periode_end)) >= 20 ? 0 : 1;
    return (\$b->unit->name ?? '') . '_' . \$isMonthly . '_' . \$b->periode_start; 
})";

$content = str_replace($search, $replace, $content);
file_put_contents($path, $content);
echo "OK";
