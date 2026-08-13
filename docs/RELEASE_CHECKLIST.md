# Release Checklist

- [ ] Approved database backup and checksum exist outside the web root.
- [ ] Staging restore drill and application smoke test passed.
- [ ] Production environment variables and least-privilege database credentials are configured.
- [ ] HTTPS, secure cookies, trusted proxy settings, and Apache `.htaccess` overrides are verified.
- [ ] `scripts/release_check.ps1` passes against the release target.
- [ ] Role/permission matrix and final `SYS_ADMIN` safeguard are verified.
- [ ] BOQ upload limits and denied storage/source paths return expected results.
- [ ] Error logging is writable and browser responses contain no debug trace.
- [ ] Known legacy-runtime, placeholder-view, formula-cache, and schema-gap risks are accepted by the release owner.
- [ ] Rollback package, responsible operators, maintenance window, and post-release monitoring are confirmed.
