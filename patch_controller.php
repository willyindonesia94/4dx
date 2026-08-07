<?php
$controllerPath = 'app/Http/Controllers/LaporanBulananController.php';
$controllerContent = file_get_contents($controllerPath);

$newMethodContent = file_get_contents('new_exportLengkap.php');

// We need to replace public function exportLengkap(Request $request) { ... }
// Since it's the last method in the class, we can find its start and replace until the end of the file, then add a closing brace.
$startPos = strpos($controllerContent, 'public function exportLengkap(Request $request)');
if ($startPos !== false) {
    // The class closing brace is the very last '}'
    $lastBracePos = strrpos($controllerContent, '}');
    
    $prefix = substr($controllerContent, 0, $startPos);
    
    // Remove the <?php tag from newMethodContent
    $newMethodContent = str_replace('<?php' . PHP_EOL . '// Just the method logic for exportLengkap' . PHP_EOL, '', $newMethodContent);
    
    $newContent = $prefix . $newMethodContent . "\n}\n";
    file_put_contents($controllerPath, $newContent);
    echo "Controller patched successfully.\n";
} else {
    echo "Could not find exportLengkap method.\n";
}
