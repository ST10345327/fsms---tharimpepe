# RBAC Verification Report

**Project:** Tharimpepe FSMS  
**Date:** 23 June 2026  
**Scope:** Phase 1 role-based access control implementation  
**Roles:** `admin`, `staff`, `volunteer`, `donor`

Central permission definitions live in `app/helpers/Rbac.php`.

---

## 1. Role Capability Summary

| Capability | Admin | Staff | Volunteer | Donor |
|------------|:-----:|:-----:|:---------:|:-----:|
| Operational dashboard | ✓ | ✓ | ✓ | |
| Donor dashboard | | | | ✓ |
| Profile & change password | ✓ | ✓ | ✓ | ✓ |
| User administration | ✓ | | | |
| Beneficiaries | ✓ | ✓ | | |
| Attendance | ✓ | ✓ | ✓ | |
| Food stock | ✓ | ✓ | | |
| Volunteer schedules | ✓ | ✓ | ✓ | |
| Donation management (all) | ✓ | ✓ | | |
| Own donation history | | | | ✓ |
| Reports | ✓ | ✓ | | |
| Internal messages | ✓ | ✓ | ✓ | |
| Audit / activity logs (admin UI) | ✓ | | | |
| Delete donations / stock (web) | ✓ | | | |

---

## 2. Web Controllers

| Route | Permission | Admin | Staff | Volunteer | Donor |
|-------|------------|:-----:|:-----:|:---------:|:-----:|
| `AuthController.php` (login/logout/register) | Public / session | ✓ | ✓ | ✓ | ✓ |
| `DashboardController.php` | `dashboard.operational` | ✓ | ✓ | ✓ | |
| `DonorController.php?action=dashboard` | `dashboard.donor` | | | | ✓ |
| `DonorController.php?action=history` | `donations.own` | | | | ✓ |
| `ProfileController.php?action=profile` | `profile` | ✓ | ✓ | ✓ | ✓ |
| `ProfileController.php?action=change_password` | `change_password` | ✓ | ✓ | ✓ | ✓ |
| `UserController.php` (all admin actions) | `users` | ✓ | | | |
| `UserController.php?action=activity_log` | `audit` | ✓ | | | |
| `BeneficiaryController.php` | `beneficiaries` | ✓ | ✓ | | |
| `AttendanceController.php` | `attendance` | ✓ | ✓ | ✓ | |
| `FoodStockController.php` | `food_stock` | ✓ | ✓ | | |
| `FoodStockController.php?action=delete` | `food_stock.delete` (admin only in handler) | ✓ | | | |
| `VolunteerController.php` | `volunteers` | ✓ | ✓ | | |
| `VolunteerScheduleController.php` | `schedules` | ✓ | ✓ | ✓ | |
| `DonationController.php` | `donations.manage` | ✓ | ✓ | | |
| `DonationController.php?action=delete` | `donations.delete` (admin only in handler) | ✓ | | | |
| `ReportsController.php` | `reports` | ✓ | ✓ | | |
| `MessageController.php` | `messages` | ✓ | ✓ | ✓ | |

**Post-login redirect**

| Role | Destination |
|------|-------------|
| admin, staff, volunteer | `/views/dashboard.php` |
| donor | `/controllers/DonorController.php?action=dashboard` |

---

## 3. Web Views (direct access)

| View | Permission | Admin | Staff | Volunteer | Donor |
|------|------------|:-----:|:-----:|:---------:|:-----:|
| `views/dashboard.php` | `dashboard.operational` | ✓ | ✓ | ✓ | redirects |
| `views/dashboard/dashboard-mobile.php` | `dashboard.operational` | ✓ | ✓ | ✓ | redirects |
| `views/dashboard/donor.php` | via DonorController | | | | ✓ |
| `views/beneficiaries/list-mobile.php` | `beneficiaries` | ✓ | ✓ | | |
| `views/attendance/list-mobile.php` | `attendance` | ✓ | ✓ | ✓ | |
| `views/food_stock/list-mobile.php` | `food_stock` | ✓ | ✓ | | |
| `views/donations/list-mobile.php` | `donations.manage` | ✓ | ✓ | | |
| `views/donations/donor_history.php` | via DonorController | | | | ✓ |
| `views/schedules/list-mobile.php` | `schedules` | ✓ | ✓ | ✓ | |
| `views/reports/list-mobile.php` | `reports` | ✓ | ✓ | | |
| `views/login.php` | Public | ✓ | ✓ | ✓ | ✓ |
| `views/register.php` | Public (creates volunteer) | ✓ | ✓ | ✓ | ✓ |

---

## 4. Navigation (role-filtered)

Sidebar (`layout-header.php`), mobile drawer, and mobile bottom nav are built from `rbacNavItems()` / mobile link definitions in `Rbac.php` and `mobile-drawer.php`.

| Nav item | Admin | Staff | Volunteer | Donor |
|----------|:-----:|:-----:|:---------:|:-----:|
| Dashboard (operational) | ✓ | ✓ | ✓ | |
| My Dashboard | | | | ✓ |
| My Donations | | | | ✓ |
| Beneficiaries | ✓ | ✓ | | |
| Attendance | ✓ | ✓ | ✓ | |
| Food Stock | ✓ | ✓ | | |
| Volunteers / Schedules | ✓ | ✓ | ✓ | |
| Donations (manage) | ✓ | ✓ | | |
| Reports | ✓ | ✓ | | |
| Users | ✓ | | | |
| My Profile | ✓ | ✓ | ✓ | ✓ |
| Logout | ✓ | ✓ | ✓ | ✓ |

---

## 5. REST API Endpoints

Legend: ✓ = allowed, — = denied (403)

### Authentication (public / token)

| Endpoint | Auth | Notes |
|----------|------|-------|
| `POST /api/auth/login.php` | Public | Returns role in user payload |
| `POST /api/auth/logout.php` | Bearer | All roles |
| `POST /api/auth/refresh.php` | Bearer | All roles |
| `GET /api/auth/validate.php` | Bearer | All roles |
| `GET /api/system/health.php` | Public | Health check |

### Beneficiaries — `api.beneficiaries` (admin, staff)

| Endpoint | Admin | Staff | Volunteer | Donor |
|----------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/beneficiaries/list.php` | ✓ | ✓ | — | — |
| `GET /api/beneficiaries/get.php` | ✓ | ✓ | — | — |
| `GET /api/beneficiaries/detail.php` | ✓ | ✓ | — | — |
| `GET /api/beneficiaries/filter.php` | ✓ | ✓ | — | — |
| `GET /api/beneficiaries/stats.php` | ✓ | ✓ | — | — |
| `POST /api/beneficiaries/create.php` | ✓ | ✓ | — | — |
| `PUT/POST /api/beneficiaries/update.php` | ✓ | ✓ | — | — |
| `DELETE /api/beneficiaries/delete.php` | ✓ | ✓ | — | — |

### Attendance

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `POST /api/attendance/save.php` | write | ✓ | ✓ | ✓ | — |
| `POST /api/attendance/bulk-mark.php` | write | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/today.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/recent.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/history.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/sessions.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/stats.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/analytics.php` | read | ✓ | ✓ | ✓ | — |
| `GET /api/attendance/export.php` | export | ✓ | ✓ | — | — |

### Food stock — `api.stock` (admin, staff)

| Endpoint | Admin | Staff | Volunteer | Donor |
|----------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/stock/list.php` | ✓ | ✓ | — | — |
| `GET /api/stock/stats.php` | ✓ | ✓ | — | — |
| `GET /api/stock/alerts.php` | ✓ | ✓ | — | — |
| `GET /api/stock/history.php` | ✓ | ✓ | — | — |
| `GET /api/stock/low-stock.php` | ✓ | ✓ | — | — |
| `GET /api/stock/movements.php` | ✓ | ✓ | — | — |
| `POST /api/stock/add.php` | ✓ | ✓ | — | — |
| `POST /api/stock/update.php` | ✓ | ✓ | — | — |
| `POST /api/stock/distribute.php` | ✓ | ✓ | — | — |

### Volunteers

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/volunteers/list.php` | manage | ✓ | ✓ | — | — |
| `GET /api/volunteers/stats.php` | manage | ✓ | ✓ | — | — |
| `POST /api/volunteers/register.php` | manage | ✓ | ✓ | — | — |
| `POST /api/volunteers/assign-shift.php` | manage | ✓ | ✓ | — | — |
| `GET /api/volunteers/schedule.php` | self | ✓ | ✓ | ✓ | — |
| `GET/POST /api/volunteers/status.php` | self | ✓ | ✓ | ✓ | — |
| `GET /api/volunteers/attendance.php` | self | ✓ | ✓ | ✓ | — |

### Donations

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/donations/list.php` | manage | ✓ | ✓ | — | — |
| `POST /api/donations/cash.php` | manage | ✓ | ✓ | — | — |
| `POST /api/donations/record.php` | manage | ✓ | ✓ | — | — |
| `GET /api/donations/history.php` | own | ✓ | ✓* | — | ✓** |
| `GET /api/donations/receipt.php` | own | ✓ | ✓* | — | ✓** |

\* Staff/admin may pass `donor_id` filter.  
\** Donor requests are auto-scoped to `UserID = current user`.

### Reports — `api.reports` (admin, staff)

| Endpoint | Admin | Staff | Volunteer | Donor |
|----------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/reports/generate.php` | ✓ | ✓ | — | — |
| `GET /api/reports/export.php` | ✓ | ✓ | — | — |
| `GET /api/reports/download.php` | ✓ | ✓ | — | — |
| `GET /api/reports/history.php` | ✓ | ✓ | — | — |
| `GET/POST /api/reports/schedule.php` | ✓ | ✓ | — | — |
| `GET /api/reports/summary.php` | ✓ | ✓ | — | — |

### Dashboard APIs

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/dashboard/summary.php` | ops | ✓ | ✓ | ✓ | — |
| `GET /api/dashboard/activity.php` | ops | ✓ | ✓ | ✓ | — |
| `GET /api/dashboard/alerts.php` | ops | ✓ | ✓ | ✓ | — |
| `GET /api/dashboard/inventory-summary.php` | ops | ✓ | ✓ | ✓ | — |
| `GET /api/dashboard/donations-summary.php` | donor | — | — | — | ✓ |

### Users

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/users/list.php` | admin | ✓ | — | — | — |
| `POST /api/users/create.php` | admin | ✓ | — | — | — |
| `POST /api/users/update.php` | admin | ✓ | — | — | — |
| `POST /api/users/change-password.php` | self | ✓ | ✓ | ✓ | ✓ |

### Audit & activity

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/audit/logs.php` | audit | ✓ | ✓ | — | — |
| `GET /api/audit/stats.php` | audit | ✓ | ✓ | — | — |
| `GET /api/activity/list.php` | activity | ✓ | ✓ | — | — |

### Meal sessions

| Endpoint | Permission | Admin | Staff | Volunteer | Donor |
|----------|------------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/meal-sessions/list.php` | read | ✓ | ✓ | ✓ | — |
| `POST /api/meal-sessions/create.php` | write | ✓ | ✓ | — | — |
| `POST /api/meal-sessions/close.php` | write | ✓ | ✓ | — | — |

### Notifications — `api.notifications` (admin, staff, volunteer)

| Endpoint | Admin | Staff | Volunteer | Donor |
|----------|:-----:|:-----:|:---------:|:-----:|
| `GET /api/notifications/list.php` | ✓ | ✓ | ✓ | — |
| `DELETE /api/notifications/delete.php` | ✓ | ✓ | ✓ | — |

---

## 6. New / Changed Files (Phase 1)

| File | Purpose |
|------|---------|
| `app/helpers/Rbac.php` | Central permissions, nav filtering, web/API guards |
| `app/controllers/ProfileController.php` | Profile & password change for all roles |
| `app/controllers/DonorController.php` | Donor dashboard and history |
| `app/views/dashboard/donor.php` | Donor dashboard UI |
| `app/views/donations/donor_history.php` | Donor donation history UI |
| `app/models/Donation.php` | Added `getDonationsByUserId()`, `getDonorSummaryByUserId()` |

---

## 7. Manual Verification Steps

1. Log in as `admin` / `coordinator` (staff) / `volunteer1` / `donor1` (seed password: `password`).
2. Confirm sidebar shows only role-appropriate links.
3. Attempt direct URL access to blocked modules (e.g. volunteer → `UserController.php`) — expect redirect with access denied.
4. Donor login → lands on donor dashboard with donation summary.
5. Donor → `ProfileController.php?action=profile` and change password flow works.
6. API: call `/api/reports/generate.php` with volunteer token → 403.
7. API: call `/api/donations/history.php` with donor token → only own `UserID` rows.

---

## 8. Known Follow-ups (Phase 2+)

- Reports dashboard static mock still needs live data wiring.
- Volunteer schedule self-scoping (own shifts only) not yet enforced in web controller.
- PDF/CSV export buttons on web report pages still print-only.
- Mobile Capacitor shell donor HTML pages (`donor-dashboard.html`) not yet added — nav references prepared in `shared.js`.

---

**Automated tests:** 16/16 passing (`tests/run_all_tests.php`) after Phase 1 changes.
