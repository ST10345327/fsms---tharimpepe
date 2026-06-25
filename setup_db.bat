@echo off
cd /d C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS fsms;"
mysql -u root fsms < "C:\Users\CASH\Desktop\fsms - tharimpepe\sql\schema.sql"
echo Database setup complete!
pause