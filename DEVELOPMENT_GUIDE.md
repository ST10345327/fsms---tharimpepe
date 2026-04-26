# Development Guide

This guide translates the documents in `Documentation/` into a practical development workflow for this repository.

Primary references:
- [Task2a_Requirements_Analysis.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2a_Requirements_Analysis.docx)
- [Task2b_System_Design.docx](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Task2b_System_Design.docx)
- [Figma_Prototype_Handoff.md](/C:/Users/CASH/Desktop/fsms%20-%20tharimpepe/Documentation/Figma_Prototype_Handoff.md)

## Scope Guardrail

The documented system is a web-based feeding scheme management platform with these core capabilities:
- authentication
- beneficiary registration and maintenance
- attendance capture
- food stock tracking
- donation management
- volunteer registration and scheduling
- operational reporting and dashboard summaries

When preparing issues, pull requests, or new modules, each change should map back to one of those areas.

## Phase Split

The documentation supports a clean two-phase delivery model for development and review.

### Phase 1: Backend

Use the Requirements Analysis document as the source of truth for system behavior and the System Design document as the source of truth for data and process structure.

Backend phase goals:
1. Stabilize shared infrastructure
   - authentication
   - session handling
   - database access
   - validation
   - error handling
2. Strengthen domain modules
   - beneficiaries
   - attendance
   - food stock
   - donations
   - volunteers
   - schedules
3. Align implementation with documented entities and workflows
   - reduce schema naming drift
   - reduce controller/model workflow drift
   - preserve traceability to Task 2a and Task 2b
4. Improve reporting and dashboard data quality
5. Expand automated tests around documented workflows

Backend review lens:
- Does the change match a documented use case?
- Does the controller/model split stay clean?
- Does the database change support the documented logical model?
- Are tests or validation steps included?

### Phase 2: Frontend

Use the System Design document for input/request interaction structure and the Figma handoff for screen fidelity.

Frontend phase goals:
1. Standardize the shared shell
   - navbar
   - sidebar
   - topbar
   - cards
   - forms
   - tables
2. Align each major screen to documented workflows
   - dashboard
   - beneficiaries
   - attendance
   - stock and donations
   - volunteers and schedules
   - reports
3. Keep layouts responsive and reviewer-friendly
4. Keep view logic thin and avoid backend leakage into UI templates

Frontend review lens:
- Does the screen match the documented interaction?
- Is the layout consistent with the prototype handoff?
- Does the UI stay inside the documented module scope?
- Is the change isolated to views/assets unless a shared interface contract changed?

## Module Mapping

### Beneficiaries

Documentation expectation:
- register, update, search, deactivate beneficiary records

Implementation areas:
- `app/controllers/BeneficiaryController.php`
- `app/models/Beneficiary.php`
- `app/views/beneficiaries/`

### Attendance

Documentation expectation:
- record attendance per meal session and date
- generate attendance reports

Implementation areas:
- `app/controllers/AttendanceController.php`
- `app/models/Attendance.php`
- `app/views/attendance/`

Note:
- this is one of the places where implementation should be kept under review because the docs model attendance with meal sessions more explicitly than the current code.

### Stock and Donations

Documentation expectation:
- maintain stock records
- track donor and donation data
- report on stock and donor activity

Implementation areas:
- `app/controllers/FoodStockController.php`
- `app/controllers/DonationController.php`
- `app/models/FoodStock.php`
- `app/models/Donation.php`
- `app/views/food_stock/`
- `app/views/donations/`

### Volunteers and Scheduling

Documentation expectation:
- register volunteers
- assign volunteers to sessions
- view schedules and schedule reports

Implementation areas:
- `app/controllers/VolunteerController.php`
- `app/controllers/VolunteerScheduleController.php`
- `app/models/Volunteer.php`
- `app/models/VolunteerSchedule.php`
- `app/views/volunteers/`
- `app/views/schedules/`

### Reports and Dashboard

Documentation expectation:
- attendance, stock, donor, volunteer, and dashboard summary reporting

Implementation areas:
- `app/controllers/ReportsController.php`
- `app/controllers/DashboardController.php`
- `app/models/Reports.php`
- `app/models/Dashboard.php`
- `app/views/reports/`
- `app/views/dashboard/`

## Definition of "Ready for Development"

Before starting new backend or frontend feature work, make sure:
- the target module maps to documented scope
- the route/controller/model/view path is clear
- the database dependency is known
- tests are runnable locally
- any new screen or UI flow has a matching documented interaction

## Suggested GitHub Workflow

Use issue or branch names tied to modules, for example:
- `backend/beneficiary-validation`
- `backend/attendance-report-fixes`
- `frontend/dashboard-layout-refresh`
- `frontend/stock-donations-screen`

Each PR should describe:
- which documented module it touches
- whether it is backend, frontend, or shared
- what workflow from `Documentation/` it improves
- how it was tested

Recommended commit prefixes:
- `backend:` server-side logic, schema, tests, validation
- `frontend:` view/layout/prototype-alignment changes
- `shared:` repo setup, CI, docs, developer workflow

Recommended reviewer flow:
1. Review phase label first: backend or frontend.
2. Check the documentation reference in the PR.
3. Review commit-by-commit for module scope.
4. Confirm CI is green.
5. Confirm local/manual verification notes are present.

## Immediate Priorities

Good next development candidates:
1. Align attendance workflow more closely with the documented meal-session model.
2. Clean up module-to-database naming drift between the docs and the implementation.
3. Expand test coverage from auth/validation into beneficiary, attendance, stock, and scheduling flows.
4. Standardize the frontend shell using the Figma handoff and current prototype.
