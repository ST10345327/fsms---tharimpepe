@echo off
REM Start PHP development server with correct include_path for FSMS
php -S localhost:8000 -t public public/router.php -d include_path=
