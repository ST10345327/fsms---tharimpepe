# FSMS - Final Testing Preparation
**System:** Feeding Scheme Management System (Tharimpepe)
**Version:** 1.0
**Date:** 2024-06-18
**Status:** Pre-UAT - Ready for Final Testing

---

## Table of Contents
1. [Test Data Plan](#test-data-plan)
2. [Testing Checklist](#testing-checklist)
3. [UAT Checklist](#uat-checklist)
4. [Validation Results](#validation-results)

---

## 1. TEST DATA PLAN

### 1.1 Overview
This plan defines realistic test datasets that mirror real-world operating conditions in Soweto, South Africa. All data has been seeded to verify system behavior under production-like loads.

### 1.2 Test Data Files

| File | Usage | Instructions |
|------|-------|--------------|
| `sql/schema.sql` | Database structure | Execute first to create tables |
| `sql/test_data_seed.sql` | Sample data | Execute after schema creation |
| `tests/run_all_tests.php` | Automated tests | Run via CLI for regression |

### 1.3 Credentials (All passwords: `admin123`)

| Username | Password | Role | Full Name | Phone |
|----------|----------|------|-----------|-------|
| `admin` | `admin123` | admin | Thandiwe Mokoena | 082-555-0101 |
| `coordinator` | `admin123` | staff | Sipho Nkosi | 083-555-0102 |
| `volunteer1` | `admin123` | volunteer | Nompumelelo Dlamini | 084-555-0103 |
| `volunteer2` | `admin123` | volunteer | Thabo Molefe | 082-555-0104 |
| `volunteer3` | `admin123` | volunteer | Lerato Khumalo | 079-555-0105 |
| `donor1` | `admin123` | donor | Johann van der Merwe | 082-333-0101 |
| `donor2` | `admin123` | donor | Fatima Patel | 083-444-0102 |
| `donor3` | `admin123` | donor | Community Church Group | 071-222-0103 |

### 1.4 Entity Volumes

| Entity | Records | Description |
|--------|---------|-------------|
| **Users** | 8 | Admin, staff, volunteers, donors |
| **Beneficiaries** | 50 | Mix of children, adults, elderly |
| **Volunteers** | 3 | Linked to 3 volunteer user accounts |
| **Meal Sessions** | 15 | Breakfast, lunch, dinner over 30 days |
| **Attendance** | 22+ | Linked to first 2 meal sessions |
| **Donations** | 9 | Cash (EFT, card, cash), food, supplies |
| **Food Stock** | 12 | Staples, canned goods, some low-stock items |
| **Volunteer Schedules** | 6 | Upcoming assignments |
| **Distributions** | 9 | Stock movement tracking |
| **Payment Transactions** | 5 | Linked to completed/pending donations |

### 1.5 Data Characteristics

#### Users
- 3 role types: admin, staff, volunteer, donor
- Mix of active and inactive accounts
- Valid email formats and phone numbers

#### Beneficiaries
- Age distribution: 5-82 years
- Gender: Mixed (Male, Female)
- Categories: Children (<18), Adults (18-60), Elderly (>60)
- Status: 49 active, 1 inactive (moved away)
- Registration dates: Jan-Mar 2024

#### Volunteers
- Skills: Food prep, logistics, childcare, admin
- Availability: 2 available, 1 on_leave
- Status: All approved except none pending
- Schedules: 3 days of upcoming assignments

#### Attendance
- Present: ~80% rate (realistic)
- Absent with reasons: sick, no transport, job interview, etc.
- Coverage: First 2 sessions (~22 records for expansion)

#### Donations
- Types: cash, food, supplies
- Methods: EFT, card, cash, in-kind
- Statuses: completed, pending
- Amounts: ZAR 300 - 5000
- Includes anonymous donation

#### Food Stock
- Items: Maize meal, rice, oil, canned goods, bread, milk powder, sugar, tea, coffee, porridge
- Quantities: 0 (bread - donated daily) to 150 kg
- Expiry dates: Spanning 2024-2026
- Low stock indicators: Cooking Oil (25L), Milk Powder (10kg - critical)
- Zero stock: Bread (zero-tolerance, daily donations)

#### Meal Sessions
- Session types: Breakfast, Lunch, Dinner
- Frequency: 1-2 per day (mimicking service schedule)
- Locations: Main Hall
- Dates: Past 30 days + upcoming

---

## 2. TESTING CHECKLIST

### 2.1 CRUD Operations

#### Users
- [ ] **CREATE** - Register new user via admin interface
- [ ] **READ** - List users, view individual user
- [ ] **UPDATE** - Edit user details, change role
- [ ] **DELETE** - Soft delete (set inactive)
- [ ] Verify password hashing

#### Beneficiaries
- [ ] **CREATE** - Register new beneficiary
- [ ] **READ** - List with pagination, search by name
- [ ] **UPDATE** - Edit details, change status
- [ ] **DELETE** - Soft delete (set inactive)
- [ ] Verify Category auto-calculation (Child/Adult/Elderly)

#### Volunteers
- [ ] **CREATE** - Register linked to user account
- [ ] **READ** - List with availability filter
- [ ] **UPDATE** - Edit skills, availability, status
- [ ] **DELETE** - Soft delete
- [ ] Verify User-Volunteer relationship

#### Attendance
- [ ] **CREATE** - Mark attendance for session
- [ ] **READ** - View by date, beneficiary, session
- [ ] **UPDATE** - Change status (present/absent)
- [ ] **DELETE** - Remove record
- [ ] Verify duplicate prevention (unique beneficiary+date)

#### Donations
- [ ] **CREATE** - Log cash, food, or supply donation
- [ ] **READ** - List with filters (type, date range, status)
- [ ] **UPDATE** - Edit details, update status
- [ ] **DELETE** - Admin only delete
- [ ] Verify payment transaction linkage

#### Food Stock
- [ ] **CREATE** - Add new stock item
- [ ] **READ** - List with expiry alerts
- [ ] **UPDATE** - Adjust quantity, update expiry
- [ ] **DELETE** - Admin only
- [ ] Verify quantity never negative

#### Meal Sessions
- [ ] **CREATE** - Schedule new session
- [ ] **READ** - View upcoming, history
- [ ] **UPDATE** - Edit session details
- [ ] **DELETE** - Admin only
- [ ] Verify uniqueness (date+type+location)

### 2.2 Reporting

#### Dashboard Metrics
- [ ] Total beneficiaries count accurate
- [ ] New registrations this month correct
- [ ] Meals served today accurate
- [ ] Low stock items correctly identified (<=25 units)
- [ ] Active volunteer count matches filters

#### Attendance Reports
- [ ] Daily summary
- [ ] Weekly aggregation
- [ ] Monthly trends
- [ ] Absentee reasons summary
- [ ] Export to CSV

#### Donation Reports
- [ ] Total donations by period
- [ ] Donation type breakdown (cash/food/supplies)
- [ ] Top donors list
- [ ] Payment status reconciliation
- [ ] Receipt generation

#### Stock Reports
- [ ] Current inventory levels
- [ ] Low stock alerts
- [ ] Expiry warnings (30-day outlook)
- [ ] Distribution history
- [ ] Reorder suggestions

#### Volunteer Reports
- [ ] Hours worked summary
- [ ] Attendance by volunteer
- [ ] Skills inventory
- [ ] Availability matrix

### 2.3 API & Mobile

#### Authentication
- [ ] Login returns tokens (access + refresh)
- [ ] Access token expires after 24 hours
- [ ] Refresh token expires after 30 days
- [ ] Unauthorized access returns 401
- [ ] Token refresh on 401 auto-retry
- [ ] Logout revokes tokens

#### Data Sync
- [ ] Initial sync downloads full dataset
- [ ] Incremental sync updates deltas
- [ ] Conflict resolution (server wins)
- [ ] Offline queue for failed requests
- [ ] Sync status indicator

#### Mobile Features
- [ ] Attendance marking works offline
- [ ] Dashboard loads within 3 seconds
- [ ] Search filters work locally
- [ ] Push notifications for reminders
- [ ] Camera/photo upload for gallery

### 2.4 Security

- [ ] SQL injection prevention (prepared statements)
- [ ] XSS protection (output escaping)
- [ ] CSRF tokens on forms
- [ ] Role-based access enforcement
- [ ] Password minimum 8 characters
- [ ] Failed login rate limiting
- [ ] Audit logging for all actions
- [ ] HTTPS enforced in production

### 2.5 Performance

- [ ] Dashboard loads < 3 seconds
- [ ] Beneficiary list paginates at 50
- [ ] Search response < 500ms
- [ ] Concurrent users (10+) supported
- [ ] Database indexes efficient

---

## 3. UAT CHECKLIST

### 3.1 User Acceptance Testing

#### Admin User (Thandiwe Mokoena)
- [ ] Can login successfully
- [ ] Can view dashboard with correct KPIs
- [ ] Can create/edit/delete all entities
- [ ] Can generate all reports
- [ ] Can assign roles to users
- [ ] Can view audit logs
- [ ] Can manage announcements

#### Staff User (Sipho Nkosi)
- [ ] Can login successfully
- [ ] Can register beneficiaries
- [ ] Can mark attendance
- [ ] Can log donations
- [ ] Can update food stock
- [ ] Cannot access user management
- [ ] Cannot delete records permanently

#### Volunteer (Nompumelelo Dlamini)
- [ ] Can login successfully
- [ ] Can view assigned schedule
- [ ] Can update availability
- [ ] Can mark attendance (if authorized)
- [ ] Cannot view financial reports
- [ ] Cannot manage users

#### Donor (Johann van der Merwe)
- [ ] Can login successfully
- [ ] Can view donation history
- [ ] Can make new donations
- [ ] Can view impact reports
- [ ] Cannot access operational data

### 3.2 Functional Scenarios

#### Scenario 1: Morning Breakfast Service
1. Volunteer logs in 6:00 AM
2. Views today's schedule
3. Opens attendance for 2024-05-01 Breakfast
4. Marks 50 beneficiaries present
5. Submits attendance
6. System updates dashboard "Meals Today" to 50
7. Admin reviews morning summary

**Expected Result:** Attendance saved, dashboard updates, no duplicates

#### Scenario 2: Donation Receipt
1. Donor visits center with R1000 cash
2. Staff logs donation as Cash, R1000
3. System generates receipt with reference
4. Dashboard updates donation total
5. Donor receives confirmation

**Expected Result:** Donation recorded, receipt generated, totals accurate

#### Scenario 3: Low Stock Alert
1. Milk Powder reaches 10 units (critical)
2. System flags as low stock
3. Dashboard shows "3 low stock items"
4. Admin receives alert
5. Admin creates purchase order

**Expected Result:** Alert triggered, dashboard updated, action logged

#### Scenario 4: Mobile Attendance
1. Volunteer opens mobile app
2. Logs in with credentials
3. Syncs today's beneficiary list
4. Goes offline at feeding site
5. Marks 30 attendees offline
6. Returns to connectivity
7. Auto-syncs attendance data

**Expected Result:** Offline data preserved, sync successful, no duplicates

#### Scenario 5: Report Export
1. Admin requests monthly donation report
2. Selects May 2024 date range
3. Clicks "Export CSV"
4. File downloads with all donations
5. Totals match dashboard
6. Opens in Excel correctly

**Expected Result:** CSV generated, accurate totals, proper formatting

### 3.3 Negative Testing

- [ ] Invalid login returns error (not exception)
- [ ] Expired token redirects to login
- [ ] SQL injection attempts blocked
- [ ] XSS in input fields escaped
- [ ] Unauthorized API access returns 403
- [ ] Missing required fields show validation
- [ ] Duplicate registration prevented

### 3.4 Browser/Device Compatibility

| Platform | Version | Status |
|----------|---------|--------|
| Chrome | 120+ | Required |
| Firefox | 115+ | Recommended |
| Safari | 15+ | Supported |
| Edge | 120+ | Required |
| iOS Safari | 15+ | Mobile |
| Android Chrome | 120+ | Mobile |

---

## 4. VALIDATION RESULTS

### 4.1 Pre-Test Validation

#### Database Schema
- [x] All tables created per ERD
- [x] Foreign keys defined correctly
- [x] Indexes on search columns
- [x] Constraints enforced (unique, not null)
- [x] Timestamps for audit trail

#### API Endpoints
| Endpoint | Method | Auth | Status |
|----------|--------|------|--------|
| `/api/auth/login.php` | POST | No | Ready |
| `/api/auth/validate.php` | GET | Yes | Ready |
| `/api/auth/refresh.php` | POST | Yes | Ready |
| `/api/auth/logout.php` | POST | Yes | Ready |
| `/api/beneficiaries/list.php` | GET | Yes | Ready |
| `/api/attendance/today.php` | GET | Yes | Ready |
| `/api/attendance/history.php` | GET | Yes | Ready |
| `/api/stock/list.php` | GET | Yes | Ready |
| `/api/dashboard/summary.php` | GET | Yes | Ready |

#### Mobile Shell
- [x] Capacitor config set
- [x] API client module complete
- [x] Token management implemented
- [x] Offline support via localStorage
- [x] Error handling in place

### 4.2 Automated Test Results

```
Running test suite...
TestAuthenticationAndValidation: PASS
- testValidLogin: PASS
- testInvalidPassword: PASS
- testMissingCredentials: PASS
- testTokenRefresh: PASS
- testTokenExpiry: PASS

Results: 5/5 passed (100%)
Exit code: 0
```

### 4.3 Manual Test Sign-Off

| Test Category | Tester | Date | Result | Notes |
|---------------|--------|------|--------|-------|
| Authentication | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Users | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Beneficiaries | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Volunteers | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Attendance | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Donations | [ ] | ___/___/___ | Pass / Fail | |
| CRUD - Food Stock | [ ] | ___/___/___ | Pass / Fail | |
| Dashboard Metrics | [ ] | ___/___/___ | Pass / Fail | |
| Reporting | [ ] | ___/___/___ | Pass / Fail | |
| Mobile Sync | [ ] | ___/___/___ | Pass / Fail | |
| Security | [ ] | ___/___/___ | Pass / Fail | |
| Performance | [ ] | ___/___/___ | Pass / Fail | |

### 4.4 Known Limitations

1. **Bread Donation:** Food stock shows 0 for bread (designed behavior - daily bakery donations)
2. **Demo Mode:** Some endpoints fall back to `.demo_users.json` if DB unavailable
3. **Timezone:** All timestamps stored in SAST (UTC+2)
4. **Offline Sync:** Max 100 queued items before forced sync

### 4.5 Deployment Readiness

- [ ] All critical tests pass
- [ ] Load test completed (50 concurrent users)
- [ ] Security scan completed
- [ ] Backup/restore procedure verified
- [ ] Monitoring/alerting configured
- [ ] Documentation finalized
- [ ] Training materials prepared
- [ ] Sign-off obtained

---

## Appendices

### A. Test Data Loading Instructions

```bash
# 1. Create database and schema
mysql -u root -p < sql/schema.sql

# 2. Load realistic test data
mysql -u root -p fsms < sql/test_data_seed.sql

# 3. Verify data loaded
mysql -u root -p -e "USE fsms; SELECT COUNT(*) FROM Beneficiaries;"
```

### B. API Testing with cURL

```bash
# Login
curl -X POST http://localhost/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Get beneficiaries (replace TOKEN)
curl http://localhost/api/beneficiaries/list.php \
  -H "Authorization: Bearer TOKEN"
```

### C. Automated Test Execution

```bash
# Run full test suite
php tests/run_all_tests.php

# Run specific test file directly
php tests/TestAuthenticationAndValidation.php
```

---

**Document Owner:** System Development Team
**Last Updated:** 2025-06-18
**Review Cycle:** Per sprint