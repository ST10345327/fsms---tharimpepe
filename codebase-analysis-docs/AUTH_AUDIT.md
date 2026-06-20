# 🔐 Full Authentication System Audit — FSMS (Tharimpepe)

**Date:** 2026-06-18 (Updated with Capacitor re-analysis)  
**Scope:** Complete login flow from UI → API → Backend → Database, with Capacitor-specific analysis  
**Files Analyzed:** 15 key files across web, mobile-shell, Capacitor config, backend, and database layers

---

## 🧠 CRITICAL ARCHITECTURE REALIZATION: Capacitor ≠ Native Mobile App

This is **NOT** a native Android app. It is a **Capacitor hybrid application** wrapping a WebView around the mobile-shell directory.

```
┌──────────────────────────────────────────────────────────┐
│                   CAPACITOR APP                           │
│                                                           │
│  ┌─────────────────────────────────────────────────────┐  │
│  │  Android/iOS WebView                                 │  │
│  │                                                      │  │
│  │  ┌─────────────────────────────────────────────────┐ │  │
│  │  │  mobile-shell/ (served from local files via      │ │  │
│  │  │  capacitor.config.json → webDir)                 │ │  │
│  │  │                                                 │ │  │
│  │  │  ┌──────────┐  ┌──────────────┐  ┌──────────┐ │ │  │
│  │  │  │index.html│─>│dashboard.html│─>│reports.. │ │ │  │
│  │  │  │(login)   │  │(protected)   │  │          │ │ │  │
│  │  │  └──────────┘  └──────────────┘  └──────────┘ │ │  │
│  │  │     │                                              │ │
│  │  │     ▼ (sessionStorage only)                        │ │
│  │  │  ┌─────────────────────┐                          │ │
│  │  │  │ NO API CALL TO PHP  │  ← CRITICAL              │ │
│  │  │  │ NO BACKEND CONTACT  │                          │ │
│  │  │  │ CLIENT-SIDE AUTH    │                          │ │
│  │  │  └─────────────────────┘                          │ │
│  │  └─────────────────────────────────────────────────┘ │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                           │
│  capacitor.config.json defines webDir, NOT server.url     │
│  → App loads local static files, not a PHP backend        │
│  → No CORS, no cookies, no server sessions in mobile      │
└──────────────────────────────────────────────────────────┘
```

---

## 📊 COMPLETE AUTH FLOW DIAGRAM (Two Separate Systems)

```
                    SYSTEM 1: WEB APP (PHP Server-Side)
                    ──────────────────────────────────────
                    Browser → PHP Sessions → MySQL DB
                    
                    Browser                PHP Server                DB
                    ┌────────┐  POST /controllers/  ┌──────────────┐
                    │login.php│─────AuthController──>│  bootstrap   │
                    │  Form   │   ?action=login     │  - session   │
                    └────────┘                      │  - DB conn   │
                         │                          └──────┬───────┘
                         │                                 │
                         │                          ┌──────▼───────┐
                         │                          │ AuthController│
                         │                          │ 1. Demo Fast  │──┐
                         │                          │    Path      │  │
                         │                          │  (plaintext)  │  │
                         │                          │              │<─┘
                         │                          │ 2. DB Auth   │──┐
                         │                          │    Path      │  │
                         │                          │  (password_   │  │
                         │                          │   verify)     │<─┘
                         │                          │              │  MySQL
                         │                          │ 3. Demo      │──┐
                         │                          │    Fallback   │  │
                         │                          └──────┬───────┘  │
                         │                                 │          │
                         │                          ┌──────▼──────┐  │
                         │<─────────────302─────────│ Session     │  │
                         │           /index.php     │ Created     │  │
                         │                          │ (Cookie,    │  │
                         │                          │  httponly)  │  │
                         └──────────────────────────┴─────────────┘  │


                    SYSTEM 2: MOBILE APP (Client-Side Only, NO BACKEND)
                    ─────────────────────────────────────────────────
                    Capacitor WebView → sessionStorage (volatile)
                    
                    mobile-shell/index.html
                    ┌──────────────────────────────────────────────┐
                    │ 1. User submits username + password           │
                    │ 2. JS checks hardcoded demoUsers object       │
                    │ 3. If match → sessionStorage.setItem('user') │
                    │ 4. Redirect to dashboard.html                 │
                    │ 5. No server call, no API, no DB              │
                    │ 6. sessionStorage is VOLATILE in WebView      │
                    └──────────────────────────────────────────────┘
                    
                    ⚠ THESE TWO SYSTEMS DO NOT COMMUNICATE ⚠
                    Mobile auth state is ENTIRELY CLIENT-SIDE
```

---

## 🚨 CAPACITOR-SPECIFIC ISSUES (NEW - Priority Order)

### 🚨 CAP-01: Mobile App Has Zero Backend Integration (🔴 BLOCKER)

**Files:** `capacitor.config.json`, `mobile-shell/index.html`, `mobile-shell/assets/shared.js`  
**Severity:** 🔴 BLOCKER (Login cannot function in Capacitor)

```json
// capacitor.config.json
{
  "appId": "com.tharimpepe.fsms",
  "appName": "Tharimpepe FSMS",
  "webDir": "mobile-shell",     // ← Serves LOCAL files only
  "server": {
    "cleartext": true
  }
}
```

**Problem:**  
The Capacitor app is configured with `webDir: "mobile-shell"` but **no `server.url`** is defined. This means:
- The app loads its pages as local `file://` or `http://localhost` resources
- There is NO PHP server, NO database, NO backend API
- The login page hardcodes demo users and authenticates purely on the client
- All 6 internal pages (`dashboard.html`, `beneficiaries.html`, etc.) use `sessionStorage` for auth state
- The mobile app is a **static HTML app** that cannot communicate with the PHP backend

**Root Cause:**  
The architect intended `mobile-shell/` to be a standalone prototype, but the Capacitor config does not bridge it to the PHP backend. There is no API endpoint for the mobile app to call.

**Fix (Option A - Live API Proxy):** Configure Capacitor to proxy to the PHP backend:
```json
{
  "server": {
    "url": "http://192.168.1.100:8000",  // Your dev server IP
    "cleartext": true,
    "allowNavigation": ["192.168.1.100"]
  }
}
```

**Fix (Option B - Proper API Layer):** Build a REST API in `api/` directory and update mobile-shell to call it:
```php
// api/auth/login.php
<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$data = json_decode(file_get_contents('php://input'), true);
$userModel = new User($db);
$user = $userModel->authenticate($data['username'], $data['password']);

if ($user) {
    $token = bin2hex(random_bytes(32));
    // Store token in DB with expiry
    echo json_encode(['success' => true, 'token' => $token, 'user' => $user]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
```

---

### 🚨 CAP-02: sessionStorage Wiped on Android WebView Restart (🔴 BLOCKER)

**Files:** `mobile-shell/index.html:66`, `mobile-shell/assets/shared.js:5-7,118`  
**Severity:** 🔴 BLOCKER (User always "forgets login" after app restart)

```javascript
// mobile-shell/index.html line 66
sessionStorage.setItem('user', JSON.stringify({ username, role: demoUsers[username].role }));

// mobile-shell/assets/shared.js line 118
function logout() {
  sessionStorage.removeItem('user');
  window.location.href = './index.html';
}
```

**Problem:**
- Android WebView's `sessionStorage` is tied to the WebView's browsing context
- When the app is backgrounded and killed by the OS (which happens frequently on Android), the WebView is destroyed and `sessionStorage` is **completely wiped**
- iOS WKWebView similarly clears `sessionStorage` on app termination
- This means: every time the user closes and reopens the app, they are logged out
- `localStorage` would persist slightly better but is NOT used anywhere

**Root Cause:**  
`sessionStorage` is designed for single-browser-tab sessions. Capacitor WebViews treat the entire app as one "tab session" that gets destroyed on app kill. The code assumed browser-like persistence.

**Fix:**  
Use `@capacitor/preferences` plugin (formerly `@capacitor/storage`) for persistent storage:

```bash
npm install @capacitor/preferences
npx cap sync
```

```javascript
// In Capacitor-compatible environment
import { Preferences } from '@capacitor/preferences';

// Store login state persistently
async function setLoggedInUser(user) {
  await Preferences.set({
    key: 'auth_user',
    value: JSON.stringify(user)
  });
}

// Retrieve on app start
async function getLoggedInUser() {
  const { value } = await Preferences.get({ key: 'auth_user' });
  return value ? JSON.parse(value) : null;
}

// Clear on logout
async function logout() {
  await Preferences.remove({ key: 'auth_user' });
  window.location.href = './index.html';
}
```

**Fallback Fix (if no Capacitor plugins):** Use `localStorage` instead of `sessionStorage`:
```javascript
// Minimum viable fix - localStorage persists across WebView restarts
localStorage.setItem('user', JSON.stringify({...}));
function getCurrentUser() {
  const data = localStorage.getItem('user');
  return data ? JSON.parse(data) : null;
}
```

---

### 🚨 CAP-03: No Secure Storage for Auth Tokens (🔴 CRITICAL)

**Files:** `mobile-shell/index.html:52-72`, `capacitor.config.json`  
**Severity:** 🔴 CRITICAL (Any app with file access can read credentials)

```javascript
// Plaintext password check in client-side JS
const demoUsers = {
    admin: { pass: 'admin123', role: 'admin' },
    volunteer: { pass: 'vol123', role: 'volunteer' },
    // ...
};
```

**Problem:**  
- Demo credentials are hardcoded in **plaintext** in the HTML source
- `sessionStorage` on Android can be read by:
  - Any app with `WebView` debugging enabled
  - USB debugging tools
  - Malicious apps using `WebView` overlay attacks
- No use of platform-native secure storage (Android Keystore, iOS Keychain)
- No biometric or device-level auth

**Root Cause:**  
The mobile prototype was built as a static HTML demo without any security considerations. Capacitor provides `@capacitor/preferences` but it's not installed or used.

**Fix:**  
1. Remove hardcoded credentials from production builds
2. Use `@capacitor/preferences` with encryption for token storage
3. For higher security, implement token-based auth with short-lived access tokens + refresh tokens

```javascript
import { Preferences } from '@capacitor/preferences';

// Store only an opaque token, never the password
async function storeAuthToken(token) {
  await Preferences.set({
    key: 'auth_token',
    value: token
  });
  // On iOS, consider using Keychain via a native plugin
}
```

---

### 🚨 CAP-04: Cookie-Based PHP Sessions Won't Work in Capacitor WebView (🔴 CRITICAL)

**Files:** `app/helpers/SessionHandler.php:1-22`, `app/controllers/AuthController.php:46-54`  
**Severity:** 🔴 CRITICAL (Server session auth is incompatible with mobile)

```php
// SessionHandler.php
$sessionRoot = rtrim(sys_get_temp_dir(), '\\/') . '/fsms-sessions';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// AuthController.php
$_SESSION["user_id"] = $user["UserID"];
$_SESSION["username"] = $user["Username"];
header("Location: /index.php");
```

**Problem:**
- The PHP backend uses **server-side sessions** identified by a session cookie (`PHPSESSID`)
- Capacitor WebView loads pages from `webDir` (local files), NOT from the PHP server
- Even if `server.url` were configured, Android WebView has known issues with:
  - Cookies not being sent on first request
  - Cookie persistence across navigation
  - Third-party cookie blocking (if API is on different origin)
- `session_regenerate_id()` is never called (Issue #5 from original audit still applies)
- No `HttpOnly`, `SameSite`, or `Secure` flags on session cookies

**Root Cause:**  
The backend was designed for browser-based access where the PHP server serves pages directly. Capacitor separates the frontend (local files) from the backend (PHP server), breaking the cookie-based session model.

**Fix:**  
Implement token-based authentication for mobile:

```php
// api/auth/login.php - Return JWT or opaque token
require_once __DIR__ . '/../app/helpers/bootstrap.php';

function generateAccessToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store in database
    $db = getDBConnection();
    $stmt = $db->prepare(
        "INSERT INTO AuthTokens (UserID, Token, ExpiresAt, CreatedAt) 
         VALUES (:user_id, :token, :expires, NOW())"
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => hash('sha256', $token),  // Store hash, not raw token
        ':expires' => $expiresAt
    ]);
    
    return $token;
}
```

```javascript
// mobile-shell/assets/shared.js - Token-based auth
async function loginWithApi(username, password) {
    const response = await fetch('https://your-server.com/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    
    if (response.ok) {
        const data = await response.json();
        await Preferences.set({ key: 'auth_token', value: data.token });
        await Preferences.set({ key: 'auth_user', value: JSON.stringify(data.user) });
        return true;
    }
    return false;
}

// Send token with every API request
async function apiRequest(endpoint, options = {}) {
    const { value: token } = await Preferences.get({ key: 'auth_token' });
    
    const response = await fetch(endpoint, {
        ...options,
        headers: {
            ...options.headers,
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    });
    
    if (response.status === 401) {
        // Token expired - redirect to login
        await Preferences.clear();
        window.location.href = './index.html';
        return null;
    }
    
    return response.json();
}
```

---

### 🚨 CAP-05: No CORS Configuration at All (🟠 HIGH)

**Files:** `app/helpers/bootstrap.php`, `app/controllers/AuthController.php`  
**Severity:** 🟠 HIGH (Mobile API calls will be blocked by CORS)

**Problem:**
- The PHP backend has **zero CORS headers**
- `bootstrap.php` and `AuthController.php` never set `Access-Control-Allow-Origin`
- When the Capacitor app loads from `file://` or `http://localhost` and tries to call the PHP server, the browser/WebView blocks the request
- Even if `server.url` is configured in Capacitor, any cross-origin navigation would fail

**Root Cause:**  
The backend was built assuming same-origin requests only (browser → same PHP server). Capacitor apps are inherently cross-origin.

**Fix:**  
Add CORS middleware to `bootstrap.php`:

```php
// In app/helpers/bootstrap.php, after session start
// CORS headers for Capacitor WebView
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
```

---

### 🚨 CAP-06: No Token Refresh Mechanism and Access Token Has No Expiry (🟠 HIGH)

**Files:** `mobile-shell/index.html`, `app/helpers/SessionHandler.php:30-33`  
**Severity:** 🟠 HIGH (Once token is stolen, it works forever)

**Problem:**
- The current mobile auth has **no tokens at all** (just `sessionStorage`)
- Even in the proposed fix, there's no:
  - Access token expiry
  - Refresh token rotation
  - Token revocation mechanism
  - Session timeout check (Issue #6 from original audit)

**Root Cause:**  
The auth system was never designed for token-based mobile access.

**Fix:**  
Implement proper token lifecycle in a new `AuthTokens` table:

```sql
-- sql/schema.sql - Add tokens table
CREATE TABLE IF NOT EXISTS AuthTokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TokenHash VARCHAR(64) NOT NULL,
    RefreshTokenHash VARCHAR(64),
    ExpiresAt DATETIME NOT NULL,
    RefreshExpiresAt DATETIME,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastUsedAt DATETIME,
    RevokedAt DATETIME,
    DeviceInfo VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_token_hash (TokenHash),
    INDEX idx_refresh_hash (RefreshTokenHash),
    INDEX idx_user_id (UserID)
);
```

---

### 🚨 CAP-07: android.allowMixedContent Blocking HTTP API Calls (🟡 MEDIUM)

**File:** `capacitor.config.json:9`  
**Severity:** 🟡 MEDIUM

```json
{
  "android": {
    "allowMixedContent": false,
    "captureInput": true
  }
}
```

**Problem:**  
- `allowMixedContent: false` blocks all HTTP requests when the WebView loads from HTTPS
- If connecting to a local dev server over HTTP, the Android WebView will block it
- Combined with `server.cleartext: true`, this is contradictory - cleartext allows HTTP, but mixedContent blocks non-HTTPS resources

**Root Cause:**  
Misconfiguration - the two settings conflict.

**Fix:**  
```json
{
  "android": {
    "allowMixedContent": true,
    "captureInput": true
  }
}
```

Or for production (secure):
```json
{
  "android": {
    "allowMixedContent": false,
    "captureInput": true
  },
  "server": {
    "url": "https://your-production-server.com",
    "cleartext": false
  }
}
```

---

### 🚨 CAP-08: No Auth State Restoration on App Relaunch (🟠 HIGH)

**Files:** `mobile-shell/assets/shared.js:123-130`  
**Severity:** 🟠 HIGH

```javascript
function initPage(pageTitle, currentPage) {
  const user = getCurrentUser();
  if (!user || !user.username) {
    window.location.href = './index.html';
    return;
  }
  // ...
}
```

**Problem:**  
- `initPage()` runs on every page load
- It checks if user data exists in `sessionStorage`
- On app restart, `sessionStorage` is empty → user gets redirected to login
- Even with `localStorage`, there's no server-side validation of the stored token
- A tampered `localStorage` entry would grant access without any backend check

**Fix:**  
Add token validation on app initialization:

```javascript
async function initPage(pageTitle, currentPage) {
  const { value: token } = await Preferences.get({ key: 'auth_token' });
  const { value: userData } = await Preferences.get({ key: 'auth_user' });
  
  if (!token || !userData) {
    window.location.href = './index.html';
    return;
  }
  
  // Validate token with backend
  try {
    const response = await fetch('/api/auth/validate.php', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    if (!response.ok) {
      // Token invalid or expired
      await Preferences.clear();
      window.location.href = './index.html';
      return;
    }
    
    const data = await response.json();
    // Update user data from server
    await Preferences.set({ key: 'auth_user', value: JSON.stringify(data.user) });
  } catch (err) {
    // Offline - allow cached data if within refresh window
    const user = JSON.parse(userData);
    if (Date.now() - user.lastValidated > 300000) { // 5 min
      window.location.href = './index.html';
      return;
    }
  }
  
  // Continue with page initialization
  // ...
}
```

---

### 🚨 CAP-09: Plaintext HTTP Traffic with Cleartext Enabled (🔴 CRITICAL)

**File:** `capacitor.config.json:6`  
**Severity:** 🔴 CRITICAL

```json
"server": {
  "cleartext": true
}
```

**Problem:**  
- `cleartext: true` allows unencrypted HTTP traffic
- All login credentials, tokens, and data would be sent in plaintext
- On public WiFi or mobile networks, attackers can trivially intercept all traffic
- No HTTPS enforcement on the PHP backend either (Issue #13 from original audit)

**Fix:**  
```json
// Production
"server": {
  "url": "https://your-production-server.com",
  "cleartext": false
}
```

And enforce HTTPS in the backend (updated from original Issue #13):
```php
// In app/helpers/bootstrap.php
function enforceHTTPS() {
    $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '[::1]']);
    
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if (!$isLocal && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
            $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: {$redirectUrl}");
            exit();
        }
    }
    
    // Security headers
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
}
```

---

### 🚨 CAP-10: Backend Does Not Set HttpOnly/Secure/SameSite Session Cookies (🟡 MEDIUM)

**File:** `app/controllers/AuthController.php:46-54,103-110`  
**Severity:** 🟡 MEDIUM

**Problem:**  
- PHP sessions rely on the default `PHPSESSID` cookie
- No `HttpOnly` flag → JavaScript can read the session cookie
- No `Secure` flag → cookie sent over HTTP
- No `SameSite` flag → vulnerable to CSRF
- In Capacitor, missing SameSite flag can cause the cookie to not be sent on cross-origin requests

**Fix:**  
Configure session cookie parameters before `session_start()`:

```php
// In app/helpers/bootstrap.php, before session start
session_set_cookie_params([
    'lifetime' => 86400,     // 24 hours
    'path' => '/',
    'domain' => '',
    'secure' => true,         // HTTPS only
    'httponly' => true,       // Not accessible via JavaScript
    'samesite' => 'Lax'       // CSRF protection
]);
```

Better yet, for mobile, **don't use cookies at all** - use the Authorization header as described in CAP-04.

---

## 🚨 ORIGINAL AUTH ISSUES (Confirmed and Updated)

### 🚨 #1 — Database Connection Skipped for Login POST (🔴 CRITICAL)

**File:** `app/controllers/AuthController.php:21`  
**Severity:** 🔴 CRITICAL

```php
// DB connection is SKIPPED for login POST
if (!($action === 'login' && $_SERVER["REQUEST_METHOD"] === "POST")) {
    try { $db = getDBConnection(); } catch (Exception $dbError) { ... }
}
```

**Impact on Capacitor:**  
If mobile is ever pointed at the PHP backend, this bug means NO database auth works. Demo fallback only.

**Fix:**  
```php
// Always attempt DB connection
try {
    $db = getDBConnection();
} catch (Exception $dbError) {
    logMessage("Database unavailable: " . $dbError->getMessage(), 'WARNING');
}
```

---

### 🚨 #2 — Duplicate Login Code Blocks (🟠 HIGH)

**File:** `app/controllers/AuthController.php:34-58,65-124`  
**Severity:** 🟠 HIGH

Two separate `if ($action === 'login')` blocks. The first does demo fast-path, the second does "real" auth. Both execute. If first succeeds, redirect happens. If first fails (wrong password), execution falls through to second block.

---

### 🚨 #3 — Plaintext Password Comparison (🟠 HIGH)

**Files:** `app/controllers/AuthController.php:44,90`, `.demo_users.json`  
**Severity:** 🟠 HIGH

Demo passwords stored in plaintext, compared with `===`.

---

### 🚨 #4 — Wrong Column Name `Password` vs `PasswordHash` (🔴 CRITICAL)

**File:** `app/controllers/UserController.php:162`  
**Severity:** 🔴 CRITICAL

```php
// WRONG: column is 'PasswordHash' not 'Password'
$query = "INSERT INTO Users (...) Password, ...)";
```

---

### 🚨 #5 — No Session Regeneration (🟠 HIGH)

**File:** `app/controllers/AuthController.php`  
**Severity:** 🟠 HIGH

No `session_regenerate_id(true)` after login. Vulnerable to session fixation.

---

### 🚨 #6 — No Session Timeout (🟡 MEDIUM)

**File:** `app/helpers/SessionHandler.php:30-33`  
**Severity:** 🟡 MEDIUM

No timeout check on sessions. Sessions never expire.

---

### 🚨 #7 — Mobile Client-Side Only Auth (Covered by CAP-01, CAP-02, CAP-03)

---

### 🚨 #8 — No CSRF Protection (🟡 MEDIUM)

**File:** `app/views/login.php:276`  
**Severity:** 🟡 MEDIUM

CSRF token functions exist in bootstrap but are unused in login form.

---

### 🚨 #9 — Dual Status System (🟡 MEDIUM)

**File:** `sql/schema.sql:30,38`  
**Severity:** 🟡 MEDIUM

Both `Status ENUM` and `IsActive BOOLEAN` on Users table. Can get out of sync.

---

### 🚨 #10 — Password Logged in Plaintext (🟠 HIGH)

**File:** `app/controllers/UserController.php:174`  
**Severity:** 🟠 HIGH

Temporary passwords logged to ActivityLog table.

---

### 🚨 #11 — No Rate Limiting (🟡 MEDIUM)

**File:** `app/controllers/AuthController.php`  
**Severity:** 🟡 MEDIUM

No rate limiting or account lockout.

---

### 🚨 #12 — SQL Injection Risk (🟡 MEDIUM)

**File:** `app/controllers/UserController.php:108-116`  
**Severity:** 🟡 MEDIUM

Inconsistent use of prepared statements.

---

### 🚨 #13 — No HTTPS Enforcement (🔵 LOW)

**File:** `app/helpers/bootstrap.php`  
**Severity:** 🔵 LOW → 🔴 HIGH for Capacitor (See CAP-09)

---

## 📋 COMPLETE ISSUE SUMMARY (Combined)

| # | Issue | File(s) | Severity | Type | Capacitor-Specific? |
|---|-------|---------|----------|------|-------------------|
| **C1** | No backend integration for mobile | `capacitor.config.json`, `mobile-shell/` | 🔴 BLOCKER | Architecture | ✅ Yes |
| **C2** | sessionStorage wiped on app restart | `mobile-shell/index.html:66`, `shared.js:5-7` | 🔴 BLOCKER | Persistence | ✅ Yes |
| **C3** | No secure storage for tokens | `mobile-shell/index.html:52-72` | 🔴 CRITICAL | Security | ✅ Yes |
| **C4** | Cookie sessions incompatible with WebView | `SessionHandler.php`, `AuthController.php` | 🔴 CRITICAL | Architecture | ✅ Yes |
| **C5** | No CORS headers | `bootstrap.php`, `AuthController.php` | 🟠 HIGH | Configuration | ✅ Yes |
| **C6** | No token refresh mechanism | `SessionHandler.php:30-33` | 🟠 HIGH | Security | ✅ Yes |
| **C7** | allowMixedContent blocks HTTP | `capacitor.config.json:9` | 🟡 MEDIUM | Configuration | ✅ Yes |
| **C8** | No auth state restoration | `shared.js:123-130` | 🟠 HIGH | UX | ✅ Yes |
| **C9** | cleartext + no HTTPS | `capacitor.config.json:6` | 🔴 CRITICAL | Security | ✅ Yes |
| **C10** | Missing session cookie flags | `AuthController.php` | 🟡 MEDIUM | Security | ✅ Yes |
| 1 | DB connection skipped for login POST | `AuthController.php:21` | 🔴 CRITICAL | Logic Bug | No |
| 2 | Duplicate login code blocks | `AuthController.php:34,65` | 🟠 HIGH | Code Quality | No |
| 3 | Plaintext password comparison | `AuthController.php:44,90` | 🟠 HIGH | Security | No |
| 4 | Wrong column `Password` vs `PasswordHash` | `UserController.php:162` | 🔴 CRITICAL | SQL Bug | No |
| 5 | No session regeneration | `AuthController.php` | 🟠 HIGH | Security | No |
| 6 | No session timeout | `SessionHandler.php:30-33` | 🟡 MEDIUM | Security | No |
| 8 | No CSRF protection | `views/login.php:276` | 🟡 MEDIUM | Security | No |
| 9 | Dual Status/IsActive system | `sql/schema.sql:30,38` | 🟡 MEDIUM | Design Flaw | No |
| 10 | Password logged in plaintext | `UserController.php:174` | 🟠 HIGH | Security | No |
| 11 | No rate limiting | `AuthController.php` | 🟡 MEDIUM | Security | No |
| 12 | SQL injection risk | `UserController.php:108-116` | 🟡 MEDIUM | Security | No |
| 13 | No HTTPS enforcement | `bootstrap.php` | 🔵 LOW (🔴 HIGH for mobile) | Security | Partially |
| 14 | Absolute form action path | `views/login.php:276` | 🔵 LOW | Config | No |
| 15 | No account lockout notification | `AuthController.php` | 🔵 LOW | UX | No |

---

## 🎯 PRIORITY FIX ORDER (Capacitor-Focused)

### Phase 1: Make Login Work (BLOCKERS - Must fix immediately)

1. **CAP-01** → Add `server.url` in `capacitor.config.json` OR build API layer in `api/`
2. **CAP-02** → Replace `sessionStorage` with `@capacitor/preferences` or at minimum `localStorage`
3. **CAP-04** → Implement token-based auth (JWT or bearer token) instead of cookie sessions for mobile

### Phase 2: Security & Persistence (CRITICAL - Fix next)

4. **CAP-03** → Remove hardcoded credentials, use secure storage for tokens
5. **CAP-09** → Enforce HTTPS, change `cleartext` to false in production
6. **CAP-05** → Add CORS headers to all API endpoints
7. **#1** → Fix DB connection skip in AuthController

### Phase 3: Reliability & UX (HIGH - Fix for stable app)

8. **CAP-06** → Implement access token expiry + refresh token rotation
9. **CAP-08** → Add token validation on app startup / initPage()
10. **CAP-10** → Configure session cookie params (if still using cookies)
11. **#5** → Add `session_regenerate_id()` after login
12. **#6** → Add session timeout check

### Phase 4: Code Quality & Hardening (MEDIUM/LOW)

13. **CAP-07** → Fix `allowMixedContent` mismatch
14. **#2** → Merge duplicate login blocks
15. **#3** → Hash demo passwords
16. **#4** → Fix `Password` → `PasswordHash` column
17. **#8** → Add CSRF tokens
18. **#9** → Consolidate Status/IsActive
19. **#10** → Stop logging passwords
20. **#11** → Add rate limiting
21. **#12** → Prepared statements
22. **#13** → HTTPS enforcement
23. **#14** → Relative paths

---

## 🔗 FILES TOUCHED BY ALL FIXES

| File | Issues |
|------|--------|
| `capacitor.config.json` | C1, C7, C9 |
| `mobile-shell/index.html` | C1, C2, C3, C8 |
| `mobile-shell/assets/shared.js` | C2, C3, C4, C6, C8 |
| `app/helpers/bootstrap.php` | C5, C10, 13 |
| `app/controllers/AuthController.php` | 1, 2, 3, 5, 11 |
| `app/controllers/UserController.php` | 4, 10, 12 |
| `app/helpers/SessionHandler.php` | C4, 6 |
| `app/views/login.php` | 8, 14 |
| `sql/schema.sql` | C6, 9 |
| `.demo_users.json` | 3 |
| `api/auth/login.php` | NEW - C1, C4 (needs creation) |
| `api/auth/validate.php` | NEW - C6, C8 (needs creation) |
| `package.json` | C2, C3 (needs @capacitor/preferences) |

---

## 📌 CAPACITOR-SPECIFIC ACTION ITEMS (Immediate)

### 1. Install Capacitor Preferences Plugin
```bash
cd /path/to/project
npm install @capacitor/preferences
npx cap sync
```

### 2. Update `capacitor.config.json`
```json
{
  "appId": "com.tharimpepe.fsms",
  "appName": "Tharimpepe FSMS",
  "webDir": "mobile-shell",
  "server": {
    "url": "http://YOUR_DEV_IP:8000",
    "cleartext": true,
    "allowNavigation": ["YOUR_DEV_IP"]
  },
  "android": {
    "allowMixedContent": true,
    "captureInput": true
  }
}
```

### 3. Update `mobile-shell/assets/shared.js` - Replace sessionStorage with Persistent Storage
```javascript
// Capacitor-compatible storage wrapper
const AppStorage = {
  async get(key) {
    if (window.Capacitor?.isNativePlatform()) {
      const { Preferences } = await import('@capacitor/preferences');
      const { value } = await Preferences.get({ key });
      return value;
    }
    // Fallback to localStorage in browser
    return localStorage.getItem(key);
  },
  async set(key, value) {
    if (window.Capacitor?.isNativePlatform()) {
      const { Preferences } = await import('@capacitor/preferences');
      await Preferences.set({ key, value });
    } else {
      localStorage.setItem(key, value);
    }
  },
  async remove(key) {
    if (window.Capacitor?.isNativePlatform()) {
      const { Preferences } = await import('@capacitor/preferences');
      await Preferences.remove({ key });
    } else {
      localStorage.removeItem(key);
    }
  },
  async clear() {
    if (window.Capacitor?.isNativePlatform()) {
      const { Preferences } = await import('@capacitor/preferences');
      await Preferences.clear();
    } else {
      localStorage.clear();
    }
  }
};
```

### 4. Create `api/auth/login.php` (API endpoint for mobile)
```php
<?php
// api/auth/login.php - Mobile authentication endpoint
require_once __DIR__ . '/../../app/helpers/bootstrap.php';

// CORS headers for Capacitor
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit();
}

try {
    $userModel = new User($db);
    $user = $userModel->authenticate($input['username'], $input['password']);
    
    if ($user) {
        // Generate access token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $db->prepare(
            "INSERT INTO AuthTokens (UserID, TokenHash, ExpiresAt, CreatedAt) 
             VALUES (:uid, :hash, :expires, NOW())"
        );
        $stmt->execute([
            ':uid' => $user['UserID'],
            ':hash' => $tokenHash,
            ':expires' => $expiresAt
        ]);
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => [
                'user_id' => $user['UserID'],
                'username' => $user['Username'],
                'email' => $user['Email'],
                'role' => $user['Role']
            ]
        ]);
    } else {
        // Fallback to demo users if DB unavailable
        $demoFile = dirname(__DIR__, 2) . '/.demo_users.json';
        if (is_file($demoFile)) {
            $demoUsers = json_decode(file_get_contents($demoFile), true);
            if (!empty($demoUsers[$input['username']]) && 
                $demoUsers[$input['username']]['password'] === $input['password']) {
                $demo = $demoUsers[$input['username']];
                echo json_encode([
                    'success' => true,
                    'token' => 'demo-token-' . bin2hex(random_bytes(16)),
                    'user' => [
                        'user_id' => 1,
                        'username' => $input['username'],
                        'email' => $demo['email'],
                        'role' => $demo['role']
                    ]
                ]);
                exit();
            }
        }
        
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    logMessage("Mobile login error: " . $e->getMessage(), 'ERROR');
}
```

---

## ✅ CONCLUSION

The mobile app and web app are **architecturally two completely separate authentication systems**:

| Aspect | Web App | Mobile App (Capacitor) |
|--------|---------|----------------------|
| Auth mechanism | PHP server-side sessions | Client-side sessionStorage |
| Storage persistence | PHP session files on server | WebView volatile memory |
| Security model | Cookie-based (PHPSESSID) | Plaintext JS checks |0
| Backend contact | Yes (same origin) | **No backend contact at all** |
| Login validation | Server validates credentials | Hardcoded JS object |
| Session timeout | None implemented | None (sessionStorage auto-clears) |

**The top 3 things to fix immediately:**
1. **Token-based API** → Create `api/auth/login.php` for mobile to call
2. **Persistent storage** → Replace `sessionStorage` with `@capacitor/preferences`
3. **Capacitor config** → Add `server.url` and fix `allowMixedContent`/`cleartext` settings

**Without these fixes, the "mobile app" is a static prototype that cannot actually log in to the backend system.**