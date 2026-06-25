# Staff Role — Permissions Matrix

## Legend
| Icon | Meaning |
|------|---------|
| ✅ | Allowed (controller enforces) |
| ❌ | Denied (controller blocks) |
| 👁️ | Visible but read-only |
| 🔒 | Hidden from UI (defense-in-depth) |

---

## 1. Controller-Level Access

| Controller | Permission Key | Admin | Staff | Volunteer | Donor |
|---|---|---|---|---|---|
| DashboardController | `dashboard.operational` | ✅ | ✅ | ✅ | ❌ |
| DonorController | `donations.own` | ❌ | ❌ | ❌ | ✅ |
| BeneficiaryController | `beneficiaries` | ✅ | ✅ | ❌ | ❌ |
| AttendanceController | `attendance` | ✅ | ✅ | ✅ | ❌ |
| FoodStockController | `food_stock` | ✅ | ✅ | ❌ | ❌ |
| DonationController | `donations.manage` | ✅ | ✅ | ❌ | ❌ |
| VolunteerController | `volunteers` | ✅ | ✅ | ❌ | ❌ |
| VolunteerScheduleController | `schedules` | ✅ | ✅ | 👁️ | ❌ |
| ReportsController | `reports` | ✅ | ✅ | ❌ | ❌ |
| MessageController | `messages` | ✅ | ✅ | ✅ | ❌ |
| ProfileController | `profile` | ✅ | ✅ | ✅ | ✅ |
| UserController | `users` | ✅ | ❌ | ❌ | ❌ |

---

## 2. Fine-Grained Action Restrictions

| Module | Action | Admin | Staff | Volunteer | Enforced At |
|---|---|---|---|---|---|
| **Attendance** | View list | ✅ | ✅ | ✅ | Controller |
| | Create record | ✅ | ✅ | ✅ | Controller |
| | Edit record | ✅ | ✅ | ❌ | Controller + View |
| | Delete record | ✅ | ✅ | ❌ | Controller + View |
| | Daily summary | ✅ | ✅ | 👁️ | Controller |
| | Export | ✅ | ✅ | ❌ | Controller |
| **Beneficiaries** | View list | ✅ | ✅ | ❌ | Controller |
| | Create | ✅ | ✅ | ❌ | Controller + View |
| | Edit | ✅ | ✅ | ❌ | Controller + View |
| | Delete | ✅ | 🔒 | ❌ | View (rbacCan) |
| | Search/Filter | ✅ | ✅ | ❌ | Controller |
| **Donations** | View list | ✅ | ✅ | ❌ | Controller |
| | Create | ✅ | ✅ | ❌ | Controller |
| | Edit | ✅ | ✅ | ❌ | Controller |
| | Delete | ✅ | ❌ | ❌ | Controller + View |
| | Report | ✅ | ✅ | ❌ | Controller |
| **Food Stock** | View list | ✅ | ✅ | ❌ | Controller |
| | Add item | ✅ | ✅ | ❌ | Controller + View |
| | Edit item | ✅ | ✅ | ❌ | Controller |
| | Distribute | ✅ | ✅ | ❌ | Controller |
| | Delete item | ✅ | ❌ | ❌ | Controller + View |
| **Volunteers** | View list | ✅ | ✅ | ❌ | Controller |
| | Edit | ✅ | ✅ | ❌ | Controller + View |
| | Deactivate | ✅ | ✅ | ❌ | Controller + View |
| | Export | ✅ | ✅ | ❌ | Controller |
| **Schedules** | View schedule | ✅ | ✅ | ✅ | Controller |
| | Create schedule | ✅ | ✅ | ❌ | Controller |
| | Edit schedule | ✅ | ✅ | ❌ | Controller |
| | Delete schedule | ✅ | ✅ | ❌ | Controller |
| | Manage own availability | ✅ | ✅ | ✅ | Controller (ownership check) |
| **Reports** | View dashboard | ✅ | ✅ | ❌ | Controller |
| | Generate report | ✅ | ✅ | ❌ | Controller |
| | Export CSV/XLS | ✅ | ✅ | ❌ | Controller |
| **Messages** | Inbox/Sent | ✅ | ✅ | ✅ | Controller |
| | Compose/Send | ✅ | ✅ | ✅ | Controller |
| | Delete message | ✅ | ✅ | ✅ | Controller (model ownership) |
| **Users** | All (list/create/edit/delete/roles) | ✅ | ❌ | ❌ | Controller |

---

## 3. Navigation Items Visibility

| Menu Item | Permission | Admin | Staff | Volunteer | Donor |
|---|---|---|---|---|---|
| Dashboard | `dashboard.operational` | ✅ | ✅ | ✅ | ❌ |
| My Dashboard | `dashboard.donor` | ❌ | ❌ | ❌ | ✅ |
| My Donations | `donations.own` | ❌ | ❌ | ❌ | ✅ |
| Beneficiaries | `beneficiaries` | ✅ | ✅ | ❌ | ❌ |
| Attendance | `attendance` | ✅ | ✅ | ✅ | ❌ |
| Food Stock | `food_stock` | ✅ | ✅ | ❌ | ❌ |
| Schedules | `schedules` | ✅ | ✅ | 👁️ | ❌ |
| Donations | `donations.manage` | ✅ | ✅ | ❌ | ❌ |
| Reports | `reports` | ✅ | ✅ | ❌ | ❌ |
| Users | `users` | ✅ | ❌ | ❌ | ❌ |
| My Profile | `profile` | ✅ | ✅ | ✅ | ✅ |

---

## 4. Quick Actions (Dashboard Page)

| Quick Action | Admin | Staff | Volunteer |
|---|---|---|---|
| Add Beneficiary | ✅ | ✅ | ❌ |
| Record Attendance | ✅ | ✅ | ✅ |
| Add Stock | ✅ | ✅ | ❌ |
| Generate Report | ✅ | ✅ | ❌ |

---

## 5. Notification Bell Visibility

| Feature | Admin | Staff | Volunteer | Donor |
|---|---|---|---|---|
| Notification bell (messages) | ✅ | ✅ | ✅ | ❌ |

---

## Summary: What Staff CAN vs CANNOT Do

### Staff CAN:
- View operational dashboard
- Manage beneficiaries (CRUD, search, filter) — no delete
- Record and manage attendance
- Manage food inventory (add, edit, distribute) — no delete
- Manage donations (create, edit, view) — no delete
- View and manage volunteer profiles (edit, deactivate)
- View and create schedules
- Generate and export all reports (CSV + XLS)
- Send and receive messages
- View and update own profile + change password

### Staff CANNOT:
- Create, edit, delete, or deactivate user accounts
- Assign or modify roles/permissions
- Delete donations (admin-only)
- Delete food stock items (admin-only)
- Access the Users module or role management
- Access donor-specific dashboards or donation history
- See restricted navigation items (Users, My Dashboard, My Donations)

---

## File-by-File Enforcement Summary

### Controller-Level
| File | Change Made |
|---|---|
| `app/controllers/ProfileController.php` | Added `rbacRequirePermission('profile')` (was missing) |

### View-Level (defense-in-depth button hiding)
| File | Change Made |
|---|---|
| `app/views/donations/view.php` | Delete button wrapped in `rbacCan('donations.delete')` |
| `app/views/donations/delete.php` | Delete form wrapped in `rbacCan('donations.delete')` |
| `app/views/food_stock/list.php` | Delete link + Add Stock button wrapped in `rbacCan(...)` |
| `app/views/food_stock/view.php` | Delete button wrapped in `rbacCan('food_stock.delete')` |
| `app/views/food_stock/delete.php` | Delete form wrapped in `rbacCan('food_stock.delete')` |
| `app/views/volunteers/list.php` | Edit + Deactivate wrapped in `rbacCan('volunteers')` |
| `app/views/volunteers/view.php` | Edit + Deactivate wrapped in `rbacCan('volunteers')` |
| `app/views/beneficiaries/list.php` | Register + Edit buttons wrapped in `rbacCan('beneficiaries')` |
| `app/views/beneficiaries/view.php` | Edit + Delete buttons wrapped in `rbacCan('beneficiaries')` |
| `app/views/beneficiaries/edit.php` | Delete button wrapped in `rbacCan('beneficiaries')` |
