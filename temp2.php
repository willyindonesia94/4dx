<?php
$val = "286.97";
echo "\nValue 1: " . floatval(str_replace(",", "", $val));
$val = "1,105.02";
echo "\nValue 2: " . floatval(str_replace(",", "", $val));

