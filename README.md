# Tharimpepe Feeding Scheme Management System

This repository contains the Feeding Scheme Management System (FSMS) for the Tharimpepe Feeding Scheme. The project is based on the academic requirements and design documents in `docs/academic/`.

The documented core scope is:

- Beneficiary management
- Attendance tracking
- Food stock and donation management
- Volunteer registration and scheduling
- Reports and dashboard summaries
- Secure user authentication

## Figma Prototype

**Interactive UI Prototype:** [https://trunk-canon-07981658.figma.site](https://trunk-canon-07981658.figma.site)

This Figma prototype showcases the complete user interface design for all modules: Dashboard, Beneficiaries, Attendance, Stock and Donations, Volunteers, and Reports. Use this as the visual reference for frontend implementation.

**Prototype screenshots:** See `docs/screenshots/`.

## Stack

- Backend: PHP, PDO, MySQL/MariaDB (MySQL/XAMPP is the supported database)
- Frontend: PHP views, HTML, Bootstrap 5
- Mobile: Capacitor 8 with static HTML/JS shell in `mobile-shell/`
- API: REST JSON endpoints under `api/`
- Architecture: MVC-style separation across `app/controllers`, `app/models`, and `app/views`
- Testing: PHP test runner in `tests/` (uses MySQL)

## Repository Structure

```text
app/
  controllers/   HTTP and workflow logic
  helpers/       bootstrap, validation, session, error handling
  models/        database access and business rules
  views/         UI templates
api/             REST JSON endpoints for mobile and integrations
android/         Capacitor Android wrapper
config/
  database.php   database connection layer
docs/
  academic/      submitted academic documentation
  diagrams/      diagrams and modelling artifacts
  screenshots/   Figma prototype screenshots
  proposals/     prototype handoff and planning notes
mobile-shell/    Capacitor mobile app pages and assets
migrations/      database migration scripts
public/
  index.php      web application entry point
sql/
  schema.sql     database schema
tests/
  run_all_tests.php
tools/           database utilities and maintenance scripts
```

## Current Status

The system is in a working state suitable for demonstration and academic submission:

- Database connection and schema are functional
- Core modules operate across web, API, and mobile surfaces
- Automated tests pass (16/16)
- Final functionality audit: see `docs/FUNCTIONALITY_AUDIT.md`

## Quick Start

### 1. Start MySQL/MariaDB

This project is set up for a local XAMPP MySQL installation on Windows.

### 2. Ensure the schema exists

The repository schema lives in [sql/schema.sql](sql/schema.sql). Run the setup scripts or migrations as needed:

```powershell
setup_db_clean.bat
```

### 3. Open the app

Point your local PHP/XAMPP setup at:

```text
public/index.php
```

Or use the included dev server script:

```powershell
start-server.bat
```

### 4. Run tests

```powershell
php tests\run_all_tests.php
```

## Mobile App

See [MOBILE_APP_GUIDE.md](MOBILE_APP_GUIDE.md) for Capacitor build and deployment instructions.

## Development Direction

Use [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) as the working plan for backend and frontend implementation.

### Backend phase

Backend work should stay aligned with the academic model in the documentation:

- User authentication
- Beneficiaries
- Attendance
- Food stock
- Donations
- Volunteers
- Schedules
- Reports

### Frontend phase

Frontend work should use:

- The system design document for input/request interaction structure
- `docs/proposals/Figma_Prototype_Handoff.md` for prototype fidelity
- `docs/screenshots/` for screen-by-screen visual references
- Existing `app/views/` patterns where they already support the documented workflows

## Project Rules

- Keep business logic in controllers and models, not in views.
- Use prepared statements for all database access.
- Keep features traceable to the documented scope in `docs/academic/`.
- Prefer improving existing modules over adding new unrelated features.
- Run the test suite after backend changes.

## Documentation References

- Requirements: `docs/academic/ST10345327_OLEBOGENG_Task_2_Requirements_Analysis.pdf`
- System design: `docs/academic/ST10345327_OLEBOGENG_Task_2_System_Design.pdf`
- Figma/prototype handoff: `docs/proposals/Figma_Prototype_Handoff.md`
- Prototype screenshots: `docs/screenshots/`
- Functionality audit: `docs/FUNCTIONALITY_AUDIT.md`
- Testing guide: `TESTING_DOCUMENTATION.md`
