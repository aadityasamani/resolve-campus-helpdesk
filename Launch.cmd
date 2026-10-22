@echo off
cd /d "%~dp0"
start "" http://127.0.0.1:8029
cd public
php -d extension=openssl -S 127.0.0.1:8029 ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
if errorlevel 1 pause
