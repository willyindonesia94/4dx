<?php
$path = 'resources/views/cascading/lm.blade.php';
$content = file_get_contents($path);

$searchGroupBy = "->groupBy(function(\$item) {\n                                                                                return \\Carbon\\Carbon::parse(\$item->periode_start)->format('M Y');\n                                                                            });";
$replaceGroupBy = "->groupBy(function(\$item) {\n                                                                                if (\$item->bulan && \$item->tahun) {\n                                                                                    return strtoupper(\$item->bulan_indo) . ' ' . \$item->tahun;\n                                                                                }\n                                                                                return strtoupper(\\Carbon\\Carbon::parse(\$item->periode_start)->locale('id')->translatedFormat('F Y'));\n                                                                            });";
$content = str_replace($searchGroupBy, $replaceGroupBy, $content);

$searchDate = "->format('d M Y') }}";
$replaceDate = "->locale('id')->translatedFormat('d M Y') }}";
$content = str_replace($searchDate, $replaceDate, $content);

file_put_contents($path, $content);
echo "OK";
