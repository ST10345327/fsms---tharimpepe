# Required Corrections List — FSMS Database Reconciliation

**Date:** 2026-06-18
**Source Reports:** DATABASE_RECONCILIATION_REPORT.md, ERD_VALIDATION_REPORT.md
**Scope:** Documented findings requiring corrective action. No corrections have been implemented.

---

## 1. CORRECTION PRIORITY MATRIX

| Priority | Count | Categories |
|----------|-------|------------|
| 🔴 HIGH | 1 | Normalization |
| 🟠 MEDIUM | 1 | Naming Consistency |
| 🟡 LOW | 7 | Missing Indexes |
| 🟢 OPTIONAL | 1 | New Table (Reporting) |

---

## 2. HIGH PRIORITY CORRECTIONS

### CORR-01: Normalize Volunteer Name Fields

| Field | Value |
|-------|-------|
| **ID** | CORR-01 |
| **Category** | Normalization / Data Integrity |
| **Severity** | 🔴 HIGH |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | DUP-01, F-03 |

**Description:**
The `Volunteers` table stores `FirstName` and `LastName` separately, while the `Users` table stores `FullName` as a single field. When a user is also a volunteer, their name exists in two places with different structures. This causes data sync bugs, inconsistent reporting, and violates best practices.

**Current State:**
- `Users.FullName` VARCHAR(255)
- `Volunteers.FirstName` VARCHAR(100)
- `Volunteers.LastName` VARCHAR(100)

**Recommended Fix (Option A — Remove from Volunteers):**
```sql
-- Remove duplicate name columns from Volunteers
ALTER TABLE Volunteers DROP COLUMN FirstName;
ALTER TABLE Volunteers DROP COLUMN LastName;

-- All name queries must JOIN through Users:
-- SELECT v.*, u.FullName FROM Volunteers v
-- JOIN Users u ON v.UserID = u.UserID
```

**Recommended Fix (Option B — Standardize Users table):**
```sql
-- Split Users.FullName into first/last
ALTER TABLE Users ADD COLUMN FirstName VARCHAR(100) AFTER UserID;
ALTER TABLE Users ADD COLUMN LastName VARCHAR(100) AFTER FirstName;
-- Migrate data, remove FullName
ALTER TABLE Volunteers DROP COLUMN FirstName;
ALTER TABLE Volunteers DROP COLUMN LastName;
```

**Recommendation:** Option A is simpler and avoids migrating existing data. Use `Users.FullName` via JOIN for all volunteer name displays.

**Impact:** Affects all volunteer list views, search functionality, and reports. Must update:
- `app/models/Volunteer.php`
- `app/controllers/VolunteerController.php`
- All volunteer listing views
- Any JOIN queries that pull volunteer names

**Effort:** ~2 hours (model updates + view updates + testing)

---

## 3. MEDIUM PRIORITY CORRECTIONS

### CORR-02: Standardize VolunteerSchedule Table Naming

| Field | Value |
|-------|-------|
| **ID** | CORR-02 |
| **Category** | Naming Consistency |
| **Severity** | 🟠 MEDIUM |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-02 |

**Description:**
The database table is named `VolunteerSchedules` (plural), but the model class `VolunteerSchedule.php` and ERD diagram use the singular form `VolunteerSchedule`. Some code references may use either form, causing confusion and potential runtime errors.

**Current State:**
- Table: `VolunteerSchedules` (plural)
- Model file: `app/models/VolunteerSchedule.php`
- ERD diagram: shows `VolunteerSchedule` (singular)
- CODEBASE_KNOWLEDGE.md: references both `VolunteerSchedule` and `VolunteerSchedules`

**Recommended Fix:**

**Option A — Rename table to singular (match ERD/convention):**
```sql
RENAME TABLE VolunteerSchedules TO VolunteerSchedule;
-- Update all model/controller SQL queries referencing VolunteerSchedules
```

**Option B — Keep table name, standardize code references (recommended):**
```php
// In VolunteerSchedule.php model — ensure all queries use:
// "SELECT * FROM VolunteerSchedules"  (plural, matching schema.sql)
```

**Recommendation:** Option B — Keep the existing `VolunteerSchedules` table name (schema.sql is authoritative). Audit all code files and update any references to `VolunteerSchedule` (singular) to use `VolunteerSchedules` (plural).

**Files to Check/Update:**
- `app/models/VolunteerSchedule.php`
- `app/controllers/VolunteerScheduleController.php`
- `app/models/VolunteerSchedule.php` (static methods class)
- All SQL queries in controllers/models

**Effort:** ~1 hour (search/replace across codebase + testing)

---

## 4. LOW PRIORITY CORRECTIONS (Missing Indexes)

### CORR-03: Add Missing Index — Beneficiaries.CreatedBy

| Field | Value |
|-------|-------|
| **ID** | CORR-03 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-04 |

```sql
CREATE INDEX idx_beneficiary_created_by ON Beneficiaries(CreatedBy);
```

**Justification:** Admin registration tracking queries filter by `CreatedBy` to show which staff member registered each beneficiary.

---

### CORR-04: Add Missing Index — Volunteers.ApprovedBy

| Field | Value |
|-------|-------|
| **ID** | CORR-04 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-05 |

```sql
CREATE INDEX idx_volunteer_approved_by ON Volunteers(ApprovedBy);
```

**Justification:** Admin approval tracking queries join/filter by `ApprovedBy` to review approval actions.

---

### CORR-05: Add Missing Index — Attendance.Status

| Field | Value |
|-------|-------|
| **ID** | CORR-05 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-06 |

```sql
CREATE INDEX idx_attendance_status ON Attendance(Status);
```

**Justification:** Daily attendance sheets frequently filter by `Status` (present/absent/marked).

---

### CORR-06: Add Missing Index — FoodStock.StockDate

| Field | Value |
|-------|-------|
| **ID** | CORR-06 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-07 |

```sql
CREATE INDEX idx_stock_stock_date ON FoodStock(StockDate);
```

**Justification:** Date-range inventory reports filter by `StockDate`. The `ExpiryDate` index does not cover `StockDate` queries efficiently.

---

### CORR-07: Add Missing Index — FoodDistribution.CreatedAt

| Field | Value |
|-------|-------|
| **ID** | CORR-07 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-08 |

```sql
CREATE INDEX idx_food_distribution_created_at ON FoodDistribution(CreatedAt);
```

**Justification:** Distribution chronology queries sort/filter by `CreatedAt` for audit trails.

---

### CORR-08: Add Missing Index — Messages.SentAt

| Field | Value |
|-------|-------|
| **ID** | CORR-08 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-09 |

```sql
CREATE INDEX idx_message_sent_at ON Messages(SentAt);
```

**Justification:** Message timeline/threading views sort by `SentAt`. The existing `Subject` index does not help timeline queries.

---

### CORR-09: Add Missing Composite Index — VolunteerSchedules (VolunteerID, ScheduleDate)

| Field | Value |
|-------|-------|
| **ID** | CORR-09 |
| **Category** | Performance / Index |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |
| **Source Finding** | F-10 |

```sql
CREATE INDEX idx_vol_sched_vol_date ON VolunteerSchedules(VolunteerID, ScheduleDate);
```

**Justification:** Schedule lookups query by volunteer AND date together. Individual indexes on `VolunteerID` and `ScheduleDate` are less efficient than a composite index for this common query pattern.

---

## 5. OPTIONAL CORRECTIONS

### CORR-10: Add UNIQUE Constraint — Volunteers.UserID

| Field | Value |
|-------|-------|
| **ID** | CORR-10 |
| **Category** | Data Integrity |
| **Severity** | 🟢 OPTIONAL |
| **Status** | NOT IMPLEMENTED |

**Description:**
The `Volunteers` table has a logical 1:1 relationship with `Users` (one user has one volunteer profile), but the `UserID` column is not declared as UNIQUE. Application-level enforcement exists but is not backed by a database constraint.

```sql
ALTER TABLE Volunteers ADD UNIQUE KEY uq_volunteer_user (UserID);
```

**Justification:** Prevents duplicate volunteer profiles for the same user at the database level. Business logic should already prevent this, but a UNIQUE constraint provides defense-in-depth.

**Effort:** ~15 minutes (migration + test)

---

## 6. DOCUMENTATION CORRECTIONS

### DOC-01: Update ERD Diagram — VolunteerShifts Entity

| Field | Value |
|-------|-------|
| **ID** | DOC-01 |
| **Category** | Documentation |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |

**Description:**
The ERD diagram in `codebase-analysis-docs/CODEBASE_KNOWLEDGE.md` references a `VolunteerShifts` entity connected to `VolunteerSchedule`, but this table does not exist in the schema. Either:
1. Remove the `VolunteerShifts` node from the ERD diagram, OR
2. Add a comment noting that `VolunteerSchedules` subsumes `VolunteerShifts` functionality.

**Files to Update:**
- `codebase-analysis-docs/CODEBASE_KNOWLEDGE.md` (Section 3.1 ERD diagram)
- Any other ERD diagrams in `docs/diagrams/`

---

### DOC-02: Update AUTH_AUDIT.md — Remove IsActive Reference

| Field | Value |
|-------|-------|
| **ID** | DOC-02 |
| **Category** | Documentation |
| **Severity** | 🟡 LOW |
| **Status** | NOT IMPLEMENTED |

**Description:**
`AUTH_AUDIT.md` references a non-existent `IsActive` column on the `Users` table. The schema uses only `Status ENUM('active','inactive')`. The documentation should be corrected to avoid confusion.

**Files to Update:**
- `codebase-analysis-docs/AUTH_AUDIT.md`

---

## 7. SUMMARY TABLE

| ID | Category | Description | Severity | Effort | Status |
|----|----------|-------------|----------|--------|--------|
| CORR-01 | Normalization | Remove FirstName/LastName from Volunteers; use Users.FullName | 🔴 HIGH | 2 hrs | NOT IMPLEMENTED |
| CORR-02 | Naming | Standardize VolunteerSchedules references in code | 🟠 MEDIUM | 1 hr | NOT IMPLEMENTED |
| CORR-03 | Index | Add idx_beneficiary_created_by on Beneficiaries(CreatedBy) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-04 | Index | Add idx_volunteer_approved_by on Volunteers(ApprovedBy) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-05 | Index | Add idx_attendance_status on Attendance(Status) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-06 | Index | Add idx_stock_stock_date on FoodStock(StockDate) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-07 | Index | Add idx_food_distribution_created_at on FoodDistribution(CreatedAt) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-08 | Index | Add idx_message_sent_at on Messages(SentAt) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-09 | Index | Add composite idx_vol_sched_vol_date on VolunteerSchedules(VolunteerID, ScheduleDate) | 🟡 LOW | 5 min | NOT IMPLEMENTED |
| CORR-10 | Integrity | Add UNIQUE constraint uq_volunteer_user on Volunteers(UserID) | 🟢 OPTIONAL | 15 min | NOT IMPLEMENTED |
| DOC-01 | Documentation | Fix ERD diagram VolunteerShifts reference | 🟡 LOW | 30 min | NOT IMPLEMENTED |
| DOC-02 | Documentation | Remove IsActive reference from AUTH_AUDIT.md | 🟡 LOW | 15 min | NOT IMPLEMENTED |

---

## 8. IMPLEMENTATION NOTES

**Do NOT implement these corrections based on this document alone.** Each correction should be:
1. Reviewed against current production data
2. Tested in a development/staging environment
3. Implemented with appropriate migrations/rollback plans
4. Validated with existing test suite (`tests/run_all_tests.php`)

**Special Consideration for CORR-01 (Normalization):**
- Requires data migration strategy for existing volunteer records
- Must update all application code that reads/writes volunteer names
- Regression testing required for all volunteer-related features
- Consider running during maintenance window

**Index Corrections (CORR-03 through CORR-09):**
- Low risk; can be applied during low-traffic periods
- No data migration required
- Backward-compatible (only improves query performance)

---

*List compiled from DATABASE_RECONCILIATION_REPORT.md and ERD_VALIDATION_REPORT.md. No corrections have been applied to the codebase or database.*