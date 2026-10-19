# Database migrations

Apply migrations to an existing FSMS database before deploying code that depends on them. For the API token migration, run `20261002_create_api_tokens.sql` against the configured FSMS database using MySQL or phpMyAdmin. New installations receive the same table through `sql/schema.sql`.

The token migration creates an initially empty table. Existing MD5-derived API tokens will no longer authenticate after the new API code is deployed; users must sign in again. New tokens are random bearer values, stored as SHA-256 hashes, expire after 30 days, and are revoked on logout or password change.
