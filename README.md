# Tharimpepe Feeding Scheme Management System

Feeding Scheme Management System (FSMS) for the Tharimpepe Feeding Scheme, with a **Capacitor-powered Android app** and a **PHP website** backed by MySQL.

- Beneficiary management
- Attendance tracking
- Food stock & donation management
- Volunteer registration
- Reports & dashboard summaries
- Secure authentication

## Figma Prototype

**Interactive UI:** [https://trunk-canon-07981658.figma.site](https://trunk-canon-07981658.figma.site)

Screenshots at `docs/screenshots/`.

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8, PDO, MySQL/MariaDB |
| Web frontend | PHP views, Bootstrap |
| Mobile frontend | Capacitor SPA (`www/index.html`) |
| Android build | Gradle, Android SDK |
| Server | PHP built-in dev server + XAMPP Apache |

## Repository Structure

```
www/                  Capacitor SPA (login, dashboard, reports, etc.)
  index.html          Single-page application
  js/config.js        API URL detection for emulator / device / browser
api/                  REST API
  index.php           API router
router.php            PHP dev server router (serves SPA + API on one port)
android/              Gradle Android project (Capacitor)
  app/src/main/assets/capacitor.config.json   Server URL config
sql/schema.sql        Database schema
config/database.php   Database connection
app/                  Original PHP MVC app (XAMPP served)
  controllers/
  models/
  views/
public/               XAMPP entry point
tests/                PHP test suite
tools/                Utilities
docs/                 Academic docs, diagrams, screenshots
```

## How to Run

### Start the backend servers

Start both so the mobile app and website work:

**1. XAMPP Apache + MySQL** (for PHP website + database)

Open XAMPP Control Panel, start **Apache** and **MySQL**.

**2. PHP dev server** (for Capacitor SPA + API)

```powershell
php -S 0.0.0.0:8080 router.php
```

This serves the SPA at `http://localhost:8080/` and the API at `http://localhost:8080/api`.

### Database setup

- Open phpMyAdmin at `http://localhost/phpmyadmin`
- Create database `fsms_db`
- Import `sql/schema.sql`

Or use the included script:

```powershell
setup_db.bat
```

### Website

```
http://localhost/fsms - tharimpepe/public/index.php
```

### Mobile app

#### Prerequisites

- PHP dev server running on port 8080 (`php -S 0.0.0.0:8080 router.php`)
- Android device (emulator or physical) connected via ADB
- Allowed `C:\xampp\php\php.exe` through Windows Firewall for port 8080

#### Build & install

```powershell
cd android
.\gradlew assembleDebug
```

**Emulator:** installs automatically, uses `10.0.2.2` to reach the host.

**Physical device:** the APK targets `192.168.18.47:8080` (current LAN IP). If the IP changes, update `capacitor.config.json` and rebuild:

```jsonc
"server": {
  "url": "http://YOUR_LAN_IP:8080",
  "cleartext": true,
  "allowNavigation": ["YOUR_LAN_IP", "YOUR_LAN_IP:*", "10.0.2.2", "10.0.2.2:*", "localhost", "localhost:*"]
}
```

Install on a connected device:

```powershell
adb -s <DEVICE_SERIAL> install -r app\build\outputs\apk\debug\app-debug.apk
```

#### Mobile features

| Screen | Access | Features |
|--------|--------|----------|
| Dashboard | Default | KPIs, inventory overview, attendance trend, top donors |
| Beneficiaries | Bottom nav | List & register beneficiaries |
| Attendance | Bottom nav | Mark present/absent/late, view history |
| Food Stock | Bottom nav | Inventory list, add items |
| Donations | More tab | Record & list donations |
| Volunteers | Drawer > Volunteers | List & register volunteers |
| Reports | Drawer > Reports | 6 report types with **Print** & **Export CSV** |

Reports can be printed via the Print button or exported as `.csv` for spreadsheet analysis.

### Tests

```powershell
php tests\run_all_tests.php
```

## API

The REST API is served at `http://localhost:8080/api`:

- `POST /api/login` / `POST /api/register` — authentication
- `GET|POST /api/beneficiaries` — beneficiary CRUD
- `POST /api/attendance` — bulk attendance
- `GET|POST /api/stock` — food stock
- `GET|POST /api/donations` — donations
- `GET|POST /api/volunteers` — volunteers
- `GET /api/dashboard` — summary KPIs
- `GET /api/reports?type=summary|attendance|donations|food_stock|beneficiaries|volunteers` — reports

All endpoints (except login/register) require a `Bearer` token returned from login.

## Project Rules

- Business logic in controllers/models, not views
- Prepared statements for all DB access
- Features traceable to academic docs in `docs/academic/`
- Prefer improving existing modules over new features
- Run tests after backend changes
