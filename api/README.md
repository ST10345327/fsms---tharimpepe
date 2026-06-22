# API

REST JSON endpoints for the FSMS mobile app and external integrations.

## Overview

- 66 PHP endpoints organised by module (auth, beneficiaries, attendance, stock, donations, volunteers, reports, dashboard, audit)
- Authentication via Bearer tokens (`AuthMiddleware.php`)
- All endpoints return JSON responses

## Key Entry Points

| Module | Example Endpoint |
|--------|------------------|
| Auth | `POST /api/auth/login.php` |
| Beneficiaries | `GET /api/beneficiaries/list.php` |
| Attendance | `POST /api/attendance/save.php` |
| Stock | `GET /api/stock/list.php` |
| Donations | `POST /api/donations/record.php` |
| Volunteers | `GET /api/volunteers/list.php` |
| Reports | `POST /api/reports/generate.php` |
| Dashboard | `GET /api/dashboard/summary.php` |
| Health | `GET /api/system/health.php` |

## Usage

Protected endpoints require an `Authorization: Bearer <token>` header obtained from the login endpoint.

See `docs/FUNCTIONALITY_AUDIT.md` for module verification status.
