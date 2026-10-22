@echo off
cd /d "%~dp0"
php -d extension=openssl vendor/phpunit/phpunit/phpunit
pause
