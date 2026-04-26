---
name: wil-fsms
description: '**WORKFLOW SKILL** — Complete FSMS (Feeding Scheme Management System) development for Tharimpepe Feeding Scheme. USE FOR: Part 2 requirements analysis & system design deliverables; building MVC modules (Beneficiaries, Attendance, Food Stock, Donations, Volunteers, Reports); debugging PHP/MySQL issues; implementing role-based access control; creating responsive Bootstrap 5 UI; database schema design & normalization; comprehensive testing & validation. INVOKES: file system tools for code generation, terminal for PHP validation & Git operations, subagents for complex debugging. FOR SINGLE OPERATIONS: Use default agent for quick fixes or isolated tasks.'
---

# FSMS Development Workflow

## Part 2 Deliverables Checklist

### Task 2a: Requirements Analysis Document
- [ ] **Introduction/Problem Domain** - Complete specification of Tharimpepe Feeding Scheme problem domain with detailed analysis
- [ ] **Solution Domain** - Functional requirements specification with comprehensive details
- [ ] **UML Use Case Diagrams** - Complete use case diagrams for all system actors and functions
- [ ] **Cover page and index/content** - Professional document formatting
- [ ] **GitHub link** - Direct link to repository for code review

### Task 2b: System Design Document
- [ ] **Introduction** - System description using previous document information
- [ ] **High-Level Architectural Design** - Three-level/two-level/flat system specification, client-server positioning, database placement, functional block allocation
- [ ] **Low-Level Architectural Design** - Actor-function-database relationships diagram with use case functions and database table references
- [ ] **Input Interactions** - Complete GUI specification (Option 1: Hierarchical menu structure OR Option 2: Visual GUI design)
- [ ] **Request Interactions** - Service request menus and output parameter forms (Option 1: Hierarchical list OR Option 2: Visual GUI design)
- [ ] **Database Design** - ERD logical model, normalized tables (3NF), primary/foreign key specifications, relationship diagrams
- [ ] **System Reports Design** - Report identification, data sources, presentation methods (screen/print/PDF)
- [ ] **Cover page and index/content** - Professional document formatting

## Acceptance Criteria

### Requirements Analysis Document (Task 2a)
- **Problem Domain**: Contains detailed study of Tharimpepe Feeding Scheme operations, challenges, and current processes
- **Functional Requirements**: Comprehensive list covering all 7 modules (Beneficiaries, Attendance, Food Stock, Donations, Volunteers, Reports, User Management)
- **Use Case Diagrams**: Visual diagrams showing Admin/Volunteer actors and all system interactions
- **Completeness**: All requirements traceable to system functions and database entities

### System Design Document (Task 2b)
- **Architecture**: Clear specification of MVC architecture with PHP 8 backend, MySQL 8 database, Bootstrap 5 frontend
- **Low-Level Design**: Complete actor-function-database relationship diagram with all use cases and table references
- **GUI Design**: Either hierarchical menu structure OR complete visual mockups for all input/output interactions
- **Database Design**: Normalized ERD with all 7 tables, proper relationships, sample data, and key specifications
- **Reports Design**: Specification of all required reports with data sources and presentation methods

## Testing & Validation Steps

### Document Validation
1. **Cross-Reference Check**: Verify all requirements in Task 2a are addressed in Task 2b design
2. **Completeness Audit**: Use checklist above to ensure all sections are present and detailed
3. **Technical Accuracy**: Validate that design matches implemented PHP/MySQL/Bootstrap 5 stack
4. **Peer Review**: Have another developer review documents for clarity and completeness

### Code Validation
1. **PHP Syntax Check**: Run `php -l` on all PHP files (controllers, models, views)
2. **Database Schema**: Execute schema.sql and verify all tables create successfully
3. **MVC Structure**: Confirm proper separation between controllers (logic), models (data), views (presentation)
4. **Security Validation**: Test prepared statements, password hashing, CSRF protection, role-based access

### Functional Testing
1. **Authentication**: Test login/logout with Admin and Volunteer roles
2. **CRUD Operations**: Test Create/Read/Update/Delete for all 7 modules
3. **Data Integrity**: Verify foreign key constraints and data validation
4. **UI Responsiveness**: Test Bootstrap 5 layouts on different screen sizes
5. **Report Generation**: Verify all reports display correct data and export functionality

## FSMS Coding Standards

### PHP Standards
- **PDO Prepared Statements**: ALL database queries use prepared statements with bound parameters
- **Password Security**: `password_hash(PASSWORD_BCRYPT)` for storage, `password_verify()` for validation
- **Input Validation**: Server-side validation with `htmlspecialchars()`, `filter_var()`, `intval()`
- **Session Management**: `session_start()` at top of every file using `$_SESSION`
- **MVC Pattern**: Controllers handle HTTP logic, Models handle DB queries, Views handle HTML
- **CSRF Protection**: Token validation on all POST forms

### HTML/Bootstrap 5 Standards
- **Semantic Classes**: Use Bootstrap 5 layout classes (`container-fluid`, `row`, `col-*`)
- **Form Accessibility**: Proper `<label for="id">` linking to form controls
- **Validation Feedback**: `is-invalid`/`invalid-feedback` classes for error display
- **Table Styling**: `table table-hover table-striped` for data tables

### JavaScript Standards
- **Client Validation**: Form validation before submission (server validation always present)
- **AJAX Operations**: Use `fetch()` for asynchronous requests
- **User Confirmation**: `confirm()` dialogs for destructive actions

### Security Standards
- **Output Escaping**: `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` for all HTML output
- **Error Handling**: Catch `PDOException`, never expose raw SQL errors
- **POST Redirect**: Always redirect after POST to prevent resubmission
- **Session Cleanup**: `session_destroy()` on logout

## Debugging Protocol

### 6-Step Process
1. **IDENTIFY**: State the bug/symptom in one sentence
2. **LOCATE**: Find exact file, function, line number, trace call chain
3. **ROOT CAUSE**: Explain WHY it's broken in plain language
4. **FIX**: Provide minimal targeted fix with context (5 lines above/below)
5. **VERIFY**: State how to confirm the fix works
6. **PREVENT**: Flag patterns that could recur elsewhere

### Common FSMS Bugs
- **Session Issues**: Missing `session_start()` before `$_SESSION` usage
- **SQL Joins**: Missing ON clause causing cartesian products
- **Attendance Constraints**: UNIQUE violation on (BeneficiaryID, SessionDate)
- **PDO Results**: Check `empty()` not `=== false` for fetchAll() results
- **Bootstrap Modals**: JS loaded before DOM ready
- **POST Data**: Missing redirect after form submission
- **Foreign Keys**: Constraint failures when deactivating parent records
- **Date Formats**: PHP `date('Y-m-d')` vs MySQL DATE column mismatch

## Project Architecture

### MVC Structure
```
fsms/
├── app/
│   ├── controllers/     # HTTP logic, validation, routing
│   ├── models/         # PDO database queries, business logic
│   └── views/          # HTML templates, Bootstrap UI
├── config/
│   └── db.php          # PDO singleton connection
├── public/
│   ├── index.php       # Front controller, URL routing
│   └── assets/         # CSS, JS, images
├── sql/
│   ├── schema.sql      # 7 table CREATE statements
│   └── seed.sql        # Sample data
└── .htaccess           # Apache URL rewriting
```

### Database Schema (7 Tables)
1. **Users** - UserID (PK), Username, PasswordHash, FullName, Email, Role (Admin/Volunteer), IsActive, CreatedAt
2. **Beneficiaries** - BeneficiaryID (PK), FirstName, LastName, DateOfBirth, Gender, GuardianName, ContactNumber, Address, RegistrationDate, IsActive, RegisteredBy (FK→Users)
3. **Attendance** - AttendanceID (PK), BeneficiaryID (FK), SessionDate, IsPresent, RecordedBy (FK→Users)
4. **FoodStock** - StockID (PK), ItemName, Category, Quantity (DECIMAL), UnitOfMeasure, MinimumThreshold, ExpiryDate, LastUpdated, UpdatedBy (FK→Users)
5. **Donations** - DonationID (PK), DonorName, DonationType, ItemDescription, Quantity, AmountRand, DonationDate, LinkedStockID (FK→FoodStock), RecordedBy (FK→Users)
6. **Volunteers** - VolunteerID (PK), UserID (FK optional), FullName, ContactNumber, Email, Availability, IsActive, RegisteredDate
7. **VolunteerSessions** - SessionID (PK), VolunteerID (FK), SessionDate, Role, AttendedSession

### Roles & Permissions
- **Admin**: Full CRUD access to all modules
- **Volunteer**: Read access + attendance recording; cannot manage users/reports/donations

## Quick Reference

### Common Tasks
- **New Module**: Model → Controller → View → Route (in that order)
- **Database Change**: Update schema.sql → Run migration → Update affected models
- **UI Component**: Bootstrap 5 classes → Test responsiveness → Add JavaScript if needed
- **Security Issue**: Check input validation → Verify prepared statements → Test edge cases

### File Templates
- **Controller**: `class XController { public function index() {}, create() {}, update() {}, delete() {} }`
- **Model**: `class X { public static function getAll() {}, getById($id) {}, create($data) {}, update($id, $data) {}, delete($id) {} }`
- **View**: Bootstrap 5 structure with `<?php foreach() ?>` loops and form validation

### Validation Commands
- **PHP Syntax**: `php -l app/controllers/*.php && php -l app/models/*.php`
- **Database**: `mysql -u root -p fsms < sql/schema.sql`
- **Routes**: Test all URLs in browser after changes
- **Security**: Check for SQL injection, XSS, CSRF vulnerabilities

### Development Order
1. **Foundation**: schema.sql → seed.sql → db.php → index.php → .htaccess → header.php/footer.php → AuthController/login.php
2. **Core Modules**: Dashboard → Beneficiaries → Attendance → Food Stock → Donations → Volunteers → Reports
3. **Enhancements**: Activity logging → Role-based access → UI improvements → Report exports

## Response Format

### Code Generation
- **File Header**: `// File: app/controllers/BeneficiaryController.php`
- **Complete Files**: Never truncate with "rest of code remains the same"
- **Comments**: Inline comments only for non-obvious logic
- **Usage Guide**: 2-4 bullet points after code block

### Explanations
- **Lead with Answer**: Answer first, explanation second
- **Structure**: Numbered steps for processes, bullets for lists
- **Nesting**: Maximum 3 levels

### Feature Development
- **Order**: SQL → Model → Controller → View
- **Confirmation**: State the accessible URL/route
- **Dependencies**: List required components

### Code Review
- **Issues First**: Numbered problem list before fixes
- **Minimal Changes**: Preserve existing style where standards allow