<?php
$path = 'resources/views/master-periodes/index.blade.php';
$content = file_get_contents($path);

$patterns = [
    'start_m1', 'end_m1',
    'start_m2', 'end_m2',
    'start_m3', 'end_m3',
    'start_m4', 'end_m4',
    'start_m5', 'end_m5'
];

foreach ($patterns as $key) {
    $search = 'x-model="editData.'.$key.'" x-init="flatpickr($el, { dateFormat: \'Y-m-d\', altInput: true, altFormat: \'d/m/Y\' })"';
    $replace = 'x-model="editData.'.$key.'" x-init="let fp = flatpickr($el, { dateFormat: \'Y-m-d\', altInput: true, altFormat: \'d/m/Y\' }); $watch(\'editData.'.$key.'\', val => fp.setDate(val))"';
    $content = str_replace($search, $replace, $content);
}

file_put_contents($path, $content);
echo "OK";
