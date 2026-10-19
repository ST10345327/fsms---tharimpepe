# Handoff Note — Sitemap & Wireframes

**Project:** Tharimpepe Feeding Scheme Management System (FSMS)
**Audience:** Team member working on sitemap and wireframes
**Date:** 2026-09-04

---

## What the project does

A management system for the **Tharimpepe Feeding Scheme**, a community feeding program. It administers the day-to-day operations of feeding beneficiaries:

- **Register and manage beneficiaries** — the people who receive meals. Includes status tracking (active / inactive / suspended) and filters (search, date range, age range).
- **Track daily attendance** — which beneficiaries attended each feeding session, plus per-day and per-beneficiary summaries.
- **Manage food stock** — inventory of food items (name, category, quantity, unit, cost) and stock-level status (enough / low / out).
- **Manage donations** — record incoming donations and tie them to stock/purchases.
- **Manage volunteers & schedules** — volunteer profiles and their shift schedules.
- **Reports & dashboard** — KPI summaries, feeding stats, attendance trends, food-stock status, donation stats, top donors, volunteer performance, beneficiary trends.
- **Users & roles** — secure authentication with role-based access control (RBAC).
- **Messages / notifications** — internal inbox and notification bell.
- **Audit logging** — activity log of key actions.

It ships as **two front-ends sharing one back-end**: a **PHP website** (XAMPP served) and a **Capacitor Android app**, both reading the same **MySQL** database. The PHP MVC app (`app/`) and a **REST API** (`api/`) feed both UIs.

---

## Core domain objects (business entities)

| Entity | What it is |
|--------|------------|
| Beneficiary | A feeding-scheme recipient (personal info, status, registration date). |
| Attendance | A record that a beneficiary attended a session on a given date. |
| MealSession | An organized serving time (e.g., a daily lunch service). |
| FoodStockItem | A food product in inventory (name, category, quantity, unit, cost). |
| Donation | An incoming donation (donor, amount/source, date), possibly linked to stock. |
| Volunteer | A volunteer profile (linked to a User), with first/last name, phone, address, availability. |
| VolunteerSchedule | A shift/assignment for a volunteer. |
| User | A login account with a role and an approval status. |
| Message / ActivityLog | Internal comms and audit trail. |

---

## Roles & what each can do (use cases)

Access model lives in `app/helpers/Rbac.php`. There are **four roles**:

| Role | What they can access / do |
|------|---------------------------|
| **Admin** | Everything: user management (create, edit, approve/reject, change roles, soft-delete), all operational modules, audit logs, and destructive actions (delete donations / food stock / beneficiaries). |
| **Staff** | Day-to-day operations: beneficiaries, attendance, food stock, volunteers, donations, reports, schedules. Cannot manage users or do admin-only deletes. |
| **Volunteer** | Operational access to **attendance**, **schedules**, **donations**, **messages**. Does NOT see Beneficiaries, Food Stock, Volunteers, Reports, or Users. |
| **Donor** | Self-service only: own donor dashboard, own donation history, own profile. No operational modules. |

### Permission matrix (which roles see what)

| Module | Admin | Staff | Volunteer | Donor |
|--------|:-----:|:-----:|:---------:|:-----:|
| Dashboard (operational) | ✓ | ✓ | ✓ | – |
| Donor Dashboard / My Donations | – | – | – | ✓ |
| Beneficiaries | ✓ | ✓ | – | – |
| Attendance | ✓ | ✓ | ✓ | – |
| Food Stock | ✓ | ✓ | – | – |
| Volunteers / Schedules | ✓ | ✓ | ✓ (schedules) | – |
| Donations (manage) | ✓ | ✓ | ✓ | – |
| Reports | ✓ | ✓ | – | – |
| Users | ✓ | – | – | – |
| My Profile | ✓ | ✓ | ✓ | ✓ |
| Messages | ✓ | ✓ | ✓ | – |

**Note for wireframes:** Because of RBAC, the same screens render *conditionally* per role. The sidebar is role-filtered at render time (`rbacNavItemsForRole()`), so one visitor's nav differs from another's. The sitemap should capture which role sees which screens.

---

## Approval workflow (business rule — recently reinforced)

- Anyone registering (web or mobile/API) creates an account with status **`pending`**.
- A pending account **cannot log in or use the system** — login is blocked with a "pending approval" message.
- An **admin approves** the account → status becomes `active`. For volunteers, the volunteer profile also becomes `approved`/`available`. An admin can also **reject** → `inactive`.
- Consequence: volunteers and donors must be **accepted by an administrator before they can do anything**.

---

## Key business flows (input for sitemap/wireframes)

1. **Authentication & onboarding**
   - Login → role-based redirect (operational users → main dashboard; donor → donor dashboard).
   - Register (public) → pending → admin approval → login.
2. **Beneficiary lifecycle**
   - List (paginated, status filter) → Create → View profile → Edit → Update status → Delete (admin) → Search / date-range / age-range filters.
3. **Attendance**
   - Record daily attendance for a session → day summary → per-beneficiary history → export.
4. **Food stock & donations**
   - Inventory list → add/restock items → stock-level status → record donations → link donations to stock.
5. **Volunteers & scheduling**
   - Volunteer list/profile → availability status (available / unavailable / on leave) → schedules → CSV export.
6. **Reports & dashboard**
   - KPIs, feeding stats, attendance trends, stock status, donation stats, top donors, volunteer performance, beneficiary trend.
7. **Administration**
   - User management (approve/reject pending, create, edit, roles) → audit/activity log → messages.

---

## Suggested sitemap grouping (base for wireframes)

The sidebar navigation already reflects this hierarchy:

- **Dashboard** (operational) & **Donor Dashboard** (self-service)
- **Beneficiaries** — List / Create / View / Edit
- **Attendance** — Record / Daily Summary / Lists
- **Food Stock** — Inventory / Restock
- **Donations** — Records / (donor history)
- **Volunteers** — List / Profile / View / Edit (+ Schedules)
- **Reports** — Dashboard / report views
- **Users** (admin) — List / Approve / Edit / Roles / Activity Log
- **My Profile** — settings / change password
- **Messages** — inbox / notifications
- **Auth** — Login / Register / Logout

---

## Wireframe considerations

- **Responsive / two UIs:** The web app is a desktop-first sidebar layout that collapses to an off-canvas/mobile menu; the Android app uses a bottom tab bar (Dashboard, Beneficiaries, Attendance, Stock, More) plus a slide-in drawer. Feedback on wireframes should cover both breakpoints.
- **Role-conditional nav:** Highlight which nav items appear for each role so screens are never shown without access.
- **Status-driven UI:** Beneficiary status (active/inactive/suspended), volunteer availability, and user approval status drive which actions are available on each screen.
