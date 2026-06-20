# TODO - Authentication Fix Report

- [x] Identify primary root cause from code: Capacitor client uses hardcoded baseURL `http://192.168.18.47:8000` while manual success uses `http://localhost:8000`.
- [x] Implement plan step 1: update `mobile-shell/assets/api.js` to stop hardcoding `192.168.18.47` and instead use an overrideable backend URL; default to Android emulator alias `10.0.2.2`.
- [ ] Plan step 2: verify and fix Apache routing on port 80 so `/api/auth/login.php` returns JSON (not 404).
- [ ] Re-test: web login, Capacitor login, token refresh, token validate.
- [ ] Produce final detailed report: root causes, severity, exact files, exact code changes, remaining risks.

