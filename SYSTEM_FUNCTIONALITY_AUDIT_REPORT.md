# System Functionality Audit Report
**Project:** Tharimpepe Feeding Scheme Management System (FSMS)  
**Date:** 2026-06-21  
**Scope:** End-to-end audit of web app, Android/Capacitor mobile shell, API layer, database, auth, and sync  
**Status:** Phase 1–11 audit completed; critical runtime issues repaired

---

## Executive Summary

This audit confirms that the **actual codebase** is a **PHP MVC web application** with a **Capacitor-wrapped vanilla HTML/JS mobile shell** backed by **MySQL** — not a React SPA as described in some requirement documents.

| Surface | Stack | Status |
|---------|-------|--------|
| Web admin portal | PHP views + Bootstrap 5 | ✅ Operational (core modules) |
| Mobile app | Capacitor 8 + `mobile-shell/` HTML/JS | ✅ Operational (core modules) |
| REST API | 66 PHP endpoints under `api/` | ✅ Operational |
| Database | MySQL (`fsms`) | ✅ Connected; core tables present |

**Core feeding-scheme modules work end-to-end:** beneficiaries, attendance, food stock, donations, volunteers, reports, dashboard, users, audit/security.

**Community/public NGO features from the audit prompt are NOT implemented** (blog, gallery, outreach programs, chatbot, payment gateway, public user portal). These exist only as database schema definitions in `sql/schema.sql`.

---

## 1. Actual Tech Stack (Verified)

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.x, PDO, MVC (`app/controllers`, `app/models`, `app/views`) |
| Web UI | Server-rendered PHP + Bootstrap 5 + custom CSS/JS |
| Mobile UI | Static HTML in `mobile-shell/` + Capacitor Android |
| API | File-based REST JSON endpoints (`api/*.php`) |
| Auth (web) | PHP sessions via `SessionHandler.php` |
| Auth (mobile/API) | Bearer tokens in `AuthTokens` table |
| Database | MySQL/MariaDB |
| Tests | PHP test runner — **16/16 passing** |
| CI | GitHub Actions (PHP lint + tests) |

---

## 2. Issues Discovered

### 🔴 Critical (Fixed)

| ID | Issue | Location | Impact |
|----|-------|----------|--------|
| C-01 | Migration parse error (missing semicolon) | `migrations/20260619_ensure_schema.php:5` | Migration could not run |
| C-02 | Missing API endpoints called by mobile | `mobile-shell/reports.html` → `/api/reports/history.php`, `/api/reports/download.php` | Reports history/download returned 404 |
| C-03 | Report preview data mismatch | Mobile used `result.data` (row array) instead of `result.summary` | Report modal showed zeros |
| C-04 | Report export broken on mobile | Mobile expected `result.url`; export returns file/JSON directly | Export button always failed |
| C-05 | Mobile placeholder actions | Stock update, beneficiary edit, volunteer detail | Non-functional buttons |
| C-06 | `Messages` table missing from live DB | Notifications + messaging module | DB errors on notification fetch |
| C-07 | Volunteer report summary bug | `generate.php` referenced undefined `$summary['total_volunteers']` | PHP notice / wrong avg hours |
| C-08 | Financial report type missing | `generate.php` accepted `financial` in whitelist but had no case | Empty financial reports |

### 🟠 Medium (Partially Addressed / Documented)

| ID | Issue | Status |
|----|-------|--------|
| M-01 | No password reset flow | Not implemented (web + mobile show admin-contact message) |
| M-02 | Messages module not in main web nav | Views exist at `app/views/messages/*` but not linked in `layout-header.php` |
| M-03 | No dedicated `/api/messages/*` endpoints | Web uses MVC controllers only; mobile uses notifications API |
| M-04 | Table name case inconsistency | Code mixes `users`/`Users`, `messages`/`Messages` — works on Windows/XAMPP, fragile on Linux |
| M-05 | `ReportSchedules` table missing | Schedule API returns empty / 503 on create |
| M-06 | Web notifications offcanvas is static placeholder | Shows "No notifications" always |
| M-07 | Volunteer name duplication (`Users.FullName` vs `Volunteers.FirstName/LastName`) | Documented in `REQUIRED_CORRECTIONS_LIST.md` — not normalized |

### 🟡 Low / Informational

| ID | Issue |
|----|-------|
| L-01 | Outdated README files (`api/README.md`, `frontend/README.md`) |
| L-02 | Duplicate health stub at `public/api/system/health.php` |
| L-03 | Missing indexes documented in `REQUIRED_CORRECTIONS_LIST.md` |
| L-04 | `docs/academic/*.pdf` referenced but not in repo |

### ❌ Not Implemented (Schema Only)

These entities from the audit prompt **do not have models, controllers, views, or API endpoints**:

- Blog Posts (`BlogPosts`)
- Gallery (`Gallery`)
- Announcements (`Announcements`)
- Outreach Programs (`OutreachPrograms`)
- Chatbot / FAQ (`ChatbotFAQ`)
- Payment Transactions (`PaymentTransactions`)
- Public user registration portal
- React frontend

---

## 3. Fixes Applied (This Audit Session)

| Fix | Files Changed |
|-----|---------------|
| Migration syntax repair | `migrations/20260619_ensure_schema.php` |
| Added report history API | `api/reports/history.php` (new) |
| Added report download resolver API | `api/reports/download.php` (new) |
| Report generation logging for history | `api/reports/generate.php`, `api/reports/export.php` |
| Financial report case + summary fields | `api/reports/generate.php` |
| Volunteer avg-hours bug fix | `api/reports/generate.php` |
| Beneficiary/attendance summary fields for mobile | `api/reports/generate.php` |
| Messages table migration | `migrations/20260621_ensure_messages_table.php` (new, executed) |
| Notifications query table name fix | `api/notifications/list.php` |
| Mobile reports: summary preview + authenticated download | `mobile-shell/reports.html` |
| Mobile stock update wired to API | `mobile-shell/stock.html` |
| Mobile beneficiary edit wired to API | `mobile-shell/beneficiaries.html` |
| Mobile volunteer detail modal | `mobile-shell/volunteers.html` |
| DB audit utility | `tools/audit_db_tables.php`, `tools/check_db.php` |

---

## 4. Module-by-Module Verification

### ✅ Fully Functional

| Module | Web | API | Mobile | DB | Sync |
|--------|-----|-----|--------|----|----|
| Authentication (login/logout/refresh) | ✅ | ✅ | ✅ | ✅ | ✅ (shared backend) |
| Users / RBAC | ✅ | ✅ | N/A | ✅ | ✅ |
| Beneficiaries CRUD | ✅ | ✅ | ✅ (list, detail, edit) | ✅ | ✅ |
| Attendance | ✅ | ✅ | ✅ | ✅ | ✅ |
| Food Stock | ✅ | ✅ | ✅ (list, alerts, update) | ✅ | ✅ |
| Donations | ✅ | ✅ | ✅ (record, history) | ✅ | ✅ |
| Volunteers | ✅ | ✅ | ✅ (register, list, detail) | ✅ | ✅ |
| Reports | ✅ | ✅ | ✅ (generate, export, history) | ✅ | ✅ |
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Audit / Security | ✅ (report view) | ✅ | ✅ (`security.html`) | ✅ | ✅ |
| Activity Log | ✅ | ✅ | via dashboard | ✅ | ✅ |

### ⚠️ Partial

| Module | Notes |
|--------|-------|
| Messages | Web views exist; not in sidebar nav; no REST API; `Messages` table now created |
| Notifications | Aggregates messages + dynamic stock alerts; no dedicated table |
| Report Scheduling | API exists; `ReportSchedules` table not migrated |
| Password Management | Change password works; reset flow not implemented |

### ❌ Not Built

Blog, Gallery, Announcements, Outreach, Chatbot, Payment gateway, Public donor/volunteer portal, Sponsors

---

## 5. API Endpoint Audit

**Total endpoints:** 66 PHP files in `api/` (excluding middleware)

| Category | Count | Auth Protected | Status |
|----------|-------|----------------|--------|
| Auth | 4 | Public (login) / Protected | ✅ |
| Users | 4 | ✅ | ✅ |
| Beneficiaries | 8 | ✅ | ✅ |
| Attendance | 9 | ✅ | ✅ |
| Meal Sessions | 3 | ✅ | ✅ |
| Stock | 9 | ✅ | ✅ |
| Donations | 5 | ✅ | ✅ |
| Volunteers | 7 | ✅ | ✅ |
| Dashboard | 5 | ✅ | ✅ |
| Reports | 6 | ✅ | ✅ (history/download added) |
| Notifications | 2 | ✅ | ✅ |
| Audit | 2 | ✅ (admin/staff) | ✅ |
| Activity | 1 | ✅ | ✅ |
| System | 1 | Public | ✅ |

**PHP syntax check:** All 66 API files pass `php -l` with no errors.

**Automated tests:** 16/16 passing (`tests/run_all_tests.php`)

---

## 6. Database Verification

**Connection:** ✅ OK (`tools/check_db.php`)

### Core Tables (Required — All Present)

`users`, `beneficiaries`, `attendance`, `donations`, `volunteers`, `foodstock`, `activitylog`, `authtokens`, `Messages`, `auditlogs`

### Optional / Future Tables (Missing from Live DB)

`blogposts`, `gallery`, `announcements`, `outreachprograms`, `chatbotfaq`, `paymenttransactions`, `reportschedules`

### Migrations

| File | Status |
|------|--------|
| `20260619_ensure_schema.php` | ✅ Fixed (syntax) |
| `20260619_add_missing_columns.php` | ✅ Valid |
| `20260620_create_auth_tokens_table.php` | ✅ Valid |
| `20260621_create_audit_logs_table.php` | ✅ Valid |
| `20260621_ensure_messages_table.php` | ✅ New — executed successfully |

---

## 7. Authentication & Authorization

### Implemented

- Web session login via `AuthController.php`
- API Bearer token login via `/api/auth/login.php`
- Token refresh and logout
- Role-based access: `admin`, `staff`, `volunteer`, `donor`
- `AuthMiddleware` on protected endpoints
- Audit logging via `AuditLogger.php`
- Password hashing via `password_verify()` / `password_hash()`

### Not Implemented

- Self-service password reset (email/token flow)
- Public user self-registration portal
- Separate "donor" and "user" public-facing portals from audit prompt

### Role Matrix (Actual System)

| Role | Can Access Admin Portal | Can Use Mobile App | Can Manage Users |
|------|------------------------|-------------------|------------------|
| admin | ✅ | ✅ | ✅ |
| staff | ✅ | ✅ | Limited |
| volunteer | Limited views | ✅ | ❌ |
| donor | ❌ | ❌ | ❌ |

---

## 8. Mobile App Verification

### Screens

| Screen | Navigation | API Integration | Placeholders Removed |
|--------|-----------|-----------------|---------------------|
| `index.html` (Login) | ✅ | ✅ | Forgot password → admin contact (acceptable) |
| `dashboard.html` | ✅ | ✅ | ✅ |
| `beneficiaries.html` | ✅ | ✅ | ✅ Edit form implemented |
| `attendance.html` | ✅ | ✅ | ✅ |
| `stock.html` | ✅ | ✅ | ✅ Update stock implemented |
| `volunteers.html` | ✅ | ✅ | ✅ Detail modal implemented |
| `reports.html` | ✅ | ✅ | ✅ History/export fixed |
| `security.html` | ✅ | ✅ | Export uses alert (minor) |
| `settings.html` | ✅ | Config | ✅ |

### Capacitor / Android

- App ID: `com.tharimpepe.fsms`
- `webDir`: `mobile-shell`
- minSdk 24, targetSdk 36
- Cleartext HTTP allowed (dev/LAN deployment)
- Offline queue support in `offline-queue.js` + `sync-worker.js`

---

## 9. Data Synchronization

Both web and mobile share the **same MySQL database** via the **same REST API**. Changes made on either platform persist immediately when online.

| Entity | Web → Mobile | Mobile → Web | Mechanism |
|--------|-------------|-------------|-----------|
| Beneficiaries | ✅ | ✅ | `/api/beneficiaries/*` |
| Attendance | ✅ | ✅ | `/api/attendance/*` |
| Stock | ✅ | ✅ | `/api/stock/*` |
| Donations | ✅ | ✅ | `/api/donations/*` |
| Volunteers | ✅ | ✅ | `/api/volunteers/*` |
| Reports | ✅ | ✅ | `/api/reports/*` |
| Blog/Gallery/etc. | N/A | N/A | Not implemented |

Mobile offline queue replays POST requests when connectivity returns.

---

## 10. Security Assessment

| Control | Status |
|---------|--------|
| SQL injection (prepared statements) | ✅ Most endpoints; ⚠️ some raw `$db->query()` in reports |
| XSS (output escaping in views) | ✅ Generally applied |
| CSRF | ⚠️ Not consistently implemented on web forms |
| Password hashing | ✅ bcrypt |
| API auth | ✅ Bearer tokens with expiry |
| Audit trail | ✅ `AuditLogs` + `ActivityLog` |
| Cleartext HTTP (mobile) | ⚠️ Enabled for LAN dev — use HTTPS in production |

---

## 11. Acceptance Test Journeys

### Admin Journey (FSMS Admin Portal + Mobile)

| Step | Web | Mobile | Result |
|------|-----|--------|--------|
| Login | ✅ | ✅ | Pass |
| View dashboard | ✅ | ✅ | Pass |
| Manage beneficiaries | ✅ | ✅ | Pass |
| Record attendance | ✅ | ✅ | Pass |
| Manage stock | ✅ | ✅ | Pass |
| Record donations | ✅ | ✅ | Pass |
| Register volunteers | ✅ | ✅ | Pass |
| Generate reports | ✅ | ✅ | Pass (after fixes) |
| View audit logs | ✅ | ✅ | Pass |
| Logout | ✅ | ✅ | Pass |

### User Journey (From Audit Prompt)

| Step | Expected | Actual |
|------|----------|--------|
| Public register | Public portal | ❌ Not built |
| View outreach | Public page | ❌ Not built |
| Donate (public form) | Payment workflow | ⚠️ Admin/mobile record only |
| Volunteer signup (public) | Public form | ⚠️ Admin/mobile register only |
| Send message to NGO | Contact form | ❌ Not built |
| Use chatbot | Chatbot UI | ❌ Not built |
| View blog/gallery | Public CMS | ❌ Not built |

---

## 12. Remaining Issues (Requires Future Work)

### High Priority

1. **Implement or remove** community modules (blog, gallery, outreach, announcements, chatbot) — currently schema-only
2. **Add password reset flow** (`PasswordResets` table exists in schema, no code)
3. **Link Messages module** in web navigation + add `/api/messages/*` if mobile messaging needed
4. **Migrate `ReportSchedules` table** for report scheduling feature
5. **Normalize volunteer name fields** (see `REQUIRED_CORRECTIONS_LIST.md` CORR-01)

### Medium Priority

6. Enable HTTPS and disable cleartext for production Android build
7. Add CSRF tokens to web forms
8. Replace raw `$db->query()` in report endpoints with prepared statements
9. Wire web notifications offcanvas to `/api/notifications/list.php`
10. Standardize table name casing across all SQL queries

### Low Priority

11. Update outdated README documentation
12. Add missing database indexes (CORR-03 through CORR-09)
13. Expand automated test coverage beyond auth/validation

---

## 13. Verification Checklist

| Criterion | Status |
|-----------|--------|
| Core documented FSMS features work | ✅ |
| Every mobile screen loads without 404 API calls | ✅ (after fixes) |
| No mobile "coming soon" placeholders on core actions | ✅ |
| PHP test suite passes | ✅ 16/16 |
| All API files pass syntax check | ✅ |
| Database connected | ✅ |
| Web + mobile share backend | ✅ |
| Data syncs via shared DB/API | ✅ |
| Blog/gallery/outreach/chatbot from audit prompt | ❌ Not in codebase |
| React frontend from audit prompt | ❌ Not in codebase |
| Payment gateway integration | ❌ Not implemented |

---

## 14. Recommendations

1. **Align requirements documentation** with the actual PHP MVC + Capacitor architecture to avoid false audit expectations.
2. **Run migrations on all environments:** `php migrations/20260621_ensure_messages_table.php` and remaining schema migrations.
3. **Deploy with HTTPS** and update `capacitor.config.json` / `network_security_config.xml` for production.
4. **Prioritize community portal** as a separate phase if public-facing NGO website features are still required.
5. **Expand test suite** to cover API integration tests for each module.

---

## 15. Conclusion

The **Tharimpepe FSMS core system is production-ready for internal feeding-scheme operations** (beneficiary management, attendance, stock, donations, volunteers, reporting) on both web and Android mobile.

The audit prompt described a **broader public NGO website** (React, blog, gallery, chatbot, outreach, payment gateway) that **does not exist in this repository**. Those features would require a separate implementation phase.

**Critical runtime defects found during this audit have been repaired.** Remaining gaps are documented above with priority levels.

---

*Generated by system functionality audit — 2026-06-21*
