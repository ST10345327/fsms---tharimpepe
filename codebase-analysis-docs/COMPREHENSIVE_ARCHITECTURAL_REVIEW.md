# Comprehensive Architectural Review — FSMS (Tharimpepe Feeding Scheme)

**Date:** 2026-06-18  
**Reviewer:** Lead Software Architect  
**Scope:** Full system audit — Database, Backend, Web Frontend, Mobile Shell, API Layer, Security  
**Referenced Documents:** Task 2 Requirements Analysis, Task 2b System Design, AUTH_AUDIT.md

---

## 1. EXECUTIVE SUMMARY

The current codebase has **significant architectural deficiencies** that must be addressed before proceeding with further feature development. The two most critical problems are:

1. **The mobile app and web app exist as entirely separate, non-communicating systems** with their own authentication mechanisms, data storage, and logic.
2. **The database schema has normalization issues, duplicate fields, missing tables, and security vulnerabilities.**

This review identifies **27 distinct issues** across database, backend, frontend, mobile, and security layers, with prioritized remediation plan.

---

## 2. DATABASE ANALYSIS

### 2.1 Current Schema Review

| Table | Status | Issues |
|-------|--------|--------|
| Users | Present | Dual status (Status + IsActive), Role as ENUM instead of normalized roles table |
| Volunteers | Present | Duplicates user personal data (FirstName/LastName vs FullName on Users) |
| Beneficiaries | Present | No foreign key to Users (who registered them), no audit trail |
| MealSession | Present | OK structure |
| Attendance | Present | OK structure |
| Donations | Present | **No foreign key to Users** — uses DonorName string instead of UserID |
| FoodStock | Present | OK structure |
| Messages | Present | No Subject index, no parent_message_id for threading |
| BlogPosts | Present | Missing FeaturedImage URL field per requirements |
| Gallery | Present | OK structure |
| ActivityLog | Present | OK structure |
| VolunteerAvailability | Present | OK |
| VolunteerSchedules | Present | OK |
| FoodDistribution | Present | OK |

### 2.2 Missing Tables

| Required Table | Description | Priority |
|----------------|-------------|----------|
| `roles` | Role definitions (admin, volunteer, donor, staff, guest) | HIGH |
| `user_roles` | Many-to-many user-role assignment | HIGH |
| `outreach_programs` | Community outreach program management | HIGH |
| `program_volunteers` | Volunteer-program assignment | HIGH |
| `announcements` | System announcements (distinct from blog posts) | MEDIUM |
| `chatbot_faq` | FAQ data for AI chatbot | MEDIUM |
| `auth_tokens` | Token-based authentication for mobile app | HIGH |
| `password_resets` | Password reset tokens | MEDIUM |
| `payment_transactions` | Payment gateway transactions | MEDIUM |

### 2.3 Normalization Issues

**Issue N1: Duplicate User Personal Data**
```sql
-- Users table stores FullName
Users.FullName VARCHAR(255)

-- Volunteers table duplicates with FirstName/LastName
Volunteers.FirstName VARCHAR(100)
Volunteers.LastName VARCHAR(100)
```
**Fix:** Remove FirstName/LastName from Volunteers, reference Users.FullName. OR split Users into first_name/last_name.

**Issue N2: Dual Status System**
```sql
Status ENUM('active', 'inactive') DEFAULT 'active',
IsActive BOOLEAN DEFAULT TRUE  -- DUPLICATE
```
**Fix:** Remove `IsActive`, consolidate to `Status` only.

**Issue N3: Donor as String Instead of Foreign Key**
```sql
DonorName VARCHAR(150) NOT NULL  -- Should be UserID FK
```
**Fix:** Add `UserID INT` FK to Users, keep DonorName as denormalized display field.

**Issue N4: Missing Foreign Keys**
- `Donations` → `Users` (missing UserID)
- `BlogPosts` → missing FeaturedImage
- `Beneficiaries` → missing CreatedBy (UserID FK)

### 2.4 Missing Indexes

| Table | Column(s) | Why Needed |
|-------|-----------|------------|
| Messages | SenderID, RecipientID | Frequent JOIN queries |
| Messages | IsRead | Filter unread messages |
| BlogPosts | PublishDate | Sort/filter by date |
| BlogPosts | AuthorID | JOIN with Users |
| Donations | UserID | User donation history |
| Beneficiaries | Status | Filter active/inactive |
| Attendance | (BeneficiaryID, SessionDate) | Composite UNIQUE for dedup |

---

## 3. BACKEND ANALYSIS (PHP MVC)

### 3.1 Authentication System Issues

**Critical Bug — DB Connection Skipped for Login POST**
```php
// AuthController.php line 21
if (!($action === 'login' && $_SERVER["REQUEST_METHOD"] === "POST")) {
    try { $db = getDBConnection(); } catch (Exception $dbError) { ... }
}
```
This means **database authentication NEVER works for login**. Only demo fallback works.

**Critical Bug — Wrong Column Name in INSERT**
```php
// UserController.php line 162
$query = "INSERT INTO Users (..., Password, ...)";  // Column is 'PasswordHash'
```

**Security Issue — Plaintext Password Logging**
```php
// UserController.php line 174
ActivityLog::log(..., "Created user: $username (Temp pwd: $password)");
```

**Security Issue — No Session Regeneration**
`session_regenerate_id(true)` is never called after login, enabling session fixation attacks.

**Security Issue — No Session Timeout**
Sessions persist indefinitely with no last-activity check.

### 3.2 SQL Injection Risks

```php
// UserController.php lines 108-116 — MIXED prepared + string interpolation
$query .= " AND Role = '" . $conn->quote($filters['role']) . "'";
$query .= " AND (Username LIKE '" . $conn->quote($search) . "' ...)";
```
The code uses `$conn->quote()` which is **not parameterized**. Some parts use prepared statements, others don't.

### 3.3 Code Quality Issues

1. **Duplicate login code blocks** — Two `if ($action === 'login')` blocks (lines 34-58 and 65-124), both handling the same logic.
2. **No separation of concerns** — AuthController mixes presentation logic with business logic.
3. **No proper error response format** — Mixed HTML output and redirect-based error handling.
4. **No API-only endpoints** — All controllers output HTML, not JSON.

---

## 4. MOBILE APP ANALYSIS (Capacitor/WebView)

### 4.1 Architecture Failure — Zero Backend Integration

```
Current Architecture:
mobile-shell/index.html → sessionStorage → 100% client-side auth
                    ↓
              NO API CALLS TO PHP BACKEND
                    ↓
              Static demo data only

Required Architecture:
mobile-shell/index.html → REST API (api/) → PHP Backend → MySQL
                    ↓
              Token-based auth
                    ↓
              Real data from shared database
```

### 4.2 Detailed Mobile Issues

| ID | Issue | Severity | File |
|----|-------|----------|------|
| M1 | No backend API integration | BLOCKER | all mobile-shell/ |
| M2 | sessionStorage wiped on app restart | BLOCKER | index.html:66 |
| M3 | Hardcoded plaintext credentials | CRITICAL | index.html:52-57 |
| M4 | Cookie sessions incompatible with WebView | CRITICAL | All auth flow |
| M5 | No CORS headers on backend | HIGH | bootstrap.php |
| M6 | No token refresh mechanism | HIGH | — |
| M7 | allowMixedContent misconfiguration | MEDIUM | capacitor.config.json |
| M8 | No auth state restoration on relaunch | HIGH | shared.js:123 |
| M9 | cleartext HTTP with no HTTPS | CRITICAL | capacitor.config.json |
| M10 | Missing HttpOnly/Secure/SameSite cookies | MEDIUM | SessionHandler.php |

---

## 5. REQUIREMENTS GAP ANALYSIS

### 5.1 Missing Features (From Task Requirements)

| Requirement | Status | Action Needed |
|-------------|--------|---------------|
| Role-based access control (RBAC) | Partial | Create roles/user_roles tables |
| Password reset flow | Missing | Create password_resets table + endpoints |
| Profile update | Partial | UserController has update but no password change |
| Donation management with UserID FK | Missing | Alter Donations table |
| Outreach programs CRUD | Missing | Create outreach_programs table + CRUD |
| Volunteer program assignment | Missing | Create program_volunteers table |
| Blog with featured images | Partial | Add FeaturedImage column |
| Announcements (separate from blog) | Missing | Create announcements table |
| Gallery management | Partial | Working |
| Contact messaging system | Partial | Messages exists but no admin reply |
| AI Chatbot FAQ | Missing | Create chatbot_faq table + API |
| Reports (PDF/Excel export) | Missing | Backend reporting engine |
| Donation reports (daily/monthly/annual) | Missing | Report queries |
| Volunteer participation tracking | Partial | VolunteerSchedules exists |
| Impact reporting | Missing | Cross-table aggregation queries |

---

## 6. PROPOSED ARCHITECTURAL REMEDIATION

### 6.1 Three-Layer Architecture Implementation

```
LAYER 1: VIEW LAYER
├── Web UI (PHP Views + Bootstrap 5)
│   ├── Public Website
│   ├── User Dashboard
│   ├── Volunteer Dashboard
│   └── Admin Dashboard
└── Mobile UI (Capacitor WebView)
    ├── React Native / HTML5 Shell
    └── Responsive mobile layouts

LAYER 2: DOMAIN LAYER (API)
├── /api/auth/*          — Authentication endpoints
├── /api/users/*         — User management
├── /api/volunteers/*    — Volunteer management
├── /api/donations/*     — Donation tracking
├── /api/outreach/*      — Outreach programs
├── /api/blogs/*         — Blog posts
├── /api/gallery/*       — Gallery management
├── /api/messages/*      — Contact/messaging
├── /api/reports/*       — Report generation
├── /api/chatbot/*       — AI chatbot
└── /api/announcements/* — Announcements

LAYER 3: DATA LAYER
└── MySQL Database (Single source of truth)
    ├── Shared by ALL applications
    ├── Accessed ONLY through API
    └── All business logic in Domain Layer
```

### 6.2 Database Schema Migration Plan

**Phase 1 — Critical Fixes (Must Do)**
1. Add `AuthTokens` table for token-based auth
2. Add `PasswordResets` table
3. Fix `Users` — remove `IsActive`, consolidate to `Status`
4. Fix `Donations` — add `UserID` FK
5. Add missing indexes

**Phase 2 — New Feature Tables**
6. Create `OutreachPrograms` table
7. Create `ProgramVolunteers` table
8. Create `Announcements` table
9. Create `ChatbotFAQ` table
10. Create `PaymentTransactions` table

**Phase 3 — Normalization**
11. Remove duplicated Volunteer personal data
12. Add audit fields to all tables

### 6.3 Auth Architecture — Unified Token-Based System

```
ALL CLIENTS (Web + Mobile) → Token-Based Auth API
                                    ↓
                    /api/auth/login returns JWT/bearer token
                                    ↓
                    Token stored in HttpOnly cookie (web)
                    OR @capacitor/preferences (mobile)
                                    ↓
                    Every request includes Authorization header
                                    ↓
                    API middleware validates token on each request
                                    ↓
                    Token expiry + refresh token rotation
```

### 6.4 API Response Standard

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    "id": 1,
    "type": "user",
    "attributes": {}
  },
  "meta": {
    "timestamp": "2026-06-18T09:00:00Z",
    "request_id": "abc-123"
  }
}
```

---

## 7. PRIORITIZED REMEDIATION PLAN

### Phase 1: 🔴 IMMEDIATE (Week 1) — Authentication & Database Foundation

| # | Task | Files | Effort | Risk |
|---|------|-------|--------|------|
| 1 | Fix DB connection for login POST | AuthController.php | 30 min | 🔴 High |
| 2 | Fix "Password" → "PasswordHash" column ref | UserController.php | 15 min | 🔴 High |
| 3 | Create api/auth/login.php (token-based) | api/auth/login.php | 4 hrs | 🔴 High |
| 4 | Add AuthTokens table | sql/schema.sql | 30 min | 🔴 High |
| 5 | Add CORS headers to backend | bootstrap.php | 30 min | 🔴 High |
| 6 | Consolidate Status/IsActive | sql/schema.sql, all refs | 1 hr | 🟠 Med |
| 7 | Add UserID FK to Donations | sql/schema.sql | 1 hr | 🟠 Med |
| 8 | Add missing indexes | sql/schema.sql | 30 min | 🟢 Low |

### Phase 2: 🟠 CRITICAL (Week 2) — Mobile App Integration

| # | Task | Files | Effort | Risk |
|---|------|-------|--------|------|
| 9 | Update Capacitor config (server.url) | capacitor.config.json | 30 min | 🟠 Med |
| 10 | Replace sessionStorage with Preferences | shared.js, index.html | 4 hrs | 🟠 Med |
| 11 | Add auth state restoration on app start | shared.js | 2 hrs | 🟠 Med |
| 12 | Create API validate endpoint | api/auth/validate.php | 2 hrs | 🟠 Med |
| 13 | Implement token refresh mechanism | api/auth/refresh.php | 3 hrs | 🟠 Med |
| 14 | Remove hardcoded credentials | index.html | 30 min | 🟢 Low |

### Phase 3: 🟡 STANDARD (Week 3) — Missing Features

| # | Task | Effort |
|---|------|--------|
| 15 | Create OutreachPrograms CRUD | 6 hrs |
| 16 | Create Announcements CRUD | 4 hrs |
| 17 | Create ChatbotFAQ + AI API | 8 hrs |
| 18 | Add password reset flow | 4 hrs |
| 19 | Add session timeout + regeneration | 2 hrs |
| 20 | Add rate limiting middleware | 3 hrs |

### Phase 4: 🟢 ENHANCEMENT (Week 4) — Reporting & Polish

| # | Task | Effort |
|---|------|--------|
| 21 | Build donation reports (PDF/Excel) | 8 hrs |
| 22 | Build volunteer reports | 6 hrs |
| 23 | Build impact reports | 6 hrs |
| 24 | SQL injection hardening | 4 hrs |
| 25 | CSRF protection across all forms | 3 hrs |
| 26 | Remove plaintext password logging | 1 hr |
| 27 | Normalize volunteer/user data | 3 hrs |

---

## 8. QUALITY ASSURANCE CHECKLIST

Each feature must pass the following before being marked complete:

### Security
- [ ] No hardcoded credentials in production code
- [ ] All passwords hashed with `password_hash()`
- [ ] All SQL queries use prepared statements
- [ ] Input validation on all endpoints
- [ ] Output encoding/escaping on all output
- [ ] CSRF protection on state-changing operations
- [ ] Rate limiting on auth endpoints
- [ ] CORS properly configured
- [ ] Session security (regeneration, timeout, HttpOnly)

### Data Integrity
- [ ] Foreign keys enforced with cascading rules
- [ ] No duplicate columns across tables
- [ ] Audit fields (created_at, updated_at) on all tables
- [ ] Proper indexes on foreign key columns
- [ ] Transactions used for multi-table operations

### Mobile Compatibility
- [ ] Token-based auth (not cookie-based)
- [ ] Persistent storage (not sessionStorage)
- [ ] Offline grace period for auth validation
- [ ] CORS-enabled API endpoints
- [ ] Responsive layout for mobile screens

### Web Compatibility
- [ ] PDO prepared statements for all queries
- [ ] Proper error handling (no raw exceptions to user)
- [ ] Session-based auth for browser clients
- [ ] Bootstrap 5 responsive design
- [ ] Accessibility support

---

## 9. SINGLE SOURCE OF TRUTH ENFORCEMENT

```
          ┌─────────────────────────────────────┐
          │         MySQL Database (fsms)        │
          │         SINGLE SOURCE OF TRUTH       │
          └──────────┬──────────────────────────┘
                     │
          ┌──────────▼──────────┐
          │    REST API Layer    │
          │   /api/* endpoints   │
          │   All business logic │
          └──────────┬──────────┘
                     │
          ┌──────────┴──────────┬──────────────┐
          ▼                     ▼              ▼
    ┌──────────┐        ┌──────────┐    ┌──────────┐
    │ Web App  │        │ Mobile   │    │ Future   │
    │ (PHP)    │        │ (Cap.)   │    │ Integ.   │
    └──────────┘        └──────────┘    └──────────┘
```

**Rules:**
- The database is the ONLY persistent data store
- The API is the ONLY way to access the database
- No direct database access from frontend code
- No separate SQLite/JSON/localStorage for mobile
- All clients must authenticate through the API
- All business logic resides in the API/domain layer

---

## 10. CONCLUSION

The system has a solid foundation concept but requires significant architectural remediation to become production-ready. The **top 3 priority actions** are:

1. **Fix the authentication system** — Create a unified token-based API that both web and mobile can use
2. **Fix the database schema** — Add missing tables, foreign keys, and fix normalization issues
3. **Integrate the mobile app** — Connect mobile-shell to the API instead of using client-side demo data

The remediation plan is designed to be executed incrementally, with each phase building on the previous one. The architecture outlined here ensures the system will be maintainable, scalable, secure, and consistent across all platforms.