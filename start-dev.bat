@echo off
title Tharimpepe FSMS Server
echo ============================================
echo   Tharimpepe FSMS - Development Server
echo ============================================
echo.
echo  Web App:  http://localhost:8080
echo  API:      http://localhost:8080/api
echo  Android:  https://192.168.18.73:8080/api
echo  Phone:    https://192.168.18.73:8080/api
echo  Press Ctrl+C to stop the server
echo ============================================
echo.
php -S 0.0.0.0:8080 -t "%~dp0public" "%~dp0router.php"
if %errorlevel% neq 0 (
    echo.
    echo ERROR: PHP not found or failed to start.
    echo Make sure PHP is installed and in your PATH.
    pause
)
