@echo off
echo Starting Tharimpepe FSMS Server...
echo.
echo Web:  http://localhost:8080
echo API:  https://192.168.18.73:8080/api (HTTPS proxy)
echo.
php -S 0.0.0.0:8080 -t "%~dp0public" "%~dp0router.php"
pause

