@echo off
title Laravel Server - 4DX UID Jabar
color 0A
echo ================================================
echo    Laravel Development Server - 4DX UID Jabar
echo    Akses di browser: http://127.0.0.1:8000
echo ================================================
echo.
cd /d "C:\Users\Lenovo\Documents\4DXUIDJabar"
"C:\Program Files\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
pause
