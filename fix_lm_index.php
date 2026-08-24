<?php
$content = file_get_contents('C:\Users\Lenovo\Documents\4DXJabar\app\Http\Controllers\RealizationController.php');
$search = '$isSuperAdmin = $user && $user->hasRole(\'Super Admin\');';
$replace = '$isSuperAdmin = $user && $user->hasAnyRole([\'Super Admin\', \'Perencanaan UID\']);';
$content = str_replace($search, $replace, $content);
file_put_contents('C:\Users\Lenovo\Documents\4DXJabar\app\Http\Controllers\RealizationController.php', $content);
echo "Fixed LM index.\n";
