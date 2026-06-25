# Tharimpepe Feeding Scheme Management System

This repository contains the Feeding Scheme Management System (FSMS) for the Tharimpepe Feeding Scheme. The project is based on the academic requirements and design documents in `docs/academic/`.

The documented core scope is:
- Beneficiary management
- Attendance tracking
- Food stock and donation management
- Volunteer registration and scheduling
- Reports and dashboard summaries
- Secure user authentication

## 🎨 Figma Prototype

**Interactive UI Prototype:** [https://trunk-canon-07981658.figma.site](https://trunk-canon-07981658.figma.site)

This Figma prototype showcases the complete user interface design for all modules: Dashboard, Beneficiaries, Attendance, Stock & Donations, Volunteers, and Reports. Use this as the visual reference for frontend implementation.

**Prototype screenshots:** See `docs/screenshots/`.

**Demo video:** YouTube link to be added.

## Stack

- Backend: PHP, PDO, MySQL/MariaDB
- Frontend: PHP views, HTML, Bootstrap
- Architecture: MVC-style separation across `app/controllers`, `app/models`, and `app/views`
- Testing: lightweight PHP test runner in `tests/`

## Repository Structure

```text
app/
  controllers/   HTTP and workflow logic
  helpers/       bootstrap, validation, session, error handling
  models/        database access and business rules
  views/         UI templates
api/
  README.md      placeholder for future REST/API endpoints
backend/
  README.md      placeholder for separated backend code
config/
  database.php   database connection layer
database/
  README.md      placeholder for migrations and seed data
docs/
  academic/      submitted academic documentation
  diagrams/      diagrams and modelling artifacts
  screenshots/   uploaded Figma prototype screenshots
  proposals/     prototype handoff and planning notes
frontend/
  README.md      placeholder for separated frontend code
public/
  index.php      application entry point
  fsms-prototype.html
sql/
  schema.sql     database schema
tests/
  run_all_tests.php
tools/
  generate_task2_docs.py
```

## Current Status

The repo is now in a workable development state:
- database connection and test runner are functioning
- the current PHP authentication and validation tests pass
- the project still contains a mix of academic deliverables, prototype assets, and live implementation code

That means the next development phase should stay anchored to the documentation and avoid drifting into unrelated modules unless they clearly support the documented FSMS scope.

## How to Run

This application has two delivery methods: a **PHP website** served via XAMPP, and a **mobile Android app** built with Capacitor.

---

### 🌐 Website (PHP + XAMPP)

#### Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL/MariaDB)
- PHP 8.x

#### Steps

**1. Start XAMPP services**
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

**2. Create the database**
- Open phpMyAdmin (`http://localhost/phpmyadmin`)
- Create a new database (e.g. `fsms_db`)
- Import `sql/schema.sql`
- Or run the included setup script:
  ```powershell
  setup_db.bat
  ```

**3. Open the app**
- Navigate to:
  ```
  http://localhost/fsms - tharimpepe/public/index.php
  ```
  (Or use the configured virtual host in `tools/fsms_vhost.conf`)

**4. Run tests**
  ```powershell
  php tests\run_all_tests.php
  ```

---

### 📱 Mobile App (Capacitor + Android)

This project uses [Capacitor](https://capacitorjs.com/) to package the web app as a native Android application.

#### Prerequisites

- [Node.js](https://nodejs.org/) installed
- [Android Studio](https://developer.android.com/studio) (for building the APK)
- The web app files must be in the `www/` directory

#### Steps

**1. Sync web assets to Android**
Run this after making any changes to your web app (views, assets, etc.):
```powershell
npm run mobile:sync
```
This copies files from `www/` into the Android project and syncs Capacitor plugins.

**2. Preview in browser (optional)**
```powershell
npm run mobile:serve
```
Starts a static file server at `http://localhost:3000` serving the `www/` directory.

**3. Build and run in Android Studio**
- Open the `android/` folder in Android Studio
- Wait for Gradle to finish syncing
- Click the **Run** button (green triangle) or select **Build > Build Bundle(s) / APK(s) > Build APK(s)**
- The APK will be generated at:
  ```
  android\app\build\outputs\apk\debug\app-debug.apk
  ```

#### Typical mobile workflow

1. Make changes to your web app
2. Run `npm run mobile:sync` to push changes to Android
3. Run `npm run mobile:serve` to preview in a browser
4. Open Android Studio, build, and run on a device/emulator

---

## Quick Start

### 1. Start MySQL/MariaDB

This project is currently set up around the local XAMPP MySQL installation on Windows.

### 2. Ensure the schema exists

The repository schema lives in [sql/schema.sql](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/sql/schema.sql).

### 3. Open the app

Point your local PHP/XAMPP setup at:

```text
public/index.php
```

### 4. Run tests

```powershell
php tests\run_all_tests.php
```

## Development Direction

Use [DEVELOPMENT_GUIDE.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/DEVELOPMENT_GUIDE.md) as the working plan for backend and frontend implementation.

### Backend phase

Backend work should stay aligned with the academic model in the documentation and be reviewed as server-side, schema, validation, or reporting work:
- user authentication
- beneficiaries
- attendance
- food stock
- donations
- volunteers
- schedules
- reports
- tests, database setup, and infrastructure support for those modules

### Frontend phase

Frontend work should use:
- the system design document for input/request interaction structure
- `docs/proposals/Figma_Prototype_Handoff.md` for prototype fidelity
- `docs/screenshots/` for screen-by-screen visual references
- existing `app/views/` patterns where they already support the documented workflows

Review frontend changes as screen-level work:
- dashboard shell and navigation
- beneficiary management screens
- attendance flows
- stock and donations screens
- volunteer and schedule screens
- reports and summary views

## Project Rules

- Keep business logic in controllers and models, not in views.
- Use prepared statements for all database access.
- Keep features traceable to the documented scope in `docs/academic/`.
- Prefer improving existing modules over adding new unrelated features.
- Run the test suite after backend changes.

## Review Workflow

To make code review easy:
- keep backend and frontend work in separate branches and pull requests when possible
- use small, module-based commits instead of mixed commits
- mention the matching documented workflow in each PR
- rely on CI to show PHP linting and test results automatically

See [CONTRIBUTING.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/CONTRIBUTING.md) for branch naming, commit format, and reviewer checklist.

## Documentation References

- Requirements: `docs/academic/ST10345327_OLEBOGENG_Task_2_Requirements_Analysis.pdf`
- System design: `docs/academic/ST10345327_OLEBOGENG_Task_2_System_Design.pdf`
- Figma/prototype handoff: `docs/proposals/Figma_Prototype_Handoff.md`
- Prototype screenshots: `docs/screenshots/`
