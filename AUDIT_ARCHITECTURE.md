# Architecture Compliance Report — FSMS (Tharimpepe Feeding Scheme)

**Date:** 2026-06-18  
**Reviewed By:** Lead Software Engineer  
**Scope:** MVC architecture compliance, mobile-web integration, separation of concerns, code duplication, and adherence to WIL System Design patterns  
**Reference:** Task 2b System Design Section 2 (Architecture), all `app/`, `api/`, `mobile-shell/` directories

---

## 1. MVC ARCHITECTURE COMPLIANCE

### 1.1 Directory Structure Assessment

```
project/
├── app/
│   ├── controllers/     ✅ MVC — Controllers handle requests
│   │   ├── AuthController.php
│   │   └── UserController.php
│   ├── models/          ✅ MVC — Models handle data access
│   │   └── User.php
│   ├── views/           ✅ MVC — Views handle presentation
│   │   └── (PHP templates)
│   └── helpers/         ⚠️ Helper classes, not strictly MVC
│       ├── bootstrap.php
│       ├── SessionHandler.php
│       └── FormValidator.php
├── api/                 ⚠️ Not MVC — Procedural script-per-endpoint
│   ├── auth/
│   ├── beneficiaries/
│   └── ...
├── mobile-shell/        ⚠️ Not MVC — Static HTML + JS
│   ├── index.html
│   └── assets/
└── public/              ✅ Entry point for web app
    └── router.php
```

### 1.2 Web App MVC Compliance

| Component | Location | Compliance | Notes |
|-----------|----------|------------|-------|
| Model | `app/models/User.php` | ✅ | Single responsibility: data access for Users |
| Controller | `app/controllers/AuthController.php` | ⚠️ | Mixes form handling, validation, and redirect logic |
| Controller | `app/controllers/UserController.php` | ✅ | Clean switch-based action dispatch |
| View | `app/views/*.php` | ✅ | PHP templates with HTML |
| Routing | `public/router.php` or direct controller include | ⚠️ | Controllers included directly, not via router |

**Finding ARC-01: Controllers included directly, no front controller pattern**

`AuthController.php` ends with:
```php
if ($action === 'login') {
    include __DIR__ . "/../views/login.php";
}
```
This is a **page controller** pattern, not MVC's front controller. Each controller file IS the entry point. There's no central router dispatching to controllers.

**Severity:** 🟡 MEDIUM — Works but limits route flexibility and middleware injection.

---

### 1.3 API Layer MVC Compliance

| Component | Status | Notes |
|-----------|--------|-------|
| Model | ❌ | No `api/models/` directory; models shared with web app |
| Controller | ❌ | Each endpoint is a standalone script, not a controller class |
| View | ❌ | JSON output, not views |

**Finding:** The API layer does NOT follow MVC. It uses a **script-per-endpoint** pattern (similar to PHP micro-frameworks). `api/beneficiaries/list.php` is both "controller" (auth check, query logic) and "view" (JSON encoding).

**Assessment:** Not a violation for an API layer. REST APIs commonly use this pattern. However, it creates duplication with web controllers.

---

## 2. SEPARATION OF CONCERNS

### 2.1 Layer Boundaries

| Layer | Responsibility | Status | Violations |
|-------|---------------|--------|------------|
| Presentation | HTML/CSS/JS rendering | ✅ | Mobile shell handles its own |
| Application | Request handling, auth, routing | ⚠️ | `AuthController` mixes concerns |
| Domain | Business rules, validation | ⚠️ | Validation in `FormValidator` helper |
| Data | Database access | ✅ | `User` model encapsulates DB |

**Finding ARC-02: Validation logic lives in helper, not model or service**

`FormValidator` (`app/helpers/FormValidator.php`) is a procedural utility, not a class. It handles:
- Required field checking
- Format validation (email, username, password)
- Error accumulation

This is OK for a small app but violates **Single Responsibility** — validators should be near the models they validate or in a dedicated validation service.

---

### 2.2 Bootstrap Concerns

`app/helpers/bootstrap.php` handles:
1. Error reporting configuration
2. Session management (setup, timeout, cookie params)
3. CORS headers
4. Database connection helper
5. Utility functions (currency, date, text formatting, logging, CSRF)

**Finding:** `bootstrap.php` is a **God file** — it does too many things. It should be split:
- `config/bootstrap.php` — constants, error reporting
- `app/bootstrap.php` — session, auth, database
- `helpers/cors.php` — CORS middleware
- `helpers/format.php` — display utilities

**Severity:** 🟡 MEDIUM — Works but grows unmaintainable.

---

## 3. MOBILE-WEB INTEGRATION

### 3.1 Current Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    TWO COMPLETELY SEPARATE SYSTEMS            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  WEB APP (PHP)                    MOBILE APP (Capacitor)    │
│  ─────────────                    ────────────────────      │
│  Browser → PHP sessions             WebView → JavaScript    │
│       ↓                                   ↓                 │
│  MySQL via PDO                      localStorage           │
│       ↓                                   ↓                 │
│  HTML Views                         HTML Pages             │
│       ↓                                   ↓                 │
│  No API calls                        No API calls           │
│                                                             │
│  ⚠ ZERO DATA SHARING                                          │
│  ⚠ ZERO CODE SHARING                                          │
│  ⚠ ZERO AUTH INTEGRATION                                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Auth System Comparison

| Aspect | Web App | Mobile App |
|--------|---------|------------|
| Auth mechanism | PHP session cookies | Client-side localStorage |
| Credential check | `User::authenticate()` (DB) | Hardcoded `demoUsers` object |
| Session persistence | Server-side (DB not required) | WebView localStorage |
| Auth state | `$_SESSION` | `localStorage` |
| Token support | ❌ | ❌ (no Bearer token usage) |
| Backend dependency | Yes (DB) | No (fully client-side) |
| Real users | ✅ Yes | ❌ Only demo users |

**Finding ARC-03: Mobile app does NOT use the API auth layer**

Despite `api/auth/login.php` existing and being functional, the mobile app's `mobile-shell/index.html`:
1. Does NOT call `/api/auth/login.php`
2. Uses hardcoded demo credentials
3. Stores auth in `localStorage`
4. Never contacts the PHP backend

This is a **critical architectural failure**. The mobile app is a prototype disconnected from the production system.

---

### 3.3 Capacitor Specific Issues

`capacitor.config.json`:
```json
{
  "webDir": "mobile-shell",
  "server": {
    "cleartext": true,
    "allowNavigation": ["*"]
  },
  "android": {
    "allowMixedContent": true
  }
}
```

| Issue | Status | Impact |
|-------|--------|--------|
| No `server.url` | 🔴 BLOCKER | App serves local files only; no backend |
| `cleartext: true` | 🔴 CRITICAL | Allows HTTP; credentials in plaintext |
| `allowNavigation: ["*"]` | 🟠 HIGH | Any domain loadable in WebView |
| `allowMixedContent: true` | 🟡 MEDIUM | HTTP resources in HTTPS contexts |

**Assessment:** The Capacitor configuration treats the app as a standalone prototype. The `server.url` field is required to bridge the WebView to the PHP backend. Without it, the mobile app and web app are non-communicating islands.

---

### 3.4 Data Flow Comparison

| Operation | Web App Flow | Mobile App Flow |
|-----------|-------------|-----------------|
| List beneficiaries | PHP → MySQL → HTML table | `localStorage` only (static/demo) |
| Record attendance | Form POST → PHP → MySQL | Not implemented in mobile |
| View stock | PHP → MySQL → HTML | `localStorage` only |
| Generate report | PHP → MySQL → PDF/Excel | Not implemented |

**Note:** `mobile-shell/assets/shared.js` contains `loadDashboardKPIs()` and `loadBeneficiaries()` which call `/api/...` endpoints. The JS layer is partially API-ready, but the login/auth flow never reaches these API calls because:
1. `initPage()` checks `localStorage` for `user`, which is never set by API login
2. The mobile login form (`index.html`) never calls `/api/auth/login.php`

---

## 4. CODE DUPLICATION

### 4.1 Authentication Logic Duplication

| Logic | Web Controller | API Endpoint | Shared? |
|-------|---------------|--------------|---------|
| Credential validation | `AuthController.php:51` via `User::authenticate()` | `api/auth/login.php:53` via `User::authenticate()` | ✅ Shared via model |
| Demo fallback | `AuthController.php:58-73` | `api/auth/login.php:56-71` | ❌ Duplicated |
| Session creation | `AuthController.php:79-84` | `api/auth/login.php:96-110` | ❌ Different mechanisms |
| Token generation | N/A | `api/auth/login.php:83-90` | N/A |

**Finding:** Demo fallback logic is duplicated. This should be extracted to a shared service.

---

### 4.2 Business Logic Location

| Feature | Web | API | Consistent? |
|---------|-----|-----|-------------|
| User registration | `AuthController::register` action | `api/users/create.php` | ❌ Separate implementations |
| User listing | `UserController::listUsers()` | `api/users/list.php` | ❌ Separate implementations |
| Password change | `User::changePassword()` model | `api/users/change-password.php` | ⚠️ Model shared, endpoint separate |
| Beneficiary management | Unknown | `api/beneficiaries/*` | ⚠️ Web implementation unverified |

**Risk:** Bug fixes applied to web controllers may not propagate to API endpoints and vice versa.

---

## 5. SINGLE SOURCE OF TRUTH (SSOT) COMPLIANCE

### 5.1 Data Store Analysis

| Data | Web Storage | Mobile Storage | SSOT Status |
|------|------------|----------------|-------------|
| Auth state | `$_SESSION` (server) | `localStorage` (client) | ❌ TWO sources |
| User data | MySQL | None (demo only) | ❌ MOBILE has no real data |
| Beneficiaries | MySQL | None | ❌ MOBILE has no real data |
| Attendance | MySQL | None | ❌ MOBILE has no real data |
| Stock levels | MySQL | None | ❌ MOBILE has no real data |

**Finding ARC-04: No single source of truth for mobile**

The architectural review (Section 9) correctly identifies that:
> "The database is the ONLY persistent data store"
> "The API is the ONLY way to access the database"

**Current violation:** The mobile app violates both rules. It has no database connection and no API integration. It is entirely client-side.

---

## 6. DESIGN PATTERN COMPLIANCE

### 6.1 WIL System Design Expectations

| Design Pattern | Expected | Actual | Compliance |
|---------------|----------|--------|-------------|
| MVC (Web) | Controllers → Models → Views | `AuthController` → `User` → `login.php` | ✅ Compliant |
| Repository Pattern | Data access abstracted | Models act as repositories | ✅ Compliant |
| Token-based Auth | JWT or bearer tokens | Bearer tokens with DB validation | ✅ Compliant |
| REST API | Resource endpoints | File-per-endpoint pattern | ⚠️ Functional but not RESTful |
| Single Page App | React/Vue frontend | Multi-page HTML | ❌ Not implemented |
| Service Layer | Business logic centralized | Logic in controllers/models | ⚠️ Missing service layer |

---

### 6.2 RESTfulness Assessment

| REST Principle | Current State | Assessment |
|---------------|--------------|------------|
| Resource identification | `/api/beneficiaries/list.php` | ⚠️ Action in URL, not HTTP method |
| HTTP methods | GET/POST/PUT/DELETE used | ✅ Correct methods used |
| Statelessness | Token-based, stateless | ✅ Compliant |
| HATEOAS | No links in responses | ❌ Not implemented (acceptable) |
| Versioning | No version prefix | ❌ No `/api/v1/` |

**Assessment:** The API is functional but not strictly RESTful. "REST-like" is more accurate. Adding a router would improve compliance.

---

## 7. CONFIGURATION & ENVIRONMENT

### 7.1 Configuration Management

| Setting | Location | Issue? |
|---------|----------|--------|
| DB credentials | `config/database.php` | ✅ Separate config file |
| App constants | `bootstrap.php:17-24` | ✅ Constants defined |
| Debug mode | `bootstrap.php:26` | ⚠️ `DEBUG_MODE = true` — likely not production-ready |
| Error reporting | `bootstrap.php:32-39` | ✅ Conditional on DEBUG_MODE |

**Finding ARC-05: DEBUG_MODE hardcoded to true**

```php
define('DEBUG_MODE', true);
```

**Severity:** 🟠 HIGH — Stack traces and DB errors visible to users in production.

---

### 7.2 Environment Separation

| Environment | Indicator | Current |
|------------|-----------|---------|
| Development | `DEBUG_MODE` | Hardcoded true |
| Production | `DEBUG_MODE` false | Requires manual edit |
| Testing | Unknown | No test environment detected |

**Recommendation:** Use environment variables:
```php
define('DEBUG_MODE', getenv('APP_DEBUG') === 'true');
```

---

## 8. MOBILE APP ARCHITECTURE

### 8.1 Mobile App Structure

```
mobile-shell/
├── index.html          Login page (client-side only)
├── dashboard.html      Protected dashboard
├── beneficiaries.html  Beneficiaries view
├── attendance.html     Attendance view
├── stock.html          Stock view
├── volunteers.html     Volunteers view
├── reports.html        Reports view
└── assets/
    ├── shared.js       Auth, navigation, API calls
    ├── shared.css      Common styles
    └── api.js          API client wrapper
```

### 8.2 Mobile App Issues

| Issue | Status | Severity |
|-------|--------|----------|
| No backend integration | 🔴 | BLOCKER |
| Hardcoded demo credentials | 🔴 | CRITICAL |
| `sessionStorage` deprecation | 🔴 | BLOCKER (fixed to localStorage) |
| No token refresh | 🟠 | HIGH |
| No offline support | 🟡 | MEDIUM |
| API client partially built | 🟡 | `api.js` exists but unused for auth |

---

## 9. NAMING & ORGANIZATION

### 9.1 API File Naming

| File | Issue? | Assessment |
|------|--------|------------|
| `api/auth/login.php` | No | ✅ Standard |
| `api/beneficiaries/list.php` | No | ✅ Standard |
| `api/donations/record.php` | Slight | ⚠️ `record` vs `create` — inconsistent |
| `api/stock/add.php` | Slight | ⚠️ `add` vs `create` — inconsistent |
| `api/volunteers/register.php` | Yes | ❌ `register` vs `create` — different verb |
| `api/reports/generate.php` | No | ✅ Action-specific acceptable |

**Finding:** CRUD naming is inconsistent: `list`, `get`, `create`, `update`, `delete` are standard, but `record`, `add`, `register`, `save`, `close`, `assign-shift` break the pattern.

**Recommendation:** Standardize to `create` for all POST-create operations; reserve custom names for non-CRUD actions.

---

### 9.2 Controller Naming

| Controller | Pattern | Compliant? |
|-----------|---------|------------|
| `AuthController.php` | `[Resource]Controller` | ✅ |
| `UserController.php` | `[Resource]Controller` | ✅ |
| Others | Unknown | Only 2 controllers reviewed |

---

## 10. COMPLIANCE WITH WIL ARCHITECTURE DESIGN

### 10.1 WIL Section 2: Layered Architecture

| Layer | Expected | Implemented | Compliant? |
|-------|----------|-------------|------------|
| Presentation Layer | Web UI + Mobile UI | Web UI ✅, Mobile UI ⚠️ | Partial |
| Business Logic Layer | Service classes | Mixed in controllers | ⚠️ Partial |
| Data Access Layer | Models/Repositories | `User` model ✅ | Partial |

**Finding:** The system has the right directory structure (`app/controllers/`, `app/models/`, `app/views/`) but the API layer bypasses this structure entirely.

---

### 10.2 WIL Section 4: API Design

| Principle | Requirement | Status |
|-----------|-------------|--------|
| Token-based authentication | JWT or bearer tokens | ✅ Implemented |
| RESTful endpoints | Resource-based URLs | ⚠️ Functional but not RESTful |
| JSON responses | Consistent format | ⚠️ Mostly consistent |
| Error handling | Standardized errors | ⚠️ Inconsistent |
| Versioning | `/api/v1/` | ❌ Missing |

---

## 11. CRITICAL FINDINGS SUMMARY

| ID | Finding | Location | Severity | Category |
|----|---------|----------|----------|----------|
| ARC-01 | No front controller / router | `app/controllers/` | 🟡 MEDIUM | Architecture |
| ARC-02 | Validation in helper, not model/service | `app/helpers/FormValidator.php` | 🟡 MEDIUM | Architecture |
| ARC-03 | Mobile app has zero backend integration | `mobile-shell/`, `capacitor.config.json` | 🔴 BLOCKER | Integration |
| ARC-04 | No SSOT — mobile has no real data | `mobile-shell/` | 🔴 BLOCKER | Data |
| ARC-05 | `DEBUG_MODE` hardcoded to true | `bootstrap.php:26` | 🟠 HIGH | Configuration |
| ARC-06 | CRUD naming inconsistency | `api/donations/record.php`, `api/stock/add.php` | 🟢 LOW | Convention |
| ARC-07 | No API versioning prefix | All `api/` files | 🟢 LOW | Convention |
| ARC-08 | Business logic duplicated web/API | `AuthController.php` vs `api/auth/login.php` | 🟡 MEDIUM | Duplication |

---

## 12. ARCHITECTURAL RECOMMENDATIONS

### Phase 1: Critical Integration (Week 1-2)
1. **Bridge mobile to API** — Configure `capacitor.config.json` with `server.url` pointing to PHP backend
2. **Replace mobile auth** — `mobile-shell/index.html` must call `/api/auth/login.php`
3. **Fix DEBUG_MODE** — Use `getenv()` for environment detection

### Phase 2: Structure (Week 3-4)
4. **Add front controller** — `public/index.php` as single entry point; implement basic routing
5. **Extract validation service** — Move `FormValidator` into model layer
6. **Standardize API naming** — Rename `record.php` → `create.php`, `add.php` → `create.php`
7. **Centralize CORS** — Move from individual files to `bootstrap.php` (already partially done)

### Phase 3: Maintainability (Month 2)
8. **Consolidate auth fallback** — Extract demo-user check to shared helper
9. **Add API versioning** — `/api/v1/` prefix
10. **Implement service layer** — Extract business logic from controllers into services

---

## 13. POSITIVE FINDINGS

1. **Consistent directory structure** — `controllers/`, `models/`, `views/` follows MVC convention
2. **Models properly encapsulate data access** — `User.php` cleanly handles all User DB operations
3. **API layer is logically organized** — Grouped by resource under `api/[module]/`
4. **Auth middleware is reusable** — `AuthMiddleware` works for both web and API (with adjustments)
5. **Helpers directory** — Good separation of cross-cutting concerns (bootstrap, validation, sessions)
6. **Bootstrap initializes in correct order** — Exceptions → ErrorHandler → Validator → DB → Session
7. **Mobile JS partially API-ready** — `shared.js` has `loadDashboardKPIs()`, `loadBeneficiaries()` ready for backend
8. **Two-system design intentional** — Web for admin, mobile for field workers; architecture supports this

---

## 14. VERDICT

The system demonstrates **partial MVC compliance** for the web layer and **acceptable REST-like API design** for the mobile layer. The single most critical finding is **ARC-03/ARC-04**: the mobile app is architecturally disconnected from the backend. No data flows between them. The API layer exists but is unused by the mobile client.

**Priority 1 (Blocking):** Connect `mobile-shell` to the PHP backend via `capacitor.config.json` `server.url` and update the mobile login flow to use `/api/auth/login.php`.

**Priority 2 (High):** Remove hardcoded `DEBUG_MODE`, fix session timeout API response, add security headers.

**Priority 3 (Medium):** Standardize naming, consolidate duplicate auth logic, add router.

---

*Report compiled by automated codebase audit. No code modifications made.*