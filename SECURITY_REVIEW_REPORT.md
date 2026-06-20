# FSMS Security Review Report
**Project:** Tharimpepe Feeding Scheme Management System  
**Date:** 2026-06-18  
**Reviewer:** Lead Software Engineer  
**Scope:** Authentication, Authorization, Session, Token, Database, Web & Transport Security

---

## 1. AUTHENTICATION

### 1.1 `password_hash()` and `password_verify()`

| Attribute | Finding | File:Line | Status |
|-----------|---------|-----------|--------|
| Hashing algorithm | `PASSWORD_BCRYPT` used | `app/models/User.php:133` | SECURE |
| Password storage format | `PasswordHash` column, no plaintext | `app/models/User.php:61` | SECURE |
| Verify on login | `password_verify($password, $user["PasswordHash"])` | `app/models/User.php:41` | SECURE |
| Verify in API login | `password_verify($password, $demoUsers[$username])` | `api/auth/login.php:61` | SECURE |
| Minimum password length | 6 characters enforced | `app/models/User.php:128` | ACCEPTABLE |
| Password rehash on change | `password_hash($newPassword, PASSWORD_BCRYPT)` | `app/models/User.php:213` | SECURE |

**Assessment:** Password handling is correct. bcrypt via PHP's `password_hash()`/`password_verify()` is the recommended approach. No plaintext passwords stored.

**Minor Note:** Minimum of 6 characters is acceptable for a feeding scheme management tool, but consider NIST recommendation of 8+ for production.

---

## 2. AUTHORIZATION

### 2.1 Role Validation

| Checkpoint | Finding | File:Line | Status |
|------------|---------|-----------|--------|
| Web admin enforcement | `getCurrentUser()['role'] !== 'admin'` in `UserController.php` | Web controller | PRESENT |
| API `requireAuth()` | Validates token and returns user | `api/middleware/AuthMiddleware.php:30` | PRESENT |
| API `requireRole()` | Accepts string or array; checks `in_array()` | `api/middleware/AuthMiddleware.php:52` | PRESENT |
| User creation — admin | `$auth->requireRole(['admin'])` | `api/users/create.php:29` | PRESENT |
| Role whitelist | `['admin','volunteer','donor','staff']` forced | `api/users/create.php:49` | PRESENT |
| Default safe role | Falls back to `volunteer` | `api/users/create.php:49` | PRESENT |
| Audit trail | `logMessage()` on creation | `api/users/create.php:85` | PRESENT |

**Assessment:** Role enforcement is implemented in both web and API layers. User creation defaults to least-privileged role (`volunteer`).

### 2.2 Middleware Protection

| Endpoint | Protected | Role Check | File |
|----------|-----------|------------|------|
| `api/auth/login.php` | No (public) | N/A | Correct |
| `api/auth/validate.php` | No (public) | Accepts any token | Correct |
| `api/auth/refresh.php` | No (public) | Validates refresh token internally | Correct |
| `api/auth/logout.php` | Yes | AuthMiddleware | PRESENT |
| `api/dashboard/summary.php` | Yes | AuthMiddleware | PRESENT |
| `api/beneficiaries/list.php` | Yes | AuthMiddleware | PRESENT |
| `api/users/create.php` | Yes | AuthMiddleware + `requireRole('admin')` | PRESENT |
| `api/reports/generate.php` | Yes | AuthMiddleware (any role) | PRESENT |

---

## 3. SESSION SECURITY

### 3.1 Cookie Configuration

| Setting | Value | File:Line | Status |
|---------|-------|-----------|--------|
| `secure` | `isset($_SERVER['HTTPS'])` | `bootstrap.php:77` | CONDITIONAL |
| `httponly` | `true` | `bootstrap.php:78` | SECURE |
| `samesite` | `'Lax'` | `bootstrap.php:79` | SECURE |
| `lifetime` | `86400s` (24h) | `bootstrap.php:74` | ACCEPTABLE |
| Session ID in URL | POST forms only | Application | SECURE |

**Conditional Secure flag** means cookies transmit over HTTP in development environments. This should be forced to `true` in production.

### 3.2 Session Regeneration

| Mechanism | Implementation | File:Line | Status |
|-----------|----------------|-----------|--------|
| `session_regenerate_id(true)` | On successful login | `app/controllers/AuthController.php:77` | PRESENT |
| Old session destroyed | `true` parameter passed | `AuthController.php:77` | SECURE |

### 3.3 Session Timeout

| Mechanism | Implementation | File:Line | Status |
|-----------|----------------|-----------|--------|
| Inactivity timeout | 30 minutes (`1800s`) | `bootstrap.php:89` | PRESENT |
| `last_activity` update | Set on each request | `bootstrap.php:115` | PRESENT |
| Expiry handling | Destroys session, clears cookie | `bootstrap.php:91-100` | PRESENT |

### 3.4 Session Timeout API Bug (MEDIUM)

```php
// bootstrap.php:106 — CURRENT CODE
if (!$isApiRequest && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    header("Location: /views/login.php?timeout=1");
    exit();
}
```

**Bug:** An API request with an expired session receives an HTML redirect (`Location: /views/login.php?timeout=1`) instead of a JSON 401 response. This causes JSON parse errors in mobile clients.

**Severity:** MEDIUM — Mobile API calls break on session timeout.

---

## 4. TOKEN SECURITY (Mobile/API)

### 4.1 Token Generation

| Mechanism | Implementation | File:Line | Status |
|-----------|----------------|-----------|--------|
| CSPRNG | `random_bytes(32)` | `api/auth/login.php:83` | SECURE |
| Token hashing | `hash('sha256', $token)` before DB storage | `api/auth/login.php:84` | SECURE |
| Raw token never stored | Only `TokenHash` in DB | `api/auth/login.php:99-100` | SECURE |

### 4.2 Refresh Token Rotation

**Status: ROTATION IMPLEMENTED**

```php
// api/auth/refresh.php:78-107
// Old refresh token revoked:
$revokeStmt = $db->prepare("UPDATE AuthTokens SET RevokedAt = NOW() WHERE TokenID = :token_id");
$revokeStmt->execute([':token_id' => $tokenData['TokenID']]);

// New pair issued:
$newAccessToken = bin2hex(random_bytes(32));
$newRefreshToken = bin2hex(random_bytes(32));
// INSERT INTO AuthTokens ...
```

**Verified:** Rotation is correctly implemented. Old refresh token is revoked before new pair is issued.

### 4.3 Token Expiry

| Token Type | Lifetime | File:Line | Status |
|------------|----------|-----------|--------|
| Access token | 24 hours | `api/auth/login.php:85` | ACCEPTABLE |
| Refresh token | 30 days | `api/auth/login.php:90` | ACCEPTABLE |
| Expiry check in middleware | `ExpiresAt > NOW()` | `AuthMiddleware.php:111` | SECURE |
| Refresh expiry check | `RefreshExpiresAt > NOW()` | `api/auth/refresh.php:62` | SECURE |

### 4.4 Revocation

| Mechanism | Implementation | File:Line | Status |
|-----------|----------------|-----------|--------|
| `RevokedAt` timestamp column | Checked in both validate and refresh | `AuthMiddleware.php:110`, `refresh.php:61` | PRESENT |
| Single token revocation | `revokeToken()` method | `AuthMiddleware.php:148` | PRESENT |
| Bulk revocation | `revokeAllUserTokens()` method | `AuthMiddleware.php:173` | PRESENT |

### 4.5 Token Storage (Client)

| Mechanism | Location | Status | Risk |
|-----------|----------|--------|------|
| Tokens in `localStorage` | `mobile-shell/assets/api.js:41-57` | PRESENT | MEDIUM |

**Risk:** `localStorage` is accessible via Android USB debugging (`adb shell`), rooted devices, and malicious apps with WebView overlay. No Secure Enclave or Keychain integration.

---

## 5. DATABASE SECURITY

### 5.1 Prepared Statements

| File | Pattern | Status |
|------|---------|--------|
| `app/models/User.php` | All queries use `$this->conn->prepare()` + `bindParam` | SECURE |
| `api/auth/login.php` | `$db->prepare()` + `$stmt->execute([...])` | SECURE |
| `api/auth/refresh.php` | `$db->prepare()` for all queries | SECURE |
| `api/auth/validate.php` | `$db->prepare()` for SELECT and UPDATE | SECURE |
| `api/users/create.php` | `$db->prepare()` for INSERT | SECURE |
| `api/beneficiaries/list.php` | `bindValue` with prepared statement | SECURE |
| `api/reports/generate.php` | `$db->query()` — **raw queries** | UNSAFE_PATTERN |

### 5.2 Raw Query Pattern (MEDIUM)

```php
// api/reports/generate.php:45-103 — all report queries use $db->query()
$stmt = $db->query("SELECT ... FROM Beneficiaries ORDER BY LastName, FirstName");
```

**Finding:** Report queries use `$db->query()` with raw SQL strings. No user input currently enters these queries, but the pattern establishes an unsafe precedent. The report type is whitelist-validated (`in_array($type, [...])` at line 32), which limits current risk.

**Severity:** MEDIUM — Pattern risk. Future copy-paste errors could introduce SQL injection.

---

## 6. WEB SECURITY

### 6.1 CORS Headers

| Header | Value | File | Issue |
|--------|-------|------|-------|
| `Access-Control-Allow-Origin` | `*` | `bootstrap.php:57` + all API files | CRITICAL |
| `Access-Control-Allow-Credentials` | `true` | `bootstrap.php:60` + all API files | CRITICAL |
| `Access-Control-Allow-Methods` | `GET, POST, PUT, DELETE, OPTIONS` | `bootstrap.php:58` | OK |
| `Access-Control-Allow-Headers` | `Content-Type, Authorization, X-Requested-With` | `bootstrap.php:59` | OK |

**CRITICAL FINDING:** `Access-Control-Allow-Origin: *` combined with `Access-Control-Allow-Credentials: true` is a browser security violation. Browsers block this combination per the CORS spec, but the misconfiguration signals intent to allow any origin to make credentialed requests. This must be restricted to specific origins (e.g., `https://yourdomain.com`, `capacitor://localhost`).

### 6.2 Cookie Flags

| Flag | Value | File | Status |
|------|-------|------|--------|
| `HttpOnly` | `true` | `bootstrap.php:78` | SECURE |
| `SameSite` | `'Lax'` | `bootstrap.php:79` | SECURE |
| `Secure` | `isset($_SERVER['HTTPS'])` | `bootstrap.php:77` | CONDITIONAL |
| `Path` | `/` | `bootstrap.php:75` | OK |

### 6.3 Missing Security Headers

| Header | Needed | File | Status |
|--------|--------|------|--------|
| `X-Content-Type-Options: nosniff` | Yes | All responses | MISSING |
| `X-Frame-Options: DENY` | Yes | All responses | MISSING |
| `Strict-Transport-Security` | Yes (production) | All responses | MISSING |
| `Content-Security-Policy` | Web views | HTML pages | MISSING |

### 6.4 HTTPS Enforcement

| Mechanism | Status | Evidence |
|-----------|--------|----------|
| HTTP→HTTPS redirect | NOT PRESENT | No `$_SERVER['HTTPS']` enforcement in bootstrap |
| HSTS header | NOT PRESENT | Not set anywhere |

**Note:** In development (no TLS), this is expected. Must be enforced before production.

---

## 7. CAPACITOR/MOBILE SECURITY

### 7.1 Critical Transport Configuration

```json
// capacitor.config.json:5-10
"server": {
    "cleartext": true,      // Allows HTTP traffic
    "allowNavigation": ["*"]
}
```

### 7.2 Android Mixed Content

```json
// capacitor.config.json:12
"android": {
    "allowMixedContent": true   // Allows HTTP in HTTPS WebView
}
```

| Setting | Value | Severity | Impact |
|---------|-------|----------|--------|
| `cleartext: true` | Allows unencrypted HTTP | CRITICAL | Credentials transmitted over HTTP |
| `allowNavigation: ["*"]` | Any domain | HIGH | No origin restriction |
| `allowMixedContent: true` | HTTP in HTTPS context | MEDIUM | Downgrade possible |

**Combined Risk:** The mobile app can transmit login credentials and session tokens over unencrypted HTTP. Any network observer can intercept credentials.

---

## 8. CSRF PROTECTION

### 8.1 CSRF Token Infrastructure

| Component | Status | File |
|-----------|--------|------|
| `generateCSRFToken()` | Implemented | `bootstrap.php:313` |
| `verifyCSRFToken()` | Implemented | `bootstrap.php:329` |
| Token stored in session | `$_SESSION['csrf_token']` | `bootstrap.php:316` |
| `csrfTokenInput()` helper | Returns hidden field HTML | `bootstrap.php:340` |
| Actual enforcement on forms | NOT VERIFIED | Web views not reviewed |

**Finding:** CSRF infrastructure exists but has not been confirmed as applied to all state-changing forms. Token-based API authentication (Bearer) is inherently not vulnerable to CSRF.

---

## 9. ERROR HANDLING & INFORMATION LEAKAGE

| Context | Behavior | Risk | File |
|---------|----------|------|------|
| Login failure | Generic: "Invalid username or password" | None | `api/auth/login.php:77` |
| Registration error | `$e->getMessage()` | LOW | `api/users/create.php:96` |
| API error | Generic: "Internal server error" | None | All API files |
| Token invalid | Generic: "Token is invalid" | None | `api/auth/validate.php:69` |

---

## 10. VULNERABILITY SUMMARY

| ID | Finding | Location | Severity | Category |
|----|---------|----------|----------|----------|
| VULN-01 | `Access-Control-Allow-Origin: *` with `Allow-Credentials: true` | `bootstrap.php:57-60`, all API files | CRITICAL | CORS |
| VULN-02 | `capacitor.config.json` allows cleartext HTTP | `capacitor.config.json:6` | CRITICAL | Transport |
| VULN-03 | `allowMixedContent: true` in Android config | `capacitor.config.json:12` | MEDIUM | Transport |
| VULN-04 | Session timeout returns HTML redirect to API requests | `bootstrap.php:106` | MEDIUM | Session |
| VULN-05 | `findByUsername()` returns `PasswordHash` to callers | `app/models/User.php:61` | MEDIUM | Data Exposure |
| VULN-06 | `api/reports/generate.php` uses raw `$db->query()` | `api/reports/generate.php:45-103` | MEDIUM | Injection Pattern |
| VULN-07 | CSRF token generation exists but enforcement unverified | `app/views/*` | MEDIUM | CSRF |
| VULN-08 | `secure` cookie flag is conditional on `$_SERVER['HTTPS']` | `bootstrap.php:77` | LOW | Session |
| VULN-09 | No `X-Content-Type-Options`, `X-Frame-Options` headers | All responses | LOW | Headers |
| VULN-10 | No HSTS or HTTPS redirect enforcement | `bootstrap.php` | LOW (HIGH in prod) | Transport |

---

## 11. RISK RATING MATRIX

| Severity | Count | Findings |
|----------|-------|----------|
| CRITICAL | 2 | VULN-01, VULN-02 |
| MEDIUM | 5 | VULN-03, VULN-04, VULN-05, VULN-06, VULN-07 |
| LOW | 3 | VULN-08, VULN-09, VULN-10 |

---

## 12. REMEDIATION RECOMMENDATIONS

### CRITICAL — Immediate Action Required

**VULN-01: CORS Misconfiguration**
```php
// INSTEAD OF:
header('Access-Control-Allow-Origin: *');

// USE (bootstrap.php and all API files):
$allowedOrigins = [
    'https://yourdomain.com',
    'capacitor://localhost',
    'http://localhost:8000'  // dev only
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
}
```

**VULN-02: Capacitor HTTP Allowed**
```json
// capacitor.config.json
{
  "server": {
    "cleartext": false,  // BLOCK all HTTP
    "url": "https://your-api-server.com",  // Use HTTPS backend
    "allowNavigation": ["https://yourdomain.com"]
  },
  "android": {
    "allowMixedContent": false  // BLOCK mixed content
  }
}
```

### MEDIUM — Short-Term

**VULN-04: Session Timeout for API**
```php
// bootstrap.php:102-110 — Add API branch:
if ($isApiRequest || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit();
}
// Then the HTML redirect:
$redirectPath = '/views/login.php?timeout=1';
header("Location: {$redirectPath}");
exit();
```

**VULN-05: PasswordHash in Non-Auth Queries**
```php
// Remove PasswordHash from findByUsername() if used for non-auth lookups:
// app/models/User.php:61 — Change to:
$query = "SELECT UserID, Username, Email, Role, CreatedAt, Status 
          FROM " . $this->table . " 
          WHERE Username = :username AND Status = 'active' LIMIT 1";
```

**VULN-06: Raw Queries in Reports**
```php
// Convert all $db->query() calls to prepared statements:
$stmt = $db->prepare("SELECT ... FROM Beneficiaries ORDER BY LastName, FirstName");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**VULN-07: CSRF Enforcement**
```php
// In all web form POST handlers, add at the top:
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('CSRF token validation failed');
}
```

### LOW — Long-Term Hardening

**VULN-08: Force Secure Cookie**
```php
'secure' => true,  // Force HTTPS; no conditional
```

**VULN-09: Security Headers**
```php
// Add in bootstrap.php or .htaccess:
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
```

**VULN-10: HTTPS Enforcement**
```php
// In bootstrap.php, before any output:
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
```

---

## 13. POSITIVE FINDINGS

1. **bcrypt password hashing** — Industry standard via `password_hash(PASSWORD_BCRYPT)`.
2. **Token hashing** — Raw tokens never stored; only SHA-256 hashes in DB.
3. **Token rotation implemented** — Refresh.php correctly revokes old token before issuing new pair.
4. **HttpOnly + SameSite cookies** — Proper session cookie hardening.
5. **Session regeneration on login** — `session_regenerate_id(true)` prevents fixation.
6. **30-minute inactivity timeout** — Reasonable for feeding scheme operations.
7. **Generic error messages** — No username enumeration via login errors.
8. **Bearer tokens API-safe** — Not vulnerable to CSRF.
9. **Role enforcement** — Admin endpoints protected; default role is `volunteer`.
10. **Prepared statements dominant** — Most data access uses parameterized queries.

---

## 14. CONCLUSION

The FSMS project demonstrates strong foundational security practices: bcrypt password hashing, SHA-256 token hashing, prepared statements in most code paths, role-based middleware, and correctly implemented refresh token rotation. The two CRITICAL findings (open CORS with credentials, and Capacitor allowing cleartext HTTP) must be resolved before any production deployment. The remaining MEDIUM and LOW findings represent standard hardening steps that should be addressed in the next sprint.

**Overall Risk Level: MEDIUM (with 2 production-blocking CRITICAL items)**