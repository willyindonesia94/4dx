<?php
$path = 'resources/views/master-periodes/index.blade.php';
$content = file_get_contents($path);
$content = preg_replace(
    '/<input type="date" name="([^"]+)" x-model="([^"]+)" class="([^"]+)">/', 
    '<input type="text" name="$1" x-model="$2" x-init="flatpickr(\$el, { dateFormat: \'Y-m-d\', altInput: true, altFormat: \'d/m/Y\' })" class="$3">', 
    $content
);
file_put_contents($path, $content);
echo "OK";
