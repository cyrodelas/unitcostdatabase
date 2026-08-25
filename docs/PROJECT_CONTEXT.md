# Project Nexus UCD

## Technology Stack
- Backend: CodeIgniter 3.1.13 using standard MVC
- Runtime: PHP 7.4.33 on XAMPP for the current workstation; Laragon-compatible configuration is required
- Database: MariaDB/MySQL database `nexus_ucd`
- UI: AdminLTE 4.0.0, Bootstrap 5.3.8, Chart.js 4.5.1, and DataTables 3.0.1; SweetAlert2 and Select2 are planned for relevant later phases

## Architecture and Conventions
- Controllers handle requests, validation, authorization, and response selection.
- Models own database access and reusable queries; CodeIgniter Query Builder is preferred.
- Views contain presentation logic only and must use shared layout partials.
- The common application shell is composed by `application/views/layouts/main.php` from header, navbar, sidebar, content-header, footer, and scripts partials.
- Module JavaScript belongs in separate files under `assets/js/modules` when practical.
- Large tables should use server-side DataTables processing.
- Apply server-side validation, escaped output, CSRF protection, session checks, password hashing, and endpoint-level authorization.
- Protected controllers extend `Authenticated_Controller`; public controllers extend `MY_Controller`.
- Permission-protected controllers extend `Authorized_Controller` and declare a required permission code; action-specific checks use `authorize()`.
- Authentication uses `app_user`, PHP `PASSWORD_DEFAULT` hashes, forced initial password change, and a five-attempt/15-minute lockout.
- RBAC uses active user-role and role-permission assignments resolved from the database on every protected request.
- Materials, Equipment, Labor, Crews, and Reference Tables are administrator-only catalogs for `SYS_ADMIN` and `UCD_ADMIN`; all other roles receive process-focused navigation and endpoint authorization.
- Display money to two decimal places, dates as `dd-MMM-yyyy`, and datetimes as `dd-MMM-yyyy hh:mm AM/PM`.
- Use UTF-8/utf8mb4 throughout.
- Some imported legacy source rows contain double-encoded diameter (`Ã˜`) and degree (`Â°`) sequences despite utf8mb4 storage. Shared HTML rendering repairs only those known sequences to `Ø` and `°`; authoritative database values are not rewritten without separate data-cleanup authorization.

## Database Baseline
- The existing `nexus_ucd` schema is authoritative.
- Current schema source: the updated `C:\Users\SPI01092\Downloads\nexus_ucd.sql` supplied 12-Aug-2026; it was inspected in an isolated database and reconciled additively because the dump contains destructive statements.
- The dump targets MariaDB 10.4.32 and declares `utf8mb4_unicode_ci`.
- CI database settings support `UCD_DB_HOST`, `UCD_DB_PORT`, `UCD_DB_USER`, `UCD_DB_PASSWORD`, and `UCD_DB_NAME`; local defaults target XAMPP MariaDB as `root` with no password.
- A git-ignored `application/config/database.local.php` may supply host-specific credentials when service-level environment variables cannot be configured; environment variables retain precedence.
- Existing tables and fields must not be recreated, dropped, renamed, or invented.
- Inspect the actual schema before proposing any database change; destructive SQL requires explicit authorization.

## Permanent Decisions
- Development proceeds one explicitly requested phase at a time.
- The base URL is configurable through `UCD_BASE_URL` and defaults to `http://localhost/ucd/` for the current local XAMPP host.
- Production environments must provide `UCD_ENCRYPTION_KEY`; the code fallback is for local development only.
- `UCD_COOKIE_SECURE` can explicitly control secure cookies; otherwise HTTPS is detected from the request.
- Existing database views should be used for read-heavy screens where suitable.
- Enterprise cost items are revision-based; resource build-ups attach to `standard_cost_item_revision`.
- Historical rates are append-oriented and must not be overwritten.
- Frontend runtime assets are pinned and served locally from `assets`; versions are recorded in `assets/VERSIONS.md`.
- Permission codes are application contracts seeded by SQL and are not editable through the UI; roles and their assignments are managed in the application.
- Dashboard aggregates query authoritative base tables until the intended database views are restored from their placeholder-table state.
- Reference CRUD is driven by an allowlisted entity registry in `application/config/reference_data.php`; 49 maintainable entities are exposed, including the surrogate-keyed Project Type–Market Segment applicability bridge and Elemental Cost Bases. Composite-key-only relationships remain outside generic CRUD. Generic model/controller/views must not accept arbitrary table or column names.
- Updated governed references cover attribute classes, PSGC locations, project classifications, trade divisions, rate evidence/basis/period/validation, markups, and confidence metadata. Missing dump targets `ref_standard_item_name` and `ref_uniformat_assembly` must not be fabricated.
- Material master pages query `material_master`, its reference tables, variants, schedules, and append-oriented rate history directly while `vw_material_master_complete` remains a placeholder table.
- Material and equipment lists share `assets/js/modules/master-data.js` for DataTables setup and exact column filters.
- Equipment master uses `equipment_master`, `ref_equipment_group`, and `cost_item_equipment`; no equipment rate schedule/history exists in the authoritative schema.
- Labor master uses governed categories plus schedule-backed rate history, component amounts, source aliases, and cost-item usage; `vw_current_labor_rate` remains a placeholder table.
- Crew composition stores Labor Master references and quantities; calculated daily crew cost is derived from current governed labor totals and is never persisted as a duplicate rate.
- Standard Cost Item screens query revision-based base tables directly; current draft descriptions/classifications may be edited, and Phase 14 governs submission, review, approval, publication, and cloned successor revisions.
- Every Standard Cost Item revision is classified by an active, governed Project Type / Market Segment applicability pair; successor revisions inherit the pair and current baseline data is Residential Subdivision / Socialized.
- Elemental costing is independent of Standard Cost Items: plans and historical evidence are scoped directly by Project Type / Market Segment and UniFormat Level 3/4, with no SCI revision foreign key or SCI-derived amount.
- Unit-rate build-up reproduces the supplied `vw_cost_item_resource_unit_rate` formula against base tables: material quantity × current variant rate, labor days × current governed total rate, plus the three defined allowances; equipment remains non-monetized until a governed rate structure exists.
- Rate management is append-only: material/labor changes create a new effective schedule and rate transactionally while preserving prior records; cost-item observations append with available project/location/source/validation context.
- Governance maps user-facing For Approval to the latest `TECHNICAL_REVIEW / APPROVED` history event because `FOR_APPROVAL` is not an allowed revision status; publishing requires an approved, coded revision.
- Project Master maintains the existing project identity, type, location, scale, dates, and active state; linked cost-item rate observations remain append-only in Rate Management.
- BOQ Management uses four normalized tables for headers/items and staged CSV/XLSX imports, with row validation, error reports, transactional commit, and stable item identifiers reserved for later mapping.
- BOQ-to-UCD mapping stores manual or future suggested candidates against exact standard-cost-item revisions, permits one selected mapping per BOQ item, and preserves mapping status changes in append-only history. Automated suggestions may create candidates later but must never select or confirm them automatically.
- Real contractor XLSX imports may contain summary/detail sheets, multi-row headers, C-F description hierarchy, split material/labor costs, and intentionally unpriced OSM/Tradecon/By Others scope. The parser selects the most detailed recognized sheet, normalizes hierarchy, uses cached formula values only, applies allowlisted UOM aliases, and reports total variance.
- Cost Benchmarking queries authoritative `cost_item_rate_history` directly across item, project, location, period, contractor, and classification dimensions. Statistics and charts are separated by UOM/currency; missing context is reported rather than inferred.
- Cost Intelligence is deterministic, explainable, and read-only. Suggestions rank governed current items without creating mappings; range/outlier signals use comparable UOM/currency groups and reference confidence bands, while missing dated/validated/location evidence is disclosed rather than inferred.
- Future ML runs as a private Python service/worker; CodeIgniter remains authoritative for RBAC, validation, workflow, persistence, and audit, and deterministic/manual fallbacks must remain available.
- New resource build-ups must distinguish manual labor from crew-derived labor. Crew-derived labor expands a selected crew/productivity snapshot into labor rows and must never add a second crew cost over those rows.
- Phase 22 implements day-based crew productivity on current Draft revisions. Editing selected productivity or crew composition marks derived labor stale; direct labor edits require explicit conversion to Manual while retaining current rows.
- Phase 23 creates a complete initial Draft through a six-step guided assembly. It atomically assigns an internal `UCD-######` UID/sequence, revision 01, Ready-for-Code state, resources, exactly one labor method, lineage, coding placeholder, and audit event; it never codes, approves, publishes, or maps the item.
- Phase 24 snapshots eligible extraction, mapping, or resource-template evidence into immutable versioned records, requires human label review, exports approved records asynchronously to checksum-verified JSONL outside the web root, and keeps model registry metadata read-only until later phases.
- Phase 25 invokes an optional active Extraction model only after hardened deterministic parsing. Proposals remain separate from staging until a user with both BOQ management and ML review permissions accepts or corrects them; all applied fields are deterministically revalidated, pending reviews block commit, and service failure preserves the original staged data.
- ML may extract fields, rank candidates, and create reviewed proposals in later authorized phases, but it may not silently alter commercial values, publish cost items, or confirm BOQ mappings.
- Release hardening applies a self-hosted-script CSP and defensive response headers, blocks repository/database/support files from HTTP access, and makes production configuration fail closed without an explicit base URL and encryption key.
- Production operations follow the checked-in deployment, backup/recovery, security, performance, user, and release-checklist documents. Backups must be encrypted/off-host and restored only under explicit authorization.
