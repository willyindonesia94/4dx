<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        $newContent = str_replace("->format('d M')", "->locale('id')->translatedFormat('d M')", $content);
        $newContent = str_replace("->format('d M Y')", "->locale('id')->translatedFormat('d M Y')", $newContent);
        $newContent = str_replace("->format('M Y')", "->locale('id')->translatedFormat('M Y')", $newContent);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated $path\n";
        }
    }
}

$ctrlPath = 'app/Http/Controllers/MasterPeriodeController.php';
$ctrlContent = file_get_contents($ctrlPath);
$ctrlContent = preg_replace("/date\('F', mktime\(0, 0, 0, \\\$periode->bulan, 10\)\)/", "\\Carbon\\Carbon::createFromDate(null, \$periode->bulan, 1)->locale('id')->translatedFormat('F')", $ctrlContent);
file_put_contents($ctrlPath, $ctrlContent);
echo "Updated MasterPeriodeController.php";
