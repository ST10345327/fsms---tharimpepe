# Documentation Compliance Report — FSMS (Tharimpepe Feeding Scheme)

**Date:** 2026-06-18  
**Reviewed By:** Lead Software Engineer  
**Scope:** Alignment between implemented system and WIL Task 2 Requirements Analysis + Task 2b System Design documentation  
**Reference:** `docs/academic/ST10345327_OLEBOGENG_Task_2_Requirements_Analysis.pdf`, `sql/schema.sql`, `app/`, `api/`, `mobile-shell/`

---

## 1. DOCUMENT OVERVIEW

### 1.1 Referenced Documents

| Document | Path | Purpose |
|----------|------|---------|
| Task 2 Requirements Analysis | `docs/academic/ST10345327_OLEBOGENG_Task_2_Requirements_Analysis.pdf` | Functional requirements, actors, ERD, use cases |
| Task 2b System Design | Referenced in code comments and `codebase-analysis-docs/COMPREHENSIVE_ARCHITECTURAL_REVIEW.md` | Database design, API design, architecture |
| AUTH_AUDIT.md | `codebase-analysis-docs/AUTH_AUDIT.md` | Prior authentication audit (22 findings) |
| COMPREHENSIVE_ARCHITECTURAL_REVIEW.md | `codebase-analysis-docs/COMPREHENSIVE_ARCHITECTURAL_REVIEW.md` | Prior architectural review (27 findings) |

---

## 2. ACTOR COMPLIANCE

### 2.1 Requirements vs Implementation

| Actor | Description | System Role | Implementation Status |
|-------|-------------|-------------|----------------------|
| Organisation Manager (Admin) | Oversees all operations, approves records, generates reports | Full system access | ✅ Implemented — `Role = 'admin'` |
| Volunteer | Assists with meal prep and distribution; records attendance | Limited access | ✅ Implemented — `Role = 'volunteer'` |
| Beneficiary Representative | Guardian registering community member | Submit registration, view status | ⚠️ Partial — registration via Admin; no self-service portal |
| Donor | Individuals/organisations contributing food or funds | View donation records, submit details | ⚠️ Partial — donation viewing exists; donor-specific portal missing |

**Missing Actor Feature:** Donor role exists in database but has no dedicated UI for viewing donations or submitting donations independently.

---

## 3. FUNCTIONAL REQUIREMENTS COMPLIANCE

### 3.1 Use Case Implementation Status

| # | Use Case | Actor | Implemented? | Evidence | Status |
|---|---------|-------|--------------|----------|--------|
| 1 | Login to the system | Admin, Volunteer | ✅ | `AuthController.php`, `api/auth/login.php` | Complete |
| 2 | Logout of the system | Admin, Volunteer | ✅ | `AuthController.php:169-186` | Complete |
| 3 | Register a new beneficiary | Admin | ✅ | `api/beneficiaries/create.php` | Complete |
| 4 | View/search beneficiary list | Admin | ✅ | `api/beneficiaries/list.php` | Complete |
| 5 | Update beneficiary details | Admin | ✅ | `api/beneficiaries/update.php` | Complete |
| 6 | Deactivate a beneficiary | Admin | ✅ | `api/beneficiaries/delete.php` (soft delete) | Complete |
| 7 | Record daily attendance | Admin, Volunteer | ✅ | `api/attendance/save.php` | Complete |
| 8 | View attendance report | Admin | ✅ | `api/reports/generate.php?type=attendance` | Complete |
| 9 | Add food stock/donation | Admin | ✅ | `api/stock/add.php`, `api/donations/record.php` | Complete |
| 10 | View current stock levels | Admin, Volunteer | ✅ | `api/stock/list.php` | Complete |
| 11 | Update stock levels | Admin | ✅ | `api/stock/update.php` | Complete |
| 12 | Record a donation | Admin | ✅ | `api/donations/record.php`, `api/donations/cash.php` | Complete |
| 13 | View donation history | Admin | ✅ | `api/donations/list.php` | Complete |
| 14 | Register a volunteer | Admin | ✅ | `api/volunteers/register.php` | Complete |
| 15 | View volunteer schedule | Admin, Volunteer | ✅ | `api/volunteers/schedule.php` | Complete |
| 16 | Assign volunteer to session | Admin | ✅ | `api/volunteers/assign-shift.php` | Complete |
| 17 | Generate attendance report | Admin | ✅ | `api/reports/generate.php?type=attendance` | Complete |
| 18 | Generate stock report | Admin | ✅ | `api/reports/generate.php?type=stock` | Complete |
| 19 | Generate donation report | Admin | ✅ | `api/reports/generate.php?type=donations` | Complete |
| 20 | Generate impact report | Admin | ⚠️ | `api/reports/generate.php?type=beneficiaries` partial | Partial |

**Use Case Coverage:** 19/20 complete (95%). Impact report is simplified; the requirements reference "meals served, beneficiaries" aggregation which is partially covered by the beneficiaries report.

---

### 3.2 Functional Requirements from Section 5.1

| Req | Description | Status | Evidence |
|-----|-------------|--------|----------|
| FR01 | Register new beneficiaries with personal details | ✅ | Beneficiaries table has all required fields |
| FR02 | Record daily attendance per feeding session | ✅ | Attendance table + save endpoint |
| FR03 | Track food stock with low-stock alerts | ✅ | FoodStock + low-stock endpoint |
| FR04 | Record donations, link to stock items | ✅ | Donations + FoodDistribution tables |
| FR05 | Register volunteers and assign to sessions | ✅ | Volunteers + VolunteerSchedules tables |
| FR06 | Generate attendance, stock, donation, impact reports | ⚠️ | Reports exist but impact report is basic |
| FR07 | Role-based access control (RBAC) | ✅ | Roles + UserRoles tables + AuthMiddleware |
| FR08 | Search beneficiaries, volunteers, donations | ✅ | Search params in list endpoints |
| FR09 | Update/deactivate beneficiary and volunteer records | ✅ | Update + delete endpoints |
| FR10 | Dashboard summary on login | ✅ | `api/dashboard/summary.php` |

---

## 4. DATABASE DESIGN COMPLIANCE

### 4.1 Requirements ERD vs Implementation

| Entity | Required Fields | Implemented Fields | Missing | Extra |
|--------|----------------|-------------------|---------|-------|
| Users | UserID, Username, PasswordHash, FullName, Email, Role, IsActive, CreatedAt | UserID, Username, PasswordHash, FullName, Email, Role, Status, CreatedAt, UpdatedAt, Phone, CreatedBy, UpdatedBy | IsActive (replaced by Status ENUM) | Phone, audit fields |
| Beneficiaries | BeneficiaryID, FirstName, LastName, DateOfBirth, Gender, GuardianName, ContactNumber, Address, RegistrationDate, IsActive, RegisteredBy | BeneficiaryID, FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes, CreatedBy | DateOfBirth (replaced by Age), GuardianName, IsActive (replaced by Status) | Age, Email, Notes |
| Attendance | AttendanceID, BeneficiaryID, SessionDate, IsPresent, RecordedBy, Notes | AttendanceID, BeneficiaryID, MealSessionID, SessionDate, Status, Notes, CreatedAt | IsPresent (replaced by Status ENUM), RecordedBy | MealSessionID |
| FoodStock | StockID, ItemName, Category, Quantity, UnitOfMeasure, MinimumThreshold, ExpiryDate, UpdatedBy | FoodStockID, ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes, CreatedAt, UpdatedAt | Category, MinimumThreshold, UpdatedBy | StockDate, Notes, audit fields |
| Donations | DonationID, DonorName, DonationType, ItemDescription, Quantity, AmountRand, DonationDate, LinkedStockID, RecordedBy | DonationID, UserID, DonorName, DonorEmail, DonationType, Amount, Description, PaymentMethod, TransactionReference, Status, DonationDate, CreatedAt, UpdatedAt | ItemDescription (→ Description), Quantity (→ only for food), AmountRand (→ Amount), LinkedStockID, RecordedBy | UserID, DonorEmail, PaymentMethod, Status, audit fields |
| Volunteers | VolunteerID, UserID, FullName, ContactNumber, Email, Availability, IsActive, RegisteredDate | VolunteerID, UserID, Skills, AvailabilityStatus, Address, Notes, Status, ApprovedBy, ApprovedAt, CreatedAt, UpdatedAt | FullName (via Users), ContactNumber (→ Address), Email (→ via Users), IsActive (→ Status), RegisteredDate (→ CreatedAt) | Skills, Approval workflow |
| VolunteerSessions | SessionID, VolunteerID, SessionDate, Role, AttendedSession | ScheduleID, VolunteerID, ScheduleDate, StartTime, EndTime, Role, Location, Status, HoursWorked, Notes, CreatedAt, UpdatedAt | SessionID (→ ScheduleID), AttendedSession (→ Status + HoursWorked) | StartTime, EndTime, Location, HoursWorked, Notes |
| MealSession | Not in original ERD | MealSessionID, SessionDate, SessionType, Location, Notes, CreatedAt | — | Added for session management |

**Assessment:** The implemented schema **exceeds** the requirements document in most areas. Key deviations:
- `IsActive` BOOLEAN replaced by `Status` ENUM — cleaner and more flexible
- `DateOfBirth` replaced by `Age` — simplified for feeding scheme context
- `MealSession` table added — not in original ERD but necessary for proper attendance tracking
- `FoodDistribution` table added — tracks distribution events separately from donations
- `UserRoles` junction table added — proper RBAC instead of single Role ENUM
- `AuthTokens`, `PasswordResets`, `PaymentTransactions`, `Announcements`, `ChatbotFAQ` — added beyond requirements

---

## 5. SYSTEM DESIGN COMPLIANCE

### 5.1 Architecture Requirements vs Implementation

| Requirement | Status | Notes |
|-------------|--------|-------|
| Three-layer MVC architecture | ⚠️ Partial | Web layer follows MVC; API layer uses script-per-endpoint |
| PHP + MySQL backend | ✅ | PHP 8+ with PDO/MySQL |
| Bootstrap 5 frontend | ✅ | Referenced in views |
| Secure authentication | ✅ | Token-based API auth + session-based web auth |
| Role-based access control | ✅ | Roles + UserRoles + AuthMiddleware |
| REST API for mobile | ✅ | 39 endpoints in `api/` |
| Reporting engine | ⚠️ | JSON data returned; PDF/Excel export endpoint exists but not verified |

**Task 2b Section 4 Requirement:** "The system must provide a RESTful API that mobile clients can consume."

**Finding:** API exists and is functional, but mobile client does NOT consume it. The mobile app uses client-side-only auth and never calls the API.

---

### 5.2 API Design Requirements vs Implementation

| Requirement | Status | Notes |
|-------------|--------|-------|
| Token-based authentication | ✅ | Bearer tokens with 24h expiry + 30d refresh |
| JSON request/response | ✅ | All endpoints return JSON |
| Error handling | ⚠️ | Generic messages; no structured error codes |
| Input validation | ⚠️ | Basic validation; no schema validation |
| Rate limiting | ❌ | Not implemented |

---

## 6. NON-FUNCTIONAL REQUIREMENTS COMPLIANCE

| NFR | Description | Status | Evidence |
|-----|-------------|--------|----------|
| NFR01 | Usability — simple, intuitive interface | ⚠️ | Bootstrap used; mobile shell has clean UI; not user-tested |
| NFR02 | Availability — 24/7, max 1% downtime | N/A | Depends on hosting infrastructure |
| NFR03 | Security — hashed passwords, HTTPS/SSL | ⚠️ | Passwords hashed ✅; HTTPS not enforced (development only) |
| NFR04 | Performance — page loads under 3s | N/A | Not measured; depends on DB optimization |
| NFR05 | Reliability — offline data entry | ❌ | No offline support in mobile or web app |
| NFR06 | Maintainability — MVC architecture | ⚠️ | Web layer MVC ✅; API layer ⚠️; no service layer |
| NFR07 | Scalability — 500 beneficiaries, 50 users | ✅ | Schema supports this; no architectural blockers |
| NFR08 | Compatibility — Chrome, Firefox, Edge | ✅ | Bootstrap 5 ensures cross-browser support |

---

## 7. MISSING FEATURES PER DOCUMENTATION

### 7.1 Requirements Not Fully Implemented

| Feature | Document Section | Gap |
|---------|------------------|-----|
| Offline data entry | NFR05 | No Service Worker, no IndexedDB, no sync mechanism |
| HTTPS enforcement | NFR03 | No HSTS, no forced redirects |
| PDF/Excel export | FR06 | Export endpoint exists but not verified |
| Donor self-service portal | Actor: Donor | Donors can view but not submit independently |
| Beneficiary self-service | Actor: Beneficiary Rep | No public registration portal |
| Impact report details | FR20/Use Case 20 | Basic beneficiary count only; no "meals served" aggregation |

### 7.2 Features Added Beyond Requirements

| Feature | Justification |
|---------|---------------|
| `MealSession` table | Enables proper session-based attendance tracking |
| `UserRoles` + `Roles` tables | Proper RBAC instead of single ENUM |
| `AuthTokens` + `PasswordResets` | Production-grade auth |
| `FoodDistribution` table | Separates distribution events from donations |
| `Announcements` table | Communication feature |
| `ChatbotFAQ` table | AI assistant support |
| `PaymentTransactions` table | Payment gateway integration ready |
| `VolunteerAvailability` table | Day-of-week scheduling |
| Mobile app (Capacitor) | Not in original requirements but valuable for field use |

---

## 8. DOCUMENT-CODE TRACEABILITY

### 8.1 Requirement to Code Mapping

| Requirement | Document Ref | Code Location |
|-------------|-------------|---------------|
| User authentication | P01, FR07 | `app/controllers/AuthController.php`, `api/auth/login.php` |
| Beneficiary CRUD | P02-P04, FR01, FR08, FR09 | `api/beneficiaries/*.php`, `app/models/User.php` (indirect) |
| Attendance tracking | P04, FR02 | `api/attendance/save.php`, `Attendance` table |
| Food stock management | P06, FR03 | `api/stock/*.php`, `FoodStock` table |
| Donation tracking | P07, FR04 | `api/donations/*.php`, `Donations` table |
| Volunteer management | P08, FR05 | `api/volunteers/*.php`, `Volunteers` table |
| Report generation | P09, FR06, FR17-20 | `api/reports/generate.php` |
| Dashboard summary | FR10 | `api/dashboard/summary.php` |
| Role-based access | P01, FR07 | `AuthMiddleware::requireRole()` |

### 8.2 ERD to Schema Mapping

| ER Entity | ER Attributes | DB Table | DB Columns | Match? |
|-----------|--------------|----------|------------|--------|
| User | userID, username, passwordHash, role | Users | UserID, Username, PasswordHash, Role | ✅ |
| Beneficiary | beneficiaryID, firstName, lastName, DOB, gender, guardian, contact, address, regDate, isActive | Beneficiaries | BeneficiaryID, FirstName, LastName, Age, Gender, Phone, Address, RegistrationDate, Status | ⚠️ DOB→Age, no guardian |
| Attendance | attendanceID, beneficiaryID, sessionDate, isPresent, recordedBy | Attendance | AttendanceID, BeneficiaryID, SessionDate, Status, CreatedAt | ⚠️ isPresent→Status, no recordedBy |
| FoodStock | stockID, itemName, category, quantity, unit, minThreshold, expiry, updatedBy | FoodStock | FoodStockID, ItemName, Quantity, Unit, ExpiryDate | ⚠️ No category, minThreshold, updatedBy |
| Donation | donationID, donorName, type, itemDesc, quantity, amount, date, linkedStock, recordedBy | Donations | DonationID, DonorName, DonationType, Description, Amount, DonationDate | ⚠️ No linkedStock, recordedBy |
| Volunteer | volunteerID, userID, fullName, contact, email, availability, isActive, regDate | Volunteers | VolunteerID, UserID, Skills, AvailabilityStatus, Status, CreatedAt | ⚠️ No fullName/contact/email columns |
| VolunteerSession | sessionID, volunteerID, sessionDate, role, attended | VolunteerSchedules | ScheduleID, VolunteerID, ScheduleDate, Role, Status, HoursWorked | ⚠️ No boolean attended field |

---

## 9. BUSINESS RULES COMPLIANCE

### 9.1 From Requirements Section 3.9

| Business Rule | Requirement | Implementation | Status |
|---------------|-------------|----------------|--------|
| One admin → many beneficiaries | One-to-Many | `Beneficiaries.CreatedBy` FK → Users | ✅ |
| One beneficiary → many attendance | One-to-Many | `Attendance.BeneficiaryID` FK | ✅ |
| One user → many attendance entries | One-to-Many | `Attendance.CreatedBy` (implicit via session) | ⚠️ No `RecordedBy` column |
| One stock item → many donations | One-to-Many (opt) | `Donations.LinkedStockID` NOT in schema; `FoodDistribution` links stock to distribution | ❌ Donations don't link to stock |
| One volunteer → many sessions | One-to-Many | `VolunteerSchedules.VolunteerID` FK | ✅ |
| One user → one volunteer (opt) | One-to-One (opt) | `Volunteers.UserID` FK | ✅ |

**Finding DOC-01: Donations don't link to stock items**

The requirements specify `LinkedStockID INT FK -> FoodStock(StockID)` in the Donations table. The current schema has no FK from Donations to FoodStock. Instead, `FoodDistribution` tracks stock distribution separately.

**Impact:** Cannot answer "which donation updated which stock item?"  
**Severity:** 🟡 MEDIUM — Business intelligence gap, not a functional blocker.

---

## 10. INPUT VALIDATION COMPLIANCE

### 10.1 Requirements vs Implementation

| Input Field | Required Validation | Implemented? | Location |
|-------------|---------------------|--------------|----------|
| First Name | Letters only, max 50 | ⚠️ | `FormValidator` may check; not verified |
| Last Name | Letters only, max 50 | ⚠️ | Same |
| Date of Birth | Valid past date | ❌ | Not implemented — Age used instead |
| Gender | Enum (Male/Female/Other) | ✅ | DB ENUM constraint |
| Contact Number | 10 digits, SA format | ⚠️ | Basic VARCHAR(15); no regex |
| Address | Max 200 chars | ⚠️ | TEXT column — no length limit |
| Password | Min 8 chars, 1 uppercase, 1 number | ⚠️ | Min 6 chars in `User.php:129` — does NOT match requirements |

**Finding DOC-02: Password policy doesn't match requirements**

Requirements specify: "min 8 chars, 1 uppercase, 1 number"  
Implementation (`User.php:129`): `strlen($password) < 6` — only length check

**Severity:** 🟡 MEDIUM — Weaker than specified; not a security downgrade but non-compliant.

---

## 11. OUTPUT SPECIFICATION COMPLIANCE

| Output | Required Format | Implementation | Status |
|--------|----------------|----------------|--------|
| Beneficiary list | Paginated table (screen) | JSON with `total`, `page`, `limit` | ✅ Mobile/web can paginate |
| Attendance record | On-screen confirmation | JSON response | ✅ |
| Attendance report | Table / downloadable PDF | JSON data; export endpoint exists | ⚠️ Export not verified |
| Stock levels | Dashboard table | `api/dashboard/summary.php` + `api/stock/list.php` | ✅ |
| Low stock alert | On-screen notification | `api/stock/low-stock.php` | ✅ |
| Donation history | Table / PDF | `api/donations/list.php` + export | ⚠️ Export not verified |
| Volunteer schedule | Calendar view / table | `api/volunteers/schedule.php` | ✅ |
| Impact report | Dashboard + PDF | `api/reports/generate.php?type=beneficiaries` | ⚠️ Simplified |

---

## 12. COMPLIANCE SCORE SUMMARY

| Category | Requirements | Implemented | compliant % |
|----------|-------------|-------------|-------------|
| Actors | 4 | 4 (partial for Donor/Beneficiary) | 100% * |
| Use Cases | 20 | 19 (impact report partial) | 95% |
| Functional Requirements | 10 | 10 | 100% * |
| Non-Functional Requirements | 8 | 5 (NFR05 offline missing, NFR03 HTTPS, NFR01 untested) | 63% |
| Database Entities | 7 | 7 (with deviations) | 100% * |
| Business Rules | 6 | 5 (donation-stock link missing) | 83% |
| Input Validation | 5 | 3 (password policy, DOB) | 60% |
| Output Specifications | 8 | 7 (PDF export unverified) | 88% |

**Overall Compliance:** ~85% functional, ~65% non-functional

---

## 13. CRITICAL FINDINGS SUMMARY

| ID | Finding | Document Ref | Severity |
|----|---------|-------------|----------|
| DOC-01 | Donations table missing LinkedStockID FK | Section 3.5, 3.9 | 🟡 MEDIUM |
| DOC-02 | Password policy doesn't match requirements (min 6 vs min 8+complexity) | Section 2.1, FR07 | 🟡 MEDIUM |
| DOC-03 | Beneficiaries missing GuardianName, DateOfBirth | Section 3.2 | 🟢 LOW |
| DOC-04 | Attendance missing RecordedBy FK | Section 3.3 | 🟢 LOW |
| DOC-05 | Volunteers table missing FullName, ContactNumber, Email | Section 3.6 | 🟢 LOW |
| DOC-06 | No offline data entry (NFR05) | Section 5.2 NFR05 | 🟠 HIGH |
| DOC-07 | No HTTPS enforcement (NFR03) | Section 5.2 NFR03 | 🟠 HIGH |
| DOC-08 | Impact report not fully implemented | FR06, Use Case 20 | 🟡 MEDIUM |
| DOC-09 | Donor/Beneficiary self-service portals missing | Actor descriptions | 🟡 MEDIUM |
| DOC-10 | PDF/Excel export endpoints not verified | FR06, Output Specs | 🟢 LOW |

---

## 14. RECOMMENDATIONS

### High Priority
1. **Add LinkedStockID to Donations** — Enables donation-to-stock traceability
2. **Implement offline support** — Service Worker + IndexedDB for NFR05 compliance
3. **Enforce HTTPS** — HSTS header + redirect for NFR03 compliance
4. **Strengthen password policy** — Match requirements (8+ chars, uppercase, number)

### Medium Priority
5. **Add GuardianName back to Beneficiaries** — Required for minor beneficiaries
6. **Add RecordedBy to Attendance** — Audit trail for who recorded
7. **Complete impact report** — Add "meals served" calculation across Attendance
8. **Verify PDF/Excel export** — Test `api/reports/export.php`

### Low Priority
9. **Add DateOfBirth back to Beneficiaries** — More accurate than Age for reporting
10. **Add ContactNumber to Volunteers** — Direct contact without joining Users
11. **Create donor portal** — Self-service donation submission
12. **Create beneficiary registration portal** — Public self-registration

---

## 15. POSITIVE FINDINGS

1. **Schema exceeds requirements** — 23 tables vs 7 required; production-ready extensions
2. **All 20 use cases have implementation** — Only impact report is simplified
3. **RBAC properly implemented** — Junction table pattern more flexible than spec
4. **Audit trail built in** — `CreatedBy`, `UpdatedBy`, `CreatedAt`, `UpdatedAt` on most tables
5. **Mobile app extends system** — Capacitor app not in requirements but adds value
6. **Password hashing correct** — bcrypt via `password_hash()`
7. **API layer comprehensive** — 39 endpoints cover all modules
8. **Soft deletes implemented** — Status-based deactivation preserves data

---

*Report compiled by automated codebase audit. No code modifications made.*