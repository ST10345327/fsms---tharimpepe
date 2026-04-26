@echo off
cd /d C:\xampp\mysql\bin
mysql -u root -e "DROP DATABASE IF EXISTS fsms; CREATE DATABASE fsms;"
mysql -u root fsms < "C:\xampp\htdocs\fsms\sql\schema.sql"
echo Database setup complete!
pause