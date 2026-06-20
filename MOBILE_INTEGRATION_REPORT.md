# Mobile Integration Report

**Project:** Tharimpepe Food Service Management System (FSMS)  
**Date:** 2025-06-18  
**Scope:** Mobile application integration review (API layer, auth flow, session management)

---

## 1. Executive Summary

The mobile application integrates with the FSMS backend exclusively through the REST API layer. There is **no direct database access** from the mobile client. All data operations flow through the web application's API controllers, ensuring the MySQL database remains the single source of truth.

| Component | Status | Notes |
|-----------|--------|-------|
| API Base Layer | PASS | Same-origin or configurable base URL |
| Login Endpoint | PASS | Token-based with hashed storage |
| Token Validation | PASS | Bearer token with expiry check |
| Token Refresh | PASS | Automatic 401 retry with rotation |
| Logout Endpoint | PASS | Server-side revocation + local cleanup |
| Session Persistence | PASS | localStorage with graceful error handling |
| Data Sync | PASS | REST-only, no duplicate storage |
| Architecture | PASS | No direct DB access, SSO maintained |

---

## 2. API Client Analysis (`mobile-shell/assets/api.js`)

### 2.1 HTTP Client
- Uses native `fetch` API with `async/await`
- Configurable base URL with same-origin default
- Automatic JSON request/response serialization
- Centralized error handling via `APIError` class

### 2.2 Token Persistence
```javascript
// Storage keys
localStorage: 'access_token', 'refresh_token', 'token_expires_at', 'user'
sessionStorage: 'user'
```

| Storage Key | Purpose | Lifespan |
|-------------|---------|----------|
| `access_token` | API authentication | 24 hours |
| `refresh_token` | Token renewal | 30 days |
| `token_expires_at` | Expiry timestamp | 24 hours |
| `user` | Cached user profile | Until logout |

### 2.3 Auto-Refresh Behavior
On HTTP 401 response:
1. Calls `/api/auth/refresh.php` with stored refresh token
2. If success: retries original request with new access token
3. If failure: calls `clearAuth()` and throws `SESSION_EXPIRED`

### 2.4 Error Codes
| Code | HTTP Status | Description |
|------|-------------|-------------|
| `NETWORK_ERROR` | 0 | Cannot reach server |
| `LOGIN_FAILED` | 401 | Invalid credentials |
| `SESSION_EXPIRED` | 401 | Token refresh failed |
| `PARSE_ERROR` | Variable | Invalid server response |
| `API_ERROR` | Variable | Generic API failure |

---

## 3. Authentication Endpoint Review

### 3.1 Login (`POST /api/auth/login.php`)
```json
Request:  { "username": "admin", "password": "secret" }
Response: { "success": true, "token": "...", "refresh_token": "...", "expires_at": "...", "user": {...} }
```

**Flow:**
1. Accepts JSON username/password
2. Calls `User::authenticate()` (DB-backed `password_verify`)
3. Fallback to `.demo_users.json` if DB unavailable (development mode)
4. Generates cryptographically secure tokens (`random_bytes(32)`)
5. Stores SHA-256 hashes in `AuthTokens` table
6. Returns raw tokens (never stored server-side)

**Token Lifetimes:**
- Access token: 24 hours
- Refresh token: 30 days

**Security Observations:**
- Token hashes stored, not plaintext
- Device info + IP logged for audit
- Consistent response structure

### 3.2 Validate (`GET /api/auth/validate.php`)
```json
Response: { "success": true, "message": "Token is valid", "user": {...} }
```

**Flow:**
1. Extracts Bearer token from `Authorization` header
2. Hashes token, queries `AuthTokens` JOIN `Users`
3. Checks: `RevokedAt IS NULL`, `ExpiresAt > NOW()`, `Status = 'active'`
4. Updates `LastUsedAt` timestamp
5. Returns user profile

**Security Observations:**
- Proper NULL checks on revoked/expired
- Validates user account is still `active`
- Uses parameterized queries

### 3.3 Refresh (`POST /api/auth/refresh.php`)
```json
Request:  { "refresh_token": "..." }
Response: { "success": true, "token": "...", "refresh_token": "...", "expires_at": "...", "user": {...} }
```

**Flow:**
1. Validates refresh token exists and is not expired/revoked
2. Revokes old token (`RevokedAt = NOW()`)
3. Generates new access + refresh token pair (token rotation)
4. Inserts new token records
5. Returns new credentials

**Security Observations:**
- Implements token rotation (prevents replay attacks)
- Validates user account `Status = 'active'` on refresh
- Old tokens immediately invalidated

### 3.4 Logout (`POST /api/auth/logout.php`)
```json
Response: { "success": true, "message": "Logged out successfully" }
```

**Flow:**
1. Extracts Bearer token from header
2. Calls `AuthMiddleware::revokeToken()` → sets `RevokedAt = NOW()`
3. Returns success even if token was already invalid (idempotent)

**Client Behavior:**
```javascript
// mobile-shell/assets/api.js:230-241
async logout() {
  try {
    const { accessToken } = getTokens();
    if (accessToken) {
      await request('POST', '/api/auth/logout.php', {}, true).catch(() => {});
    }
  } catch {
    // Silently ignore - we clear local state regardless
  }
  clearAuth();
}
```

**Security Observations:**
- Fire-and-forget revoke - local state cleared regardless of server response
- Handles server errors gracefully

---

## 4. Session Persistence Analysis

### 4.1 Client-Side Storage
| Mechanism | Data | Persistence |
|-----------|------|-------------|
| `localStorage` | Access token | Survives browser restart |
| `localStorage` | Refresh token | Survives browser restart |
| `localStorage` | Token expiry | Used for proactive validation |
| `localStorage` | User profile | Cached for quick access |
| `sessionStorage` | User profile | Cleared on browser close |

### 4.2 Server-Side Tracking
- All tokens stored in `AuthTokens` table with hashes
- `LastUsedAt` updated on validation
- `RevokedAt` set on logout/refresh (audit trail)
- Device info and IP address logged

### 4.3 Persistence Validation
- Graceful degradation if `localStorage` unavailable (try/catch blocks)
- `tryRefreshToken()` retries original request after refresh
- `SESSION_EXPIRED` forces re-login when refresh fails

---

## 5. Data Synchronization

### 5.1 Architecture
```
Mobile Application
      ↓ (REST API calls)
Web Application (PHP)
      ↓ (PDO queries)
MySQL Database
      ↑ (JSON responses)
Web Application (PHP)
      ↑ (JSON responses)
Mobile Application
```

### 5.2 Observed Data Flows
| Data Type | API Endpoint | Direction | Sync Method |
|-----------|--------------|-----------|-------------|
| Dashboard summary | `/api/dashboard/summary.php` | Read | Real-time fetch |
| Beneficiaries | `/api/beneficiaries/list.php` | Read | Real-time fetch |
| Attendance today | `/api/attendance/today.php` | Read | Real-time fetch |
| Attendance history | `/api/attendance/history.php` | Read | Real-time fetch |
| Stock list | `/api/stock/list.php` | Read | Real-time fetch |
| User creation | `/api/users/create.php` | Write | Real-time POST |

### 5.3 No Offline/Local Queue
- No IndexedDB usage detected
- No Service Worker sync queue
- No background sync API
- All requests require live server connection
- Network errors throw `NETWORK_ERROR` immediately

### 5.4 Data Freshness
- Each page load makes fresh API calls
- No stale-while-revalidate pattern
- No local caching beyond user token/profile

---

## 6. Compliance Checks

### 6.1 No Direct Database Access
- Mobile app contains **zero** database connection code
- All data access through HTTPS REST endpoints
- No database credentials in mobile bundle or config
- Only accessible ports: HTTPS (443) or WAMP (varies)

### 6.2 No Duplicate Storage Architecture
- Single storage layer: MySQL `AuthTokens` table
- Mobile stores only transient auth state (tokens, user)
- No local database (SQLite, IndexedDB, etc.)
- No Redis or secondary cache layer for mobile
- Server response is authoritative

### 6.3 Single Source of Truth
- MySQL database is the authority
- Web app reads/writes through PDO models
- Mobile is a thin client - zero business logic
- All validation server-side (form, business rules, auth)

---

## 7. Identified Findings

### 7.1 Strengths
1. Consistent API response structure (`success`, `message`, `data`)
2. Token rotation on refresh prevents replay attacks
3. Graceful degradation when DB is unavailable (demo mode)
4. Comprehensive error handling with typed error codes
5. No direct DB access from mobile - architecture is clean

### 7.2 Considerations
1. **CORS Headers**: `Access-Control-Allow-Origin: *` allows any origin - acceptable for public API but should be restricted in production to actual app domains
2. **Network Failures**: Logout does not block on server revocation - tokens could theoretically remain valid briefly if network drops at logout
3. **Token Storage**: Uses `localStorage` which is accessible to any JS on the page - XSS vulnerability could extract tokens
4. **No Offline Mode**: Application requires constant connectivity - no cached data fallback
5. **Refresh Token Rotation Failures**: If client loses network during refresh, old token is revoked and new tokens are lost - user must re-login

### 7.3 Non-Critical Observations
1. `tryRefreshToken()` only attempts once - no exponential backoff
2. No request deduplication or cancellation tokens
3. No request queue for offline recovery
4. `clearAuth()` does not fail if storage is corrupted (silently handles)

---

## 8. Recommendations

| Priority | Recommendation | Rationale |
|----------|----------------|-----------|
| Medium | Restrict CORS `Access-Control-Allow-Origin` to specific domains in production | Prevents CSRF from unauthorized origins |
| Low | Add token expiry pre-check with `token_expires_at` | Refresh proactively before 401 |
| Low | Consider adding `AbortController` support for request cancellation | Prevents race conditions on component unmount |

---

## 9. Conclusion

The mobile application integration is architecturally sound. The REST API layer provides a clean boundary between the mobile client and the MySQL database, maintaining a single source of truth. Authentication, session persistence, and token refresh are implemented following industry best practices.

**Overall Assessment:** COMPLIANT

The integration meets all specified requirements for API connectivity, session persistence, token refresh, authentication flow, logout flow, and data synchronization without violating the core constraints of no direct database access, no duplicate storage, and maintaining a single source of truth.