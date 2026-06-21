@echo off
echo ============================================
echo FSMS Mobile App - Database Setup
echo ============================================
echo.

echo Step 1: Checking MySQL...
mysql --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: MySQL is not installed or not in PATH
    echo Please install XAMPP and start MySQL
    pause
    exit /b 1
)
echo MySQL found!
echo.

echo Step 2: Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS fsms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo ERROR: Failed to create database
    echo Make sure MySQL is running in XAMPP
    pause
    exit /b 1
)
echo Database created successfully!
echo.

echo Step 3: Importing database schema...
mysql -u root fsms < sql\schema.sql
if errorlevel 1 (
    echo ERROR: Failed to import schema
    pause
    exit /b 1
)
echo Schema imported successfully!
echo.

echo Step 4: Creating demo user...
mysql -u root fsms -e "INSERT INTO users (Username, Password, Email, Role, Status, CreatedAt) VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@tharimpepe.org', 'admin', 'Active', NOW()) ON DUPLICATE KEY UPDATE Username='admin';"
if errorlevel 1 (
    echo WARNING: Demo user may already exist or failed to create
) else (
    echo Demo user created successfully!
)
echo.

echo Step 5: Creating auth tokens table...
mysql -u root fsms < migrations\20260620_create_auth_tokens_table.sql
if errorlevel 1 (
    echo WARNING: Auth tokens table may already exist
) else (
    echo Auth tokens table created!
)
echo.

echo ============================================
echo Setup Complete!
echo ============================================
echo.
echo Next steps:
echo 1. Start PHP server: npm run mobile:serve
echo 2. Open mobile app
echo 3. Login with: admin / admin123
echo.
pause