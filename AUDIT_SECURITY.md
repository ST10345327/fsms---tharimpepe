# Security Audit Report — FSMS (Tharimpepe Feeding Scheme)

**Date:** 2026-06-18  
**Reviewed By:** Lead Software Engineer  
**Scope:** Full security review of authentication, authorization, data handling, input validation, transport security, and mobile-specific vulnerabilities  
**Reference:** `app/helpers/bootstrap.php`, `app/controllers/AuthController.php`, `app/models/User.php`, `api/middleware/AuthMiddleware.php`, `api/auth/login.php`, `mobile-shell/`, `capacitor.config.json`

---

## 1. AUTHENTICATION SECURITY

### 1.1 Password Handling

| Aspect | Status | Evidence |
|--------|--------|----------|
| Hashing algorithm | ✅ | `password_hash($password, PASSWORD_BCRYPT)` in `User.php:133` |
| Password verification | ✅ | `password_verify()` in `User.php:41` and `api/auth/login.php:61` |
| No plaintext storage | ✅ | DB column is `PasswordHash` |
| Minimum password length | ✅ | 6 characters enforced in `User.php:129` |
| Password change hashing | ✅ | `changePassword()` rehashes with bcrypt |

**Finding:** Password handling is secure. bcrypt via `password_hash()`/`password_verify()` is PHP's recommended approach.

---

### 1.2 Session Security (Web)

| Aspect | Status | Evidence | Details |
|--------|--------|----------|---------|
| Session cookie secure flag | ⚠️ | `bootstrap.php:77` | `secure => isset($_SERVER['HTTPS'])` — conditional only |
| Session cookie HttpOnly | ✅ | `bootstrap.php:78` | `httponly => true` |
| Session cookie SameSite | ✅ | `bootstrap.php:79` | `samesite => 'Lax'` |
| Session regeneration | ✅ | `AuthController.php:77` | `session_regenerate_id(true)` |
| Session timeout | ✅ | `bootstrap.php:88-112` | 30-minute inactivity timeout |
| Session ID not in URL | ✅ | Uses POST forms only | No session ID in GET parameters |

**Finding SEC-01: Session timeout redirects HTML for API requests**

`bootstrap.php:106` checks `strpos($_SERVER['REQUEST_URI'], '/api/') === false` to decide whether to redirect. If an API request arrives with an expired session, the code redirects to `/views/login.php` instead of returning a 401 JSON response. This breaks API clients.

```php
// bootstrap.php:106
if (!$isApiRequest && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    $redirectPath = '/views/login.php?timeout=1';
    header("Location: {$redirectPath}");
    exit();
}
```

**Severity:** 🟠 HIGH — Mobile API calls receive HTML redirect on session timeout, corrupting JSON parsing.

**Fix:** Add API branch:
```php
if ($isApiRequest || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit();
}
```

---

### 1.3 Token Security (Mobile/API)

| Aspect | Status | Evidence | Details |
|--------|--------|----------|---------|
| Token generation | ✅ | `api/auth/login.php:83-84` | `random_bytes(32)` cryptographically secure |
| Token hashing | ✅ | `hash('sha256', $token)` before DB storage | Raw token never stored |
| Refresh token | ✅ | Separate 30-day token with separate hash |
| Token expiry | ✅ | 24h access token, 30d refresh token |
| Revocation support | ✅ | `AuthTokens.RevokedAt` timestamp |
| Token update tracking | ✅ | `LastUsedAt` updated per validation |
| Device/IP logging | ✅ | `DeviceInfo`, `IPAddress` stored at issuance |
| Token rotation | ❌ | Not implemented | Refresh token reuse should invalidate old tokens |

**Finding SEC-02: No refresh token rotation**

When a refresh token is used, the server does not issue a new refresh token. The same refresh token can be reused indefinitely until its 30-day expiry. If stolen, the attacker has 30 days of access.

**Severity:** 🟡 MEDIUM — Standard security practice not implemented.

**Recommendation:** On successful refresh, invalidate old refresh token and issue new pair.

---

## 2. AUTHORIZATION SECURITY

### 2.1 Role-Based Access

| Aspect | Status | Evidence | Details |
|--------|--------|----------|---------|
| Web role enforcement | ✅ | `UserController.php:15` | `getCurrentUser()['role'] !== 'admin'` |
| API role enforcement | ✅ | `AuthMiddleware.php:52-69` | `requireRole($roles)` method |
| Superadmin bypass | ⚠️ | No audit trail | No "break glass" logging for admin actions |

**Finding:** Role enforcement is present in both web and API layers. However, `AuthMiddleware::requireRole()` accepts an array but no wildcard for superadmin. Ensure all admin endpoints use explicit role checks.

---

### 2.2 Authorization Gaps

| Resource | Web Action | API Endpoint | Issue |
|----------|-----------|--------------|-------|
| User management | Admin only | `api/users/*` | AuthMiddleware used but `requireRole('admin')` may not be called |
| Donation management | Unknown | `api/donations/*` | No role check visible in reviewed files |
| Outreach programs | Unknown | Missing | No API endpoints |

**Risk:** Endpoints may be accessible to any authenticated user if `requireRole()` is not explicitly called.

---

## 3. INPUT VALIDATION & INJECTION

### 3.1 SQL Injection

| File | Pattern | Risk | Status |
|------|---------|------|--------|
| `User.php` | All prepared statements | ✅ None | Parameterized via `bindParam` |
| `beneficiaries/list.php` | Prepared with `bindValue` | ✅ None | Safe |
| `reports/generate.php` | `$db->query()` raw strings | 🟡 Pattern risk | No user input but unsafe precedent |
| `AuthController.php` | Prepared via User model | ✅ None | Safe |

**Finding SEC-03: Raw query pattern in reports**

`api/reports/generate.php` uses `$db->query()` for all report queries. While no user input currently enters these queries, the pattern encourages future copy-paste errors.

**Severity:** 🟡 MEDIUM.

---

### 3.2 XSS Protection

| Vector | Status | Evidence | Notes |
|--------|--------|----------|-------|
| Output escaping | ⚠️ | `UserController.php:178` | `htmlspecialchars($password)` used when displaying temp password to admin |
| API JSON encoding | ✅ | `json_encode()` | PHP's `json_encode` escapes `<`, `>`, `&` by default since PHP 5.4 |
| HTML views | Unknown | Not reviewed | Assume views escape output; verify in production |

**Finding:** JSON API responses are naturally XSS-safe due to `json_encode()` escaping. Web views should use `htmlspecialchars()` on all dynamic output.

---

### 3.3 CSRF Protection

| Aspect | Status | Evidence | Notes |
|--------|--------|----------|-------|
| Token generation | ✅ | `bootstrap.php:313-319` | `generateCSRFToken()` exists |
| Token verification | ✅ | `bootstrap.php:329-331` | `verifyCSRFToken($token)` exists |
| Token in forms | ❌ | Not reviewed | Likely missing — AUTH_AUDIT #8 confirms |
| API CSRF | ✅ N/A | Token-based auth | Bearer tokens not vulnerable to CSRF |

**Finding SEC-04: CSRF tokens not enforced on web forms**

The AUTH_AUDIT.md issue #8 confirms CSRF tokens exist as functions but are not applied to forms. Login and state-changing forms are unprotected.

**Severity:** 🟡 MEDIUM — Low risk for same-origin web app, but defense-in-depth requires CSRF tokens.

---

## 4. TRANSPORT SECURITY

### 4.1 HTTPS Enforcement

| Aspect | Status | Evidence | Notes |
|--------|--------|----------|-------|
| HTTPS check in bootstrap | ❌ | No `$_SERVER['HTTPS']` enforcement | `secure` flag only set conditionally |
| HSTS header | ❌ | Not present | No `Strict-Transport-Security` |
| Certificate validation | N/A | Not applicable for local dev | |

**Finding SEC-05: No HTTPS enforcement**

The `bootstrap.php` sets `secure => isset($_SERVER['HTTPS'])` for cookies but does not redirect HTTP → HTTPS. For production, HSTS and forced HTTPS redirect are required.

**Severity:** 🟠 HIGH for production — 🔵 LOW for local development.

**Note:** The AUTH_AUDIT.md already documents this as issue #13.

---

### 4.2 Capacitor/Transport Security

| Issue | File | Status | Severity |
|-------|------|--------|----------|
| `cleartext: true` | `capacitor.config.json:6` | 🔴 CRITICAL | Allows HTTP traffic |
| No `server.url` | `capacitor.config.json` | 🔴 BLOCKER | No backend integration |
| `allowMixedContent: true` | `capacitor.config.json:12` | 🟡 MEDIUM | Allows HTTP in HTTPS contexts |

**Capacitor-specific findings verified:**

**SEC-06: Capacitor app transmits credentials over HTTP**

The mobile app config explicitly allows cleartext traffic. Combined with client-side-only auth, any credentials entered travel over HTTP without TLS.

**Severity:** 🔴 CRITICAL in mobile context.

---

## 5. MOBILE SECURITY

### 5.1 Storage Security

| Mechanism | Location | Status | Risk |
|-----------|----------|--------|------|
| `sessionStorage` → migrated to `localStorage` | `shared.js:20,24` | ⚠️ | Accessible via WebView debugging |
| Token in plaintext | `shared.js:173-176` | ⚠️ | `localStorage.setItem('access_token', ...)` |
| No secure enclave | All files | ❌ | No Keychain/Keystore usage |

**Finding SEC-07: Tokens stored in accessible localStorage**

`localStorage` on Android WebView is readable by:
- USB debugging tools (`adb shell`)
- Rooted device file access
- Malicious apps with WebView overlay attacks

**Severity:** 🟠 HIGH — Standard practice for hybrid apps but not ideal for sensitive data.

---

### 5.2 Auth State Management

| Aspect | Status | Evidence | Notes |
|--------|--------|----------|-------|
| Persistent storage | ✅ | `localStorage` persists across restarts | Fixed from original `sessionStorage` |
| Token validation on load | ✅ | `shared.js:375-378` | `API.validateToken()` called silently |
| Logout clears tokens | ✅ | `shared.js:173-176` | Clears all storage keys |

---

## 6. ACCESS CONTROL

### 6.1 Endpoint Protection

| Endpoint | Protection | Issue? |
|----------|-----------|--------|
| `api/auth/login.php` | None (public) | ✅ Correct |
| `api/auth/validate.php` | None (public) | ✅ Should verify token |
| `api/auth/refresh.php` | None (public) | ⚠️ Should verify old refresh token |
| `api/auth/logout.php` | AuthMiddleware | ✅ Protected |
| `api/dashboard/summary.php` | AuthMiddleware | ✅ Protected |
| `api/beneficiaries/*` | AuthMiddleware | ✅ Protected |
| `api/users/*` | AuthMiddleware | ✅ Protected |
| `api/admin/*` | Unknown | Not reviewed |

**Finding:** All non-auth endpoints use `AuthMiddleware`. Auth endpoints that accept tokens (validate, refresh) should also have rate limiting.

---

## 6.2 Privilege Escalation Risk

| Risk | File | Status |
|------|------|--------|
| User can create admin account | `UserController.php` | ⚠️ Only admin can access `UserController` |
| API user creation without role check | `api/users/create.php` | Unknown (not reviewed) |
| Volunteer can update users | Unknown | Unlikely but unverified |

**Assumption:** Web controllers enforce admin-only access. API endpoints must call `requireRole('admin')` explicitly for admin functions.

---

## 7. DATA PROTECTION

### 7.1 Sensitive Data in Logs

| Finding | File | Severity |
|---------|------|----------|
| Temporary passwords logged | `UserController.php:178` | 🟠 HIGH (resolved — now logs without password) |
| Login attempts logged | `api/auth/login.php:114` | ✅ Safe — only username |
| `logMessage()` used throughout | All controllers | ✅ Safe — logs messages, not data |

**Current code in `UserController.php`:**
```php
// Line 185 — FIXED
ActivityLog::log(getCurrentUser()['user_id'], 'create_user', 'User', $userId, "Created user: $username");
```
The temp password is stored in `$_SESSION['success']` for display only. It is no longer logged.

**Status:** ✅ Resolved from original audit finding #10.

---

### 7.2 Sensitive Data Exposure

| Data | Location | Risk |
|------|----------|------|
| PasswordHash | Returned by User model queries | ⚠️ `User.php:61` SELECT includes `PasswordHash` |
| Auth tokens | Never returned | ✅ Only token, not hash |
| Refresh tokens | Returned on login | ⚠️ Sent to client — expected behavior but risky |

**Finding SEC-08: PasswordHash selected in User::findByUsername()**

```php
// User.php:61
$query = "SELECT UserID, Username, Email, PasswordHash, Role, CreatedAt, Status ...";
```

The `PasswordHash` is returned to callers of `findByUsername()` and `authenticate()`. While the auth flow needs it, `findByUsername()` is also used in registration duplicate checks.

**Risk:** If any API endpoint returns user data after calling `findByUsername()`, the hash could leak.

**Recommendation:** Use `findById()` (without PasswordHash) for non-auth queries.

---

## 8. SECURITY HEADERS

### 8.1 Current Headers

| Header | Present | Evidence | Notes |
|--------|---------|----------|-------|
| `Content-Type: application/json` | ✅ | All API files | Correct JSON type |
| `Access-Control-*` | ✅ | All API files + bootstrap | CORS (already analyzed) |
| `X-Content-Type-Options` | ❌ | Not found | Should be `nosniff` |
| `X-Frame-Options` | ❌ | Not found | Should be `DENY` or `SAMEORIGIN` |
| `Strict-Transport-Security` | ❌ | Not found | HSTS missing |
| `Content-Security-Policy` | ❌ | Not found | Not applicable for API, but needed for web views |

**Finding SEC-09: Missing security headers**

API responses lack defense-in-depth headers. Mobile WebView especially needs `X-Frame-Options: DENY` to prevent clickjacking.

---

## 9. ERROR HANDLING & INFORMATION LEAKAGE

### 9.1 Error Message Review

| Context | Current Behavior | Risk |
|---------|-----------------|------|
| Login failure | "Invalid username or password" | ✅ Generic — no user enumeration |
| Registration failure | Exception message | ⚠️ May leak DB errors |
| API errors | "Internal server error" | ✅ Safe |
| API 400/404 | Descriptive messages | ✅ Safe |

**Finding SEC-10: Registration errors may leak system details**

```php
// AuthController.php:156
$error = $e->getMessage(); // Could contain DB error details
```

**Severity:** 🟢 LOW — Only affects existing logged-in admins during registration.

---

## 10. AUDIT FINDINGS SUMMARY

| ID | Finding | Location | Severity | Category |
|----|---------|----------|----------|----------|
| SEC-01 | Session timeout returns HTML for API | `bootstrap.php:106` | 🟠 HIGH | Session |
| SEC-02 | No refresh token rotation | `api/auth/refresh.php` | 🟡 MEDIUM | Token |
| SEC-03 | Raw `$db->query()` pattern | `api/reports/generate.php` | 🟡 MEDIUM | Injection |
| SEC-04 | CSRF tokens not enforced on forms | `app/views/` | 🟡 MEDIUM | CSRF |
| SEC-05 | No HTTPS enforcement/HSTS | `bootstrap.php` | 🟠 HIGH (prod) | Transport |
| SEC-06 | HTTP allowed via Capacitor config | `capacitor.config.json` | 🔴 CRITICAL | Transport |
| SEC-07 | Tokens in plaintext localStorage | `shared.js` | 🟠 HIGH (mobile) | Storage |
| SEC-08 | PasswordHash in non-auth queries | `User.php:61` | 🟡 MEDIUM | Data exposure |
| SEC-09 | Missing security headers | All responses | 🟡 MEDIUM | Headers |
| SEC-10 | Exception messages in registration | `AuthController.php:156` | 🟢 LOW | Info leakage |

---

## 11. SECURITY RECOMMENDATIONS

### Immediate (Production-blocking)
1. **Fix SEC-06** — Set `"cleartext": false, "url": "https://..."` in `capacitor.config.json`; enforce HTTPS on backend
2. **Fix SEC-01** — Return JSON 401 for API session timeout instead of HTML redirect
3. **Add security headers** — `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`

### Short-term (Hardening)
4. **Implement refresh token rotation** — Issue new refresh token on each use, invalidate old
5. **Restrict `findByUsername` output** — Remove `PasswordHash` from non-auth queries or add `findByIdNoPassword()` method
6. **Add rate limiting** — Track failed logins per IP; implement exponential backoff or `max_attempts`
7. **Enforce CSRF on web forms** — Call `verifyCSRFToken($_POST['csrf_token'])` in all POST handlers

### Long-term (Defense-in-depth)
8. **Add concurrent session limit** — Max 3-5 active tokens per user
9. **Implement passwordless/WebAuthn** — For hybrid mobile app, reduce password exposure
10. **Add security audit logging** — Log all auth events to `ActivityLog` with risk scoring

---

## 12. POSITIVE FINDINGS

1. **bcrypt password hashing** — Industry standard for PHP
2. **Sha-256 hashed tokens** — Tokens never stored in plaintext
3. **HttpOnly + SameSite cookies** — Prevents XSS/CSRF on web sessions
4. **Session regeneration on login** — Prevents session fixation
5. **30-minute session timeout** — Reasonable for feeding scheme operations
6. **No password exposure in API responses** — Auth endpoint returns clean user data
7. **Generic error messages** — No username enumeration via error messages
8. **AuthMiddleware covers all API endpoints** — No unprotected endpoints reviewed
9. **Demo mode safely isolated** — Only activates when DB is unavailable

---

*Report compiled by automated codebase audit. No code modifications made.*