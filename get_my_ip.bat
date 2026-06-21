@echo off
echo ============================================
echo Finding Your Computer's IP Address
echo ============================================
echo.

echo Your computer's IP addresses:
echo.

ipconfig | findstr /C:"IPv4"

echo.
echo ============================================
echo Setup Instructions:
echo ============================================
echo.
echo 1. Start PHP server: npm run mobile:serve
echo 2. Note your IPv4 address above (e.g., 192.168.1.100)
echo 3. Open the mobile app
echo 4. Go to Settings (hamburger menu ☰)
echo 5. Enter: http://[your-ip]:8000
echo 6. Tap "Test Connection"
echo 7. Try logging in
echo.
pause