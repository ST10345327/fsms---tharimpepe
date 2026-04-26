@echo off
cd /d C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS fsms;"
mysql -u root fsms < "C:\xampp\htdocs\fsms\sql\schema.sql"
mysql -u root fsms < "C:\xampp\htdocs\fsms\sql\seed.sql"
echo Database setup complete!
pause