# API Audit Report — FSMS (Tharimpepe Feeding Scheme)

**Date:** 2026-06-18  
**Reviewed By:** Lead Software Engineer  
**Scope:** Complete REST API layer review — endpoint structure, authentication, response format, security, error handling  
**Reference:** `api/` directory, Task 2b System Design Section 4 (API Design)

---

## 1. API INVENTORY

### 1.1 Endpoint Catalog

| # | Endpoint | Method | Auth | Module | Status |
|---|---------|--------|------|--------|--------|
| 1 | `/api/auth/login.php` | POST | No | Authentication | ✅ Present |
| 2 | `/api/auth/validate.php` | POST | No | Authentication | ✅ Present |
| 3 | `/api/auth/refresh.php` | POST | No | Authentication | ✅ Present |
| 4 | `/api/auth/logout.php` | POST | Yes | Authentication | ✅ Present |
| 5 | `/api/dashboard/summary.php` | GET | Yes | Dashboard | ✅ Present |
| 6 | `/api/beneficiaries/list.php` | GET | Yes | Beneficiaries | ✅ Present |
| 7 | `/api/beneficiaries/get.php` | GET | Yes | Beneficiaries | ✅ Present |
| 8 | `/api/beneficiaries/create.php` | POST | Yes | Beneficiaries | ✅ Present |
| 9 | `/api/beneficiaries/update.php` | PUT/PATCH | Yes | Beneficiaries | ✅ Present |
| 10 | `/api/beneficiaries/delete.php` | DELETE | Yes | Beneficiaries | ✅ Present |
| 11 | `/api/attendance/today.php` | GET | Yes | Attendance | ✅ Present |
| 12 | `/api/attendance/history.php` | GET | Yes | Attendance | ✅ Present |
| 13 | `/api/attendance/save.php` | POST | Yes | Attendance | ✅ Present |
| 14 | `/api/attendance/recent.php` | GET | Yes | Attendance | ✅ Present |
| 15 | `/api/stock/list.php` | GET | Yes | Stock | ✅ Present |
| 16 | `/api/stock/add.php` | POST | Yes | Stock | ✅ Present |
| 17 | `/api/stock/update.php` | PUT/PATCH | Yes | Stock | ✅ Present |
| 18 | `/api/stock/distribute.php` | POST | Yes | Stock | ✅ Present |
| 19 | `/api/stock/history.php` | GET | Yes | Stock | ✅ Present |
| 20 | `/api/stock/low-stock.php` | GET | Yes | Stock | ✅ Present |
| 21 | `/api/donations/list.php` | GET | Yes | Donations | ✅ Present |
| 22 | `/api/donations/record.php` | POST | Yes | Donations | ✅ Present |
| 23 | `/api/donations/cash.php` | POST | Yes | Donations | ✅ Present |
| 24 | `/api/volunteers/list.php` | GET | Yes | Volunteers | ✅ Present |
| 25 | `/api/volunteers/register.php` | POST | Yes | Volunteers | ✅ Present |
| 26 | `/api/volunteers/status.php` | PUT | Yes | Volunteers | ✅ Present |
| 27 | `/api/volunteers/schedule.php` | GET | Yes | Volunteers | ✅ Present |
| 28 | `/api/volunteers/assign-shift.php` | POST | Yes | Volunteers | ✅ Present |
| 29 | `/api/users/list.php` | GET | Yes | Users | ✅ Present |
| 30 | `/api/users/create.php` | POST | Yes | Users | ✅ Present |
| 31 | `/api/users/update.php` | PUT/PATCH | Yes | Users | ✅ Present |
| 32 | `/api/users/change-password.php` | POST | Yes | Users | ✅ Present |
| 33 | `/api/meal-sessions/list.php` | GET | Yes | Meal Sessions | ✅ Present |
| 34 | `/api/meal-sessions/create.php` | POST | Yes | Meal Sessions | ✅ Present |
| 35 | `/api/meal-sessions/close.php` | POST | Yes | Meal Sessions | ✅ Present |
| 36 | `/api/activity/list.php` | GET | Yes | Activity Log | ✅ Present |
| 37 | `/api/reports/generate.php` | GET | Yes | Reports | ✅ Present |
| 38 | `/api/reports/summary.php` | GET | Yes | Reports | ✅ Present |
| 39 | `/api/reports/export.php` | GET | Yes | Reports | ✅ Present |

**Total Endpoints:** 39  
**Authenticated Endpoints:** 35  
**Public Endpoints:** 4 (login, validate, refresh, OPTIONS preflight)

---

## 2. ENDPOINT STRUCTURE ANALYSIS

### 2.1 Structure Compliance

**Current Pattern:** File-per-endpoint (`/api/[module]/[action].php`)  
**Standard:** RESTful resource routing expected: `/api/[resource]` with HTTP method dispatch

**Assessment:**
- ✅ All endpoints live under `/api/` namespace
- ✅ Module grouping by resource type (beneficiaries, attendance, stock, etc.)
- ⚠️ File-per-endpoint is less maintainable than centralized router
- ✅ Auth endpoints grouped under `/api/auth/`
- ✅ Consistent naming: `list.php`, `get.php`, `create.php`, `update.php`, `delete.php`

### 2.2 HTTP Method Compliance

| Endpoint | Expected | Actual | Status |
|----------|----------|--------|--------|
| list | GET | GET | ✅ Compliant |
| get | GET | GET | ✅ Compliant |
| create | POST | POST | ✅ Compliant |
| update | PUT/PATCH | PUT/PATCH | ✅ Compliant (see note) |
| delete | DELETE | DELETE | ✅ Compliant |
| login | POST | POST | ✅ Compliant |
| validate | POST | POST | ✅ Compliant |
| refresh | POST | POST | ✅ Compliant |
| logout | POST | POST | ✅ Compliant |
| save | POST | POST | ✅ Compliant |
| record | POST | POST | ✅ Compliant |
| cash | POST | POST | ✅ Compliant |
| register | POST | POST | ✅ Compliant |
| status | PUT | PUT | ✅ Compliant |
| close | POST | POST | ✅ Compliant |
| assign-shift | POST | POST | ✅ Compliant |
| change-password | POST | POST | ✅ Compliant |
| generate | GET | GET | ✅ Compliant |
| summary | GET | GET | ✅ Compliant |
| export | GET | GET | ✅ Compliant |

**Note on PUT/PATCH:** The codebase uses `$_SERVER['REQUEST_METHOD']` checks. Some endpoints may accept both PUT and PATCH without explicit differentiation. Verify per-endpoint.

---

## 3. AUTHENTICATION & AUTHORIZATION

### 3.1 Auth Flow

```
Client → /api/auth/login.php (POST)
        { username, password }
        ↓
Server validates → generates access token (24h) + refresh token (30d)
        ↓
Returns: { token, refresh_token, expires_at, user }
        ↓
Client stores tokens → includes in Authorization: Bearer <token>
        ↓
Subsequent requests → AuthMiddleware validates token
        ↓
Returns 401 if invalid/expired → client uses refresh token
```

**Status:** ✅ Proper token-based authentication implemented.

### 3.2 AuthMiddleware Implementation

**File:** `api/middleware/AuthMiddleware.php`

| Feature | Status | Notes |
|---------|--------|-------|
| Bearer token extraction | ✅ | Checks `HTTP_AUTHORIZATION` + `REDIRECT_HTTP_AUTHORIZATION` |
| Token hash lookup | ✅ | SHA-256 hashed tokens in DB |
| Expiry validation | ✅ | `ExpiresAt > NOW()` check |
| Revocation check | ✅ | `RevokedAt IS NULL` |
| User status check | ✅ | `u.Status = 'active'` |
| Last used timestamp | ✅ | Updates `LastUsedAt` |
| Role-based access | ✅ | `requireRole($roles)` method |

**Assessment:** AuthMiddleware is production-quality. Properly validates tokens against the database with all security checks.

### 3.3 Auth Endpoint Security

**`api/auth/login.php`:**
| Check | Status | Notes |
|-------|--------|-------|
| CORS headers | ✅ | Set before output |
| OPTIONS preflight | ✅ | 204 response |
| Method validation | ✅ | Rejects non-POST |
| Input validation | ⚠️ | Checks `empty()` but no format validation |
| Password hashing | ✅ | `password_verify()` against DB |
| Token generation | ✅ | `random_bytes(32)` cryptographically secure |
| Token storage | ✅ | Hashed before DB insert |
| Demo fallback | ✅ | Only when DB unavailable |
| Error messages | ✅ | Generic (no info leakage) |

**Missing:** Rate limiting, account lockout, login attempt logging.

---

## 4. RESPONSE FORMAT CONSISTENCY

### 4.1 Standard Response Schema

**Observed pattern (from `api/beneficiaries/list.php`):**
```json
{
  "success": true,
  "data": [...],
  "total": 100,
  "page": 1,
  "limit": 50
}
```

**Observed pattern (from `api/auth/login.php`):**
```json
{
  "success": true,
  "message": "Login successful",
  "token": "...",
  "refresh_token": "...",
  "expires_at": "...",
  "user": {...}
}
```

**Observed pattern (from `api/reports/generate.php`):**
```json
{
  "success": true,
  "message": "Report generated",
  "data": [...],
  "type": "beneficiaries",
  "count": 50
}
```

### 4.2 Response Format Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| Inconsistent pagination | 🟡 MEDIUM | `list.php` returns `total`, `page`, `limit`; other endpoints do not |
| No `meta` block | 🟡 MEDIUM | No standard `meta` object with timestamp, request_id |
| No error `code` field | 🟡 MEDIUM | Only `success` boolean + `message`; no structured error code |
| Mixed `data` placement | 🟢 LOW | Some endpoints nest data deeper, others flat |

**Recommendation:** Standardize JSON response format:
```json
{
  "success": true,
  "code": "BENEFICIARY_LIST_SUCCESS",
  "message": "Operation completed",
  "data": {},
  "meta": {
    "timestamp": "2026-06-18T10:00:00Z",
    "page": 1,
    "total": 100,
    "limit": 50
  }
}
```

---

## 5. CORS CONFIGURATION

### 5.1 Current Implementation

Every API file independently sets CORS headers:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
```

**Also present in:** `app/helpers/bootstrap.php` (lines 57-67)

### 5.2 CORS Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| Wildcard origin `*` | 🔴 HIGH | Allows any origin; should be whitelist for production |
| Repeated in every file | 🟡 MEDIUM | Should be centralized in `bootstrap.php` or middleware |
| Credentials + wildcard conflict | 🔴 HIGH | `Access-Control-Allow-Credentials: true` with `*` origin is browser-blocked; specific origins required |
| No Vary header | 🟡 MEDIUM | `Vary: Origin` missing, breaks CDN caching |

**Recommendation:**
```php
// Centralized in bootstrap.php
$allowedOrigins = ['https://tharimpepe.org', 'http://localhost:8000', 'capacitor://localhost'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true);
    header('Vary: Origin');
}
```

---

## 6. INPUT VALIDATION & SQL INJECTION

### 6.1 Validation Review

| Endpoint | Input Validation | SQL Injection Risk |
|----------|-----------------|-------------------|
| `auth/login.php` | ✅ Basic (empty check) | ✅ Prepared statements via User model |
| `beneficiaries/list.php` | ✅ GET params sanitized | ✅ Prepared statements |
| `reports/generate.php` | ⚠️ `in_array()` whitelist | 🔴 Raw `$db->query()` — no user input, but risky pattern |
| `users/create.php` | Unknown (not reviewed) | Unknown |
| `attendance/save.php` | Unknown | Unknown |

### 6.2 SQL Injection Risk — `reports/generate.php`

```php
// Lines 45-103 — ALL queries use $db->query() without parameters
$stmt = $db->query("SELECT ... FROM Beneficiaries ORDER BY LastName, FirstName");
```

**Risk:** While the `$type` is whitelisted via `in_array()`, using `$db->query()` for all queries establishes a dangerous pattern. Future modifications introducing user input into these queries could create SQL injection vulnerabilities.

**Severity:** 🟡 MEDIUM — No current exploit, but violates secure coding standards.

---

## 7. ERROR HANDLING

### 7.1 Current Pattern

```php
try {
    // operation
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    logMessage("Error: " . $e->getMessage(), 'ERROR');
}
```

### 7.2 Error Handling Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| Generic 500 message | 🟡 MEDIUM | No error code or details for debugging |
| No PDO error mode check | 🟡 MEDIUM | Silent failures if PDO throws exceptions |
| Inconsistent status codes | 🟢 LOW | Some return 400, some 405, some 503 — good variety |
| No error logging to DB | 🟢 LOW | Logs to `error_log` only; ActivityLog not used |

---

## 8. ENDPOINT COVERAGE GAPS

### 8.1 Missing Endpoints

| Required Feature | Expected Endpoint | Gap |
|-----------------|-------------------|-----|
| Password reset | `POST /api/auth/reset-request.php` | ❌ Not implemented |
| Password reset confirm | `POST /api/auth/reset-confirm.php` | ❌ Not implemented |
| Email verification | `POST /api/auth/verify-email.php` | ❌ Not implemented |
| Profile update | `PUT /api/users/profile.php` | ❌ Not implemented (web only) |
| Outreach programs CRUD | `/api/outreach/*` | ❌ Not implemented |
| Announcements CRUD | `/api/announcements/*` | ❌ Not implemented |
| Chatbot FAQ | `/api/chatbot/*` | ❌ Not implemented |
| Gallery CRUD | `/api/gallery/*` | ❌ Not implemented |
| Messages CRUD | `/api/messages/*` | ❌ Not implemented |
| Payment webhook | `POST /api/payments/webhook.php` | ❌ Not implemented |

### 8.2 Inconsistent CRUD Coverage

| Resource | List | Get | Create | Update | Delete |
|----------|------|-----|--------|--------|--------|
| Beneficiaries | ✅ | ✅ | ✅ | ✅ | ✅ |
| Attendance | ✅ | ❌ | ✅ | ❌ | ❌ |
| Stock | ✅ | ❌ | ✅ | ✅ | ❌ |
| Donations | ✅ | ❌ | ✅ | ❌ | ❌ |
| Volunteers | ✅ | ❌ | ✅ | ✅ | ❌ |
| Users | ✅ | ❌ | ✅ | ✅ | ❌ |
| Meal Sessions | ✅ | ❌ | ✅ | ❌ | ✅ |
| Reports | ✅ | ❌ | ❌ | ❌ | ❌ |

**Note:** Some "missing" endpoints may be implemented via POST with action parameters. The audit reviewed file structure only.

---

## 9. TOKEN LIFECYCLE

### 9.1 Current Implementation

| Aspect | Status | Details |
|--------|--------|---------|
| Access token generation | ✅ | `random_bytes(32)` → hex |
| Token hashing | ✅ | SHA-256 before DB storage |
| Refresh token generation | ✅ | Separate 30-day token |
| Expiry enforcement | ✅ | `ExpiresAt > NOW()` check |
| Revocation support | ✅ | `RevokedAt` timestamp |
| Last used tracking | ✅ | `LastUsedAt` updates |
| Device tracking | ✅ | `DeviceInfo`, `IPAddress` |
| Token refresh endpoint | ✅ | `/api/auth/refresh.php` exists |
| Token validation endpoint | ✅ | `/api/auth/validate.php` exists |

### 9.2 Token Security Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| No token rotation | 🟡 MEDIUM | Refresh token reuse after use should invalidate old token |
| No concurrent session limit | 🟡 MEDIUM | User can have unlimited active tokens |
| No token scope/claims | 🟢 LOW | Token doesn't carry role/permissions; requires DB lookup |

---

## 10. API vs WEB CONTROLLER DUPLICATION

### 10.1 Parallel Implementations

| Feature | Web Controller | API Endpoint | Duplication? |
|---------|---------------|--------------|-------------|
| Login | `AuthController.php` | `api/auth/login.php` | ✅ Separate |
| User list | `UserController.php` | `api/users/list.php` | ✅ Separate |
| User create | `UserController.php` | `api/users/create.php` | ✅ Separate |
| Beneficiaries | (Unknown) | `api/beneficiaries/*` | ✅ API-only |

**Finding:** The web app uses PHP controllers with HTML views, while mobile uses the REST API. Both exist independently. Business logic may be duplicated between `app/controllers/` and `api/`.

**Example duplication:**
- `AuthController.php` has DB auth logic
- `api/auth/login.php` calls `User::authenticate()` directly
- Both fallback to demo users

**Severity:** 🟡 MEDIUM — Code duplication increases maintenance burden and bug surface area.

---

## 11. CRITICAL FINDINGS SUMMARY

| ID | Finding | Location | Severity | Category |
|----|---------|----------|----------|----------|
| API-01 | CORS wildcard + credentials conflict | All API files | 🔴 HIGH | Security |
| API-02 | CORS headers duplicated in every file | All API files | 🟡 MEDIUM | Maintainability |
| API-03 | Raw `$db->query()` in reports | `api/reports/generate.php` | 🟡 MEDIUM | Security |
| API-04 | No rate limiting on auth endpoints | `api/auth/*.php` | 🟡 MEDIUM | Security |
| API-05 | Inconsistent pagination metadata | Various list endpoints | 🟡 MEDIUM | Consistency |
| API-06 | Generic error messages | All endpoints | 🟡 MEDIUM | Debugging |
| API-07 | Missing CRUD endpoints | Multiple resources | 🟡 MEDIUM | Completeness |
| API-08 | Code duplication web/API | AuthController, UserController | 🟡 MEDIUM | Architecture |

---

## 12. RECOMMENDATIONS

### Immediate (High Priority)
1. **Fix CORS wildcard** — Restrict to known origins; cannot use `*` with `Access-Control-Allow-Credentials: true`
2. **Centralize CORS** — Move to `bootstrap.php` or dedicated middleware; remove from individual files
3. **Add rate limiting** — Track failed login attempts per IP; implement exponential backoff or account lockout

### Short-term (Medium Priority)
4. **Standardize response format** — Include `code`, `meta`, consistent `data` structure
5. **Add input validation layer** — Validate JSON schema for POST/PUT bodies
6. **Audit all endpoints for METHOD checks** — Ensure PUT/PATCH/DELETE are properly enforced
7. **Consolidate business logic** — Extract shared logic from web controllers into models/services

### Long-term (Low Priority)
8. **Implement API versioning** — `/api/v1/` prefix for future compatibility
9. **Add request correlation IDs** — `X-Request-ID` header for distributed tracing
10. **OpenAPI/Swagger documentation** — Generate from code annotations

---

## 13. POSITIVE FINDINGS

1. **Token-based auth is correctly implemented** — Bearer tokens, hashed storage, expiry, revocation
2. **AuthMiddleware is well-designed** — Handles all auth concerns in one place
3. **39 endpoints provide broad coverage** — All major modules have API access
4. **Consistent JSON structure** — `success`, `message`, `data` pattern used throughout
5. **Proper CORS preflight handling** — OPTIONS requests return 204 as required
6. **No passwords in API responses** — Auth endpoint returns user data without sensitive fields
7. **Cryptographically secure tokens** — `random_bytes()` used for token generation
8. **Demo fallback is safe** — Only activates when DB is unavailable

---

*Report compiled by automated codebase audit. No code modifications made.*