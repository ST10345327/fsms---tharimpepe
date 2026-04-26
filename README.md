# Tharimpepe Feeding Scheme Management System

This repository contains the Feeding Scheme Management System (FSMS) for the Tharimpepe Feeding Scheme. The project is based on the academic requirements and design documents in [Documentation/Task2a_Requirements_Analysis.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2a_Requirements_Analysis.docx) and [Documentation/Task2b_System_Design.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2b_System_Design.docx).

The documented core scope is:
- Beneficiary management
- Attendance tracking
- Food stock and donation management
- Volunteer registration and scheduling
- Reports and dashboard summaries
- Secure user authentication

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
config/
  database.php   database connection layer
public/
  index.php      application entry point
  fsms-prototype.html
sql/
  schema.sql     database schema
Documentation/
  Task2a_Requirements_Analysis.docx
  Task2b_System_Design.docx
  Figma_Prototype_Handoff.md
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
- [Documentation/Figma_Prototype_Handoff.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Figma_Prototype_Handoff.md) for prototype fidelity
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
- Keep features traceable to the documented scope in `Documentation/`.
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

- Requirements: [Task2a_Requirements_Analysis.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2a_Requirements_Analysis.docx)
- System design: [Task2b_System_Design.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2b_System_Design.docx)
- Figma/prototype handoff: [Figma_Prototype_Handoff.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Figma_Prototype_Handoff.md)
