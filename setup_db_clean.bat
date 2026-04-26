@echo off
cd /d C:\xampp\mysql\bin
mysql -u root -e "DROP DATABASE IF EXISTS fsms; CREATE DATABASE fsms;"
if %errorlevel% neq 0 (
    echo Failed to create database
    pause
    exit /b 1
)
mysql -u root fsms < "C:\xampp\htdocs\fsms\sql\schema.sql"
if %errorlevel% neq 0 (
    echo Failed to load schema
    pause
    exit /b 1
)
echo Database setup complete!
pause