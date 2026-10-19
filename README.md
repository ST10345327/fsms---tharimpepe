# Tharimpepe Feeding Scheme Management System

Feeding Scheme Management System (FSMS) for the Tharimpepe Feeding Scheme, with a **Capacitor-powered Android app** and a **PHP website** backed by MySQL.

- Beneficiary management
- Attendance tracking
- Food stock & donation management
- Volunteer registration
- Reports & dashboard summaries
- Secure authentication

##  Figma Prototype

**Interactive UI:** [https://trunk-canon-07981658.figma.site](https://trunk-canon-07981658.figma.site)

This Figma prototype showcases the complete user interface design for all modules: Dashboard, Beneficiaries, Attendance, Stock & Donations, Volunteers, and Reports. Use this as the visual reference for frontend implementation.

**Prototype screenshots:** See `docs/screenshots/`.

Quick start: see [GETTING_STARTED.md](GETTING_STARTED.md) for a minimal, copy-paste workflow to clone, configure, and run the project.

**Demo video:** 
- Website: https://youtu.be/vvwGJumiVZw
- Mobile App: https://youtu.be/GrrGO9rQQq0
- Additional demo: https://youtube.com/shorts/jrbG3rI063A

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
php -S 0.0.0.0:8080 -t public router.php
```

This serves the Apache-style PHP UI at `http://localhost:8080/` and the API at `http://localhost:8080/api`.

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

- Local HTTPS proxy exposing the LAN API on port 8080
- Android device (emulator or physical) connected via ADB
- Allow the HTTPS proxy through Windows Firewall for port 8080

#### Build & install

```powershell
cd android
.\gradlew assembleDebug
```

The packaged Android app uses `https://192.168.18.73:8080/api`. The phone and computer must be on the same Wi-Fi network, and the HTTPS proxy must be running and reachable on port 8080. Android trusts the local certificate from `android/app/src/main/res/raw/fsms_local_dev_ca.pem`.

If the computer's LAN IP, proxy port, or HTTPS certificate changes, update `www/js/config.js` and `android/app/src/main/res/raw/fsms_local_dev_ca.pem`, then run `npm run mobile:copy` and rebuild.

Install on a connected device:

```powershell
adb -s <DEVICE_SERIAL> install -r app\build\outputs\apk\debug\app-debug.apk
```

The debug APK can be sent to teammates for testing. They can install it by enabling permission to install unknown apps for the app they use to open the APK. For the API to work, their phone must be on the same Wi-Fi as this computer while the HTTPS proxy is running; otherwise use a shared HTTPS server and update the API host before rebuilding.

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
