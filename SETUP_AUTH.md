# FSMS User Authentication - Setup & Testing Guide

## Quick Start

### 1. Create Database & Tables
```bash
# Access MySQL via XAMPP
# Create database:
CREATE DATABASE fsms_database;

# Then run the schema:
# Import sql/schema.sql into phpMyAdmin or MySQL CLI
mysql -u root fsms_database < sql/schema.sql
```

### 2. Create Admin & Test Accounts
```sql
-- Create admin account (password: admin123)
INSERT INTO Users (Username, Email, PasswordHash, Role) 
VALUES ('admin', 'admin@fsms.local', '$2y$10$N0KZEfz7y6j4/V6rwK1rXOqTpA1GC2.WB1mZVR/D4xFZwTg3OQjNq', 'admin');

-- Create test volunteer (password: test123)
INSERT INTO Users (Username, Email, PasswordHash, Role) 
VALUES ('volunteer1', 'volunteer@fsms.local', '$2y$10$1.VvfVJL1sVKsZ8Qh5K9puzL0qJZxKvL7LqN0T6xP8xF1qZq7U1yG6', 'volunteer');
```

### 3. Test the Application
Open your browser and go to:
```
http://localhost/fsms/public/index.php
```

### 4. Test Login Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Volunteer Account:**
- Username: `volunteer1`
- Password: `test123`

---

## Architecture Overview

### Three-Layer MVC Architecture

**View Layer** (`app/views/`)
- `login.php` - Login form with Bootstrap styling
- `register.php` - Registration form with validation UI
- `dashboard.php` - Protected dashboard page
- Uses SessionHandler for authentication checks

**Domain Layer** (`app/controllers/`, `app/models/`)
- `AuthController.php` - Orchestrates login, register, logout flows
- `User.php` - User business logic: authenticate, register, validate
- `SessionHandler.php` - Session middleware & helpers

**Data Layer** (`app/models/`, `sql/`)
- `User.php` - Database queries (parameterized, secure)
- `schema.sql` - Complete database schema with all entities
- `config/database.php` - PDO connection management

---

## Feature Summary

### ✅ Implemented

1. **User Registration** (HZ-USER-004)
   - Validates username, email, password
   - Hashes passwords with bcrypt
   - Prevents duplicate usernames/emails
   
2. **User Login** (HZ-USER-001, HZ-AUTH-001)
   - Authenticates credentials
   - Creates secure session
   - Redirects to dashboard
   
3. **Protected Pages** (HZ-AUTH-MIDDLEWARE-002)
   - Dashboard requires login
   - `SessionHandler.php` enforces authentication
   - Automatic redirect to login if not authenticated
   
4. **User Logout** (HZ-AUTH-004)
   - Destroys session safely
   - Redirects to login with success message
   
5. **Error Handling**
   - Validates all user inputs
   - Displays user-friendly error messages
   - Does not expose database details
   
6. **Security**
   - Parameterized MySQL queries (SQL injection prevention)
   - Password hashing with bcrypt
   - Session-based authentication
   - HTML entity encoding
   - IsActive flag for soft deletes

---

## File Structure

```
fsms/
├── app/
│   ├── controllers/
│   │   └── AuthController.php      (HZ-AUTH-001, 002, 003, 004)
│   ├── models/
│   │   └── User.php               (HZ-USER-001 to 007)
│   ├── helpers/
│   │   └── SessionHandler.php      (HZ-AUTH-MIDDLEWARE-001 to 005)
│   └── views/
│       ├── login.php              (HZ-UI-LOGIN-001 to 003)
│       ├── register.php           (HZ-UI-REGISTER-001 to 003)
│       └── dashboard.php          (HZ-AUTH-DASHBOARD-001 to 003)
├── config/
│   └── database.php               (HZ-DB-001)
├── public/
│   └── index.php                  (HZ-ENTRY-001 - Router)
├── sql/
│   └── schema.sql                 (All table definitions)
└── Documentation/
    ├── Task2a_Requirements_Analysis.docx
    └── Task2b_System_Design.docx
```

---

## Hazard Reference System

Every function has a **Hazard ID** for traceability back to requirements:

| Module | Hazard ID | Function | Purpose |
|--------|-----------|----------|---------|
| User Model | HZ-USER-001 | authenticate() | Verify login credentials |
| User Model | HZ-USER-002 | findByUsername() | Get user by username |
| User Model | HZ-USER-003 | findByEmail() | Get user by email |
| User Model | HZ-USER-004 | register() | Create new account |
| User Model | HZ-USER-005 | getUserById() | Get user by ID |
| User Model | HZ-USER-006 | deactivateUser() | Soft delete user |
| User Model | HZ-USER-007 | changePassword() | Update password |
| Auth Controller | HZ-AUTH-001 | login submission | Handle login form |
| Auth Controller | HZ-AUTH-002 | session creation | Create secure session |
| Auth Controller | HZ-AUTH-003 | register submission | Handle registration |
| Auth Controller | HZ-AUTH-004 | logout | Terminate session |
| Middleware | HZ-AUTH-MIDDLEWARE-001 | isUserLoggedIn() | Check auth status |
| Middleware | HZ-AUTH-MIDDLEWARE-002 | requireLogin() | Enforce authentication |
| Middleware | HZ-AUTH-MIDDLEWARE-003 | getCurrentUser() | Get user object |
| Middleware | HZ-AUTH-MIDDLEWARE-004 | hasRole() | Check user role |
| Middleware | HZ-AUTH-MIDDLEWARE-005 | getUserDisplayName() | Get username |

---

## Testing Checklist

- [ ] Database created and schema imported
- [ ] Test accounts inserted
- [ ] Can visit `/public/index.php` without error
- [ ] Redirects to login page when not authenticated
- [ ] Can register new account successfully
- [ ] Can login with registered account
- [ ] Dashboard displays personalized welcome
- [ ] Can logout and return to login
- [ ] Invalid credentials show error message
- [ ] Duplicate registration shows error message
- [ ] Session persists on page refresh
- [ ] Accessing dashboard without login redirects to login

---

## Password Hash Generation (for testing)

If you need to create more test accounts, generate hashes using PHP:

```php
<?php
// Generate hash for password "testpass"
echo password_hash("testpass", PASSWORD_BCRYPT);
?>
```

Then insert into database:
```sql
INSERT INTO Users (Username, Email, PasswordHash, Role) 
VALUES ('testuser', 'test@fsms.local', '$2y$10$...paste_hash_here...', 'volunteer');
```

---

## Next Steps for Development

1. **Volunteer Management Module** - Create CRUD for volunteers
2. **Beneficiary Management** - Register & manage beneficiaries
3. **Attendance Tracking** - Daily attendance recording
4. **Donation Management** - Track donations & reports
5. **Food Stock Management** - Inventory system
6. **Admin Dashboard** - Analytics & reporting

All modules will follow the same MVC pattern and use Hazard IDs for traceability.

---

**Questions?** Refer to `Task2a_Requirements_Analysis.docx` and `Task2b_System_Design.docx` for system requirements and design details.
