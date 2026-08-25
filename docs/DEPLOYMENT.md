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
UCD_ML_ARTIFACT_PATH=C:\secure-data\nexus_ucd_ml_artifacts
UCD_ML_SERVICE_URL=https://private-ml.internal/v1/extraction
UCD_ML_SERVICE_TOKEN=replace-with-secret-manager-value
UCD_DB_HOST=<database host>
UCD_DB_PORT=3306
UCD_DB_USER=<least-privilege application user>
UCD_DB_PASSWORD=<secret-store value>
UCD_DB_NAME=nexus_ucd
UCD_COOKIE_SECURE=true
UCD_LOG_THRESHOLD=1
UCD_PROXY_IPS=<trusted proxy addresses, if applicable>
```

`UCD_ML_ARTIFACT_PATH` must be an absolute directory outside the Apache document root and writable only by the controlled worker/service account. If omitted on the current XAMPP layout, the application defaults to `C:\xampp\nexus_ucd_ml_artifacts`.

`UCD_ML_SERVICE_URL` is optional. When absent or unavailable, BOQ upload records deterministic fallback and continues normally. If configured, keep the endpoint private and TLS-protected and inject `UCD_ML_SERVICE_TOKEN` from server secret management; do not commit it.

Process one queued Phase 24 dataset export from Task Scheduler or a controlled operator session:

```text
php index.php ml_worker run
```

The command processes at most one queued export and exits. Do not expose `Ml_worker` through HTTP.

## Release Procedure

1. Back up the database and verify its checksum.
2. Deploy to staging using the same PHP/Apache configuration as production.
3. Run `powershell -File scripts/release_check.ps1 -BaseUrl https://staging.example/index.php`.
4. Smoke-test login, forced password change, permission denial, dashboard, master lists, rate history, BOQ staging/mapping, benchmarking, cost intelligence, ML Governance access, and a disposable dataset export in staging.
5. Confirm production errors are logged without browser backtraces and sensitive repository files return HTTP 403.
6. Deploy during an approved window, run health/release checks, and retain a tested rollback package.

Do not run the historical schema dumps directly against an existing database. The 17 placeholder views and unresolved reference targets remain governed exceptions documented in project memory.
