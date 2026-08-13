# Deployment Guide

## Required Environment

- PHP with mysqli, mbstring, fileinfo, ZIP, XML/SimpleXML, sessions, and OpenSSL support.
- MariaDB/MySQL compatible with the authoritative `nexus_ucd` schema.
- Apache with `.htaccess` overrides enabled, HTTPS, and the project root configured as the application web root.
- Writable `application/cache` and `application/logs`; all other application source should be read-only to the web-service account.

Set production environment values outside source control:

```text
CI_ENV=production
UCD_BASE_URL=https://ucd.example.internal/
UCD_ENCRYPTION_KEY=<high-entropy secret>
UCD_DB_HOST=<database host>
UCD_DB_PORT=3306
UCD_DB_USER=<least-privilege application user>
UCD_DB_PASSWORD=<secret-store value>
UCD_DB_NAME=nexus_ucd
UCD_COOKIE_SECURE=true
UCD_LOG_THRESHOLD=1
UCD_PROXY_IPS=<trusted proxy addresses, if applicable>
```

## Release Procedure

1. Back up the database and verify its checksum.
2. Deploy to staging using the same PHP/Apache configuration as production.
3. Run `powershell -File scripts/release_check.ps1 -BaseUrl https://staging.example/index.php`.
4. Smoke-test login, forced password change, permission denial, dashboard, master lists, rate history, BOQ staging/mapping, benchmarking, and cost intelligence.
5. Confirm production errors are logged without browser backtraces and sensitive repository files return HTTP 403.
6. Deploy during an approved window, run health/release checks, and retain a tested rollback package.

Do not run the historical schema dumps directly against an existing database. The 17 placeholder views and unresolved reference targets remain governed exceptions documented in project memory.
