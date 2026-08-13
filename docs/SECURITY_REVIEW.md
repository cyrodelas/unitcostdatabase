# Security Review

## Phase 20 Controls

- Authentication uses hashed passwords, forced initial changes, generic login errors, session-ID regeneration, and a five-attempt lockout.
- CSRF protection covers every POST route; state-changing controllers reject non-POST requests.
- Endpoint permissions are resolved from active database roles on every protected request. Governed actions use separate manage/review/approve/publish permissions.
- Output is escaped in views, generic reference access is allowlisted, and Query Builder is used for user-controlled filters.
- Dynamic responses now send CSP, anti-framing, MIME-sniffing, referrer, browser-feature, and no-store headers. HSTS is sent on HTTPS.
- SQL, Markdown, configuration metadata, logs, and directory listings are denied by Apache rules.
- BOQ uploads are size/type/MIME/structure checked, stored under generated names in a denied directory, and XLSX XML rejects DTD/entity declarations and network access.
- Production startup requires an explicit base URL and encryption key, forces secure cookies, hides debug backtraces, and logs errors by default.

## Authorization and Audit Verification

All web controllers were reviewed: public access is limited to login and health; account pages require authentication; business modules use permission-protected controllers or action-specific authorization. Current approval, cost-item audit, and BOQ mapping-history tables have no orphan records. Mapping and governance transitions write append-only histories transactionally.

The authoritative schema does not provide a universal application audit table. Reference/master CRUD therefore retains its existing timestamps/actor fields where available but does not have one consolidated event trail. Adding such a table is a future schema-governance decision, not a Phase 20 invention.

## Residual Risks

- The workstation runtime is PHP 7.4 with CodeIgniter 3. This legacy stack should be upgraded and dependency-scanned before public internet exposure.
- The local BOQ XLSX reader uses cached formula results and deliberately does not evaluate formulas.
- Seventeen intended database views remain placeholder base tables until separately authorized destructive conversion.
- Production must use HTTPS, a least-privilege database account, protected off-host backups, and restricted network access. Local `root`/blank-password defaults are development-only.
- The current `nexuscandy` database account has DDL privileges and `GRANT OPTION`; the DBA should replace these with least-privilege runtime grants after deployment tasks are complete.
