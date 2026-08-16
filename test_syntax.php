<?php
$file = 'app/Http/Controllers/DashboardController.php';
// Parse the file and check for errors
try {
    $code = file_get_contents($file);
    token_get_all($code, TOKEN_PARSE);
    echo "No syntax errors\n";
} catch (ParseError $e) {
    echo "Parse error: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
}
