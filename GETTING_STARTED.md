Quick start — clone, configure, and run the project (minimal steps)

Prerequisites
- Git
- PHP 8 (CLI)
- MySQL (or XAMPP)
- Node.js + npm (for Capacitor)
- Android Studio (for Android build) or ADB to install APK

1. Clone the repo

```bash
git clone <repo-url>
cd "fsms - tharimpepe"
```

2. Copy environment example and edit if needed

```powershell
copy .env.example .env
# Edit .env to set DB credentials if different from XAMPP defaults
```

3. Start MySQL (XAMPP) or ensure an accessible MySQL server
- If using XAMPP: open XAMPP Control Panel and start Apache + MySQL

4. Initialize the database

- Recommended (PowerShell helper):

```powershell
# from repo root
powershell -ExecutionPolicy Bypass -File .\scripts\setup_db.ps1
```

- Or use the existing batch helper (for XAMPP with default paths):

```powershell
setup_db.bat
```

This creates the database specified in `.env` (default `fsms`) and imports `sql/schema.sql`.

5. Start the PHP dev server (serves SPA + API)

```powershell
# from repo root
.\start-dev.bat
# or directly
php -S 0.0.0.0:8080 router.php
```

6. Open the website
- Web app: http://localhost:8080
- API: http://localhost:8080/api

7. Mobile (Android) — build and run

- Optional: update `www/js/config.js` `physicalHost` to your PC LAN IP (if using physical device) before building.
- Sync Capacitor assets and open Android Studio:

```bash
npx cap sync
npx cap open android
```

- Build in Android Studio or install APK via adb:

```bash
cd android
./gradlew assembleDebug
adb install -r app/build/outputs/apk/debug/app-debug.apk
```

Troubleshooting
- If the app shows "invalid response (check if api is running)", confirm your phone and PC are on the same Wi-Fi and visit `http://<YOUR_PC_IP>:8080/api` from the phone browser.
- If MySQL import fails, open `sql/schema.sql` in phpMyAdmin and import manually.

Notes
- `config/database.php` reads DB configuration from environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`.
- The `.env` file is only an easy copy target; the app reads environment variables from the runtime environment (PHP CLI/XAMPP). For Windows/XAMPP, set environment variables in Apache `httpd.conf` or use `php.ini`/`setenv` helpers if needed.

If you'd like, I can also add a small `docker-compose.yml` to run MySQL and PHP so setup is fully containerized.
