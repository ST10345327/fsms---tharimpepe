# FSMS Functionality Audit

**Project:** Tharimpepe Feeding Scheme Management System (FSMS)  
**Date:** 22 June 2026  
**Purpose:** Final pre-submission verification of core system functionality

---

## 1. Executive Summary

The FSMS is a PHP MVC web application with a Capacitor-wrapped mobile shell, backed by MySQL. Core feeding-scheme modules are implemented and operational across the web portal, REST API, and mobile app.

| Surface | Stack | Status |
|---------|-------|--------|
| Web admin portal | PHP views, Bootstrap 5 | Operational |
| Mobile app | Capacitor 8, `mobile-shell/` HTML/JS | Operational |
| REST API | 66 PHP endpoints under `api/` | Operational |
| Database | MySQL (`fsms`) | Connected; core tables present |

Automated regression tests: **16/16 passing** (100% pass rate).

---

## 2. Verification Performed

| Check | Result |
|-------|--------|
| PHP test suite (`tests/run_all_tests.php`) | 16 passed, 0 failed |
| Database connectivity (`tools/check_db.php`) | OK |
| Database table audit (`tools/audit_db_tables.php`) | All required tables present |
| Application ping (`public/ping.php`) | OK |
| PHP syntax lint (all `api/*.php` files) | No syntax errors |

---

## 3. Core Module Status

### Fully Functional

| Module | Web | API | Mobile |
|--------|-----|-----|--------|
| Authentication (login, logout, refresh) | Yes | Yes | Yes |
| Users and role-based access | Yes | Yes | N/A |
| Beneficiaries (CRUD) | Yes | Yes | Yes |
| Attendance | Yes | Yes | Yes |
| Food stock | Yes | Yes | Yes |
| Donations | Yes | Yes | Yes |
| Volunteers and schedules | Yes | Yes | Yes |
| Reports and export | Yes | Yes | Yes |
| Dashboard summaries | Yes | Yes | Yes |
| Audit and activity logging | Yes | Yes | Yes |

### Partially Implemented

| Module | Notes |
|--------|-------|
| Messages | Web views exist; not linked in main navigation; no dedicated REST API |
| Notifications | Aggregates messages and stock alerts dynamically |
| Report scheduling | API exists; `ReportSchedules` table not yet migrated |
| Password reset | Change password works; self-service reset not implemented |

### Out of Scope (Schema Only)

The following entities exist in `sql/schema.sql` but have no application layer: blog posts, gallery, announcements, outreach programs, chatbot/FAQ, payment gateway, and public donor/volunteer portals.

---

## 4. Database

**Required tables verified present:** `users`, `beneficiaries`, `attendance`, `donations`, `volunteers`, `foodstock`, `activitylog`, `authtokens`, `Messages`, `auditlogs`.

**Optional/future tables not migrated:** `announcements`, `outreachprograms`, `chatbotfaq`, `paymenttransactions`, `reportschedules`.

---

## 5. Authentication and Roles

- Web: PHP session login via `SessionHandler.php`
- Mobile/API: Bearer tokens stored in `AuthTokens` table
- Roles: `admin`, `staff`, `volunteer`, `donor`
- Protected endpoints use `AuthMiddleware.php`
- Passwords hashed with `password_hash()` / verified with `password_verify()`

---

## 6. Known Limitations

1. Table name casing varies (`users` vs `Users`) — works on Windows/XAMPP; may need normalisation for Linux production.
2. Web notification panel is a static placeholder.
3. Volunteer names may appear in both `Users.FullName` and `Volunteers.FirstName/LastName`.
4. Some academic requirement documents reference a React SPA; the implemented frontend is PHP server-rendered views plus a Capacitor mobile shell.

---

## 7. Recommended Manual UAT

Before demonstration or submission, manually verify:

1. Web login with admin credentials (see `TESTING_DOCUMENTATION.md`)
2. Create, edit, and view a beneficiary record
3. Record attendance for a meal session
4. Add stock and record a donation
5. Register a volunteer and assign a schedule shift
6. Generate and export a report
7. Mobile app login and sync with the same backend

---

## 8. Conclusion

The FSMS meets the documented core scope for beneficiary management, attendance, food stock, donations, volunteers, reports, dashboard, and secure authentication. The system is suitable for academic submission and demonstration pending manual UAT of the workflows listed above.
