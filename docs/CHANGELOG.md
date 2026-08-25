# Changelog

## Phase 0 — Repository and Database Assessment
- Assessed the CodeIgniter 3 skeleton and default configuration.
- Catalogued the `nexus_ucd` SQL dump, relationships, views, procedures, and confirmed schema gaps.
- Created the seven required project memory documents.
- Made no application feature or database changes.

## Phase 1 — CodeIgniter 3 Base Application
- Configured environment-aware base URL, database access, autoloading, file sessions, secure cookie defaults, and CSRF protection.
- Added a public database readiness endpoint at `/health`.
- Established the missing local `nexus_ucd` baseline without executing destructive dump statements.
- Verified successful database access through CLI and Apache.
- Recorded the 17 unfinalized view placeholders that require explicit drop authorization.

## Phase 2 — AdminLTE 4 Integration
- Vendored pinned AdminLTE 4.0.0 and runtime dependencies locally.
- Added reusable layout partials for the header, navbar, sidebar, content header, footer, and scripts.
- Implemented a responsive Nexus UCD application shell with theme switching and planned-module navigation placeholders.
- Verified local assets, layout rendering, responsive behavior, and database health.

## Phase 3 — Authentication
- Added the minimal `app_user` table without roles or permissions.
- Implemented username/email login, password hashing, lockout, session regeneration, active-user validation, and CSRF-protected POST logout.
- Added forced first-login password change, account screens, and strong-password enforcement.
- Added a CLI-only user provisioner and created the initial local administrator.
- Protected the application shell while retaining a public health endpoint.

## Phase 4 — Roles and Permissions
- Added four normalized RBAC tables and seeded 8 system roles, 35 permission codes, and a least-privilege assignment matrix.
- Assigned the initial administrator to `SYS_ADMIN` and protected the final administrator assignment.
- Added reusable server-side permission enforcement to the controller hierarchy.
- Implemented role master, permission catalog, role-permission assignment, and user-role assignment screens.
- Added permission-aware sidebar visibility and verified allowed and denied access paths.

## Phase 5 — Dashboard
- Replaced the application home with a permission-protected operational dashboard.
- Added permission-aware KPI cards for library, publication, review, and approval counts.
- Added current revision-status and resource build-up coverage charts using locally hosted Chart.js 4.5.1.
- Added coded-revision, rate-observation, validation, and active-project operational totals.
- Kept all dashboard aggregation on authoritative base tables, leaving the 17 view placeholders untouched.

## Phase 6 — Reference Data Management
- Added a reusable, allowlisted CRUD pattern covering all 16 authoritative `ref_*` tables.
- Added a reference catalog, searchable/sortable/paginated DataTables lists, and dedicated create/edit forms.
- Added server-side validation, parent-reference validation, duplicate checks, active/inactive controls, and audit timestamp display.
- Enforced separate reference view/manage permissions and omitted hard-delete operations.
- Vendored DataTables 3.0.1 with Bootstrap 5 styling for local delivery.

## Phase 7 — Material Master
- Added a searchable material list with category, group, and active-status filters.
- Added material detail pages showing reference assignments, variants, current rates, schedules, and rate history.
- Added validated material create/edit forms, duplicate-code protection, and CSRF-protected status changes.
- Enforced separate material view/manage permissions and retained append-oriented rate history as read-only.
- Queried authoritative material base tables because the intended complete-material view remains a placeholder table.

## Phase 8 — Equipment Master
- Added a searchable equipment list with group, scope, and active-status filters.
- Added equipment details with group reference data and standard-cost-item usage visibility.
- Added validated create/edit forms, duplicate-code protection, and CSRF-protected status changes.
- Reused a shared master-data DataTables/filter module across Material and Equipment Master.
- Surfaced the confirmed equipment-rate schema gap instead of inventing rate structures.

## Phase 9 — Labor Master
- Added a searchable labor craft list with governed-category and active-status filters.
- Added labor detail pages with current rates, rate history, component breakdowns, source aliases, and cost-item usage.
- Added validated create/edit forms, duplicate-code protection, category-integrity checks, and CSRF-protected status changes.
- Reused the shared master-data DataTables/filter behavior and enforced separate labor view/manage permissions.
- Kept schedule-backed labor rates and component amounts read-only and queried authoritative base tables while the intended current-rate view remains a placeholder.

## Phase 10 — Crew Master
- Added a searchable crew list with source-trade and active-status filters.
- Added crew header create/edit/status management and composition add/edit forms.
- Referenced governed Labor Master crafts, enforced one row per craft per crew, and validated positive member quantities.
- Calculated current daily crew cost from member quantities and governed labor total rates without duplicating rate data.
- Added productivity usage visibility and separate crew view/manage permission enforcement.
- Omitted member deletion because no soft-delete field exists and destructive SQL was not authorized.

## Phase 11 — Standard Cost Item Master
- Added a searchable current-item list with CSI division, revision, coding, and lifecycle filters.
- Added full enterprise item details covering descriptions, classification, UOM, optional specification, PSMM mappings, resources, productivity, code governance, revision history, and approval state.
- Added validated current-Draft editing for descriptive and classification fields and CSRF-protected lifecycle changes.
- Kept enterprise-code generation, new revisions, workflow transitions, and resource mutation read-only for their dedicated later phases.
- Verified that an empty optional specification does not block draft editing or alter the governed enterprise code.

## Phase 12 — Cost Build-Up / Unit Rate
- Added a searchable build-up list with material, labor, additions, final-rate, baseline, and completeness visibility.
- Added detailed component quantity, current rate, component amount, direct-cost, allowance, final-rate, and reconciliation calculations.
- Reproduced the formula from the supplied `vw_cost_item_resource_unit_rate` definition against authoritative base tables.
- Added validated Draft-only add/edit forms for material, labor, equipment, and allowance components with duplicate-resource checks.
- Flagged missing rates and non-monetized equipment explicitly and formatted monetary displays to two decimal places.
- Enforced separate unit-rate view/manage permissions and omitted hard-delete operations.

## Phase 13 — Rate Management and History
- Added unified current and historical material/labor rate tables with schedule effective dates and source fields.
- Added complete cost-item observation history with project, location, source, supplier, and validation context.
- Added immutable append forms for new material schedules/rates, labor schedules/rates, and cost-item observations.
- Preserved prior material/labor values while transactionally moving the current designation to newly appended records.
- Enforced separate rate view/manage permissions and exposed no rate edit or delete endpoints.

## Phase 14 — Governance Workflow
- Added technical-review and approval/publication queues with independent permission enforcement.
- Implemented controlled workflow actions with required comments, actors, timestamps, approval history, and audit events.
- Derived For Approval from the constrained approval-history model without changing the authoritative schema.
- Added coded-revision publication and atomic Published → Superseded / cloned Draft revision creation.
- Added an audit-trail screen and actor names on item approval history.

## Phase 15 — Project Master
- Added a searchable project register with project-type, region, and active-state filters.
- Added project detail pages covering classification, location, area, floors, dates, and linked cost-item rate observations.
- Added validated create/edit forms, unique-code protection, chronological date validation, and CSRF-protected active-state changes.
- Enforced separate project view/manage permissions and exposed no hard-delete endpoint.

## Phase 16 — BOQ Management Schema Gate
- Reconfirmed through live `information_schema` inspection that no BOQ, import, staging, or mapping structures exist.
- Documented the minimal normalized BOQ header, item, import-batch, and import-staging proposal.
- Defined validation, transactional import, error-reporting, file-safety, traceability, and Phase 17 boundary requirements.
- Wrote and executed no schema migration or BOQ implementation SQL pending explicit authorization.

## Phase 16 — BOQ Management Implementation
- Received authorization and created the approved BOQ header, item, import-batch, and import-staging tables.
- Added BOQ header/item maintenance with project/UOM integrity, validation, totals, status controls, and permission enforcement.
- Added safe CSV/XLSX upload parsing, staging, row-level validation/error reporting, and transactional Ready-batch import.
- Rejected an insecure spreadsheet dependency and used the runtime ZIP/XML extensions without formula or macro evaluation.

## Updated Schema Reconciliation (before Phase 17)
- Inspected the updated dump in an isolated database and reconciled 41 new tables, 14 columns, valid constraints, and reference data additively; no destructive dump statements were run.
- Expanded Reference Data to 46 allowlisted CRUD entities, added trade divisions and income classification rules, linked catalog cards to CRUD pages, and removed physical table-name subtitles.
- Added database-backed search and 100-row pagination for the 43,768-row Locations reference list.
- Updated Project, Rate, Material, Standard Cost Item, and Trade code paths for the new governed reference fields.
- Preserved the unresolved upstream targets `ref_standard_item_name` and `ref_uniformat_assembly` as documented gaps rather than inventing schema.
- Verified PHP syntax, schema/config coverage, affected HTTP pages, and forced-password route confinement with disposable accounts that were removed after testing.

## Phase 17 — BOQ-to-UCD Mapping
- Added candidate, selected-mapping, and append-only mapping-history tables tied to stable BOQ items and exact standard-item revisions.
- Added manual candidate search/add/disable/restore, selection/replacement, and Proposed/Confirmed/Rejected/reopen controls.
- Added mapping register/progress, BOQ item mapping screens, UOM-match prioritization, selected revision context, and mapping history.
- Enabled the existing `boq.map` permission and sidebar entry; users without it receive HTTP 403.
- Verified confirmation, rejection, reopen, history, and selected-candidate protection through disposable HTTP/database integration data.
- Reserved candidate source, similarity score, rank, and explanation fields for later explainable suggestions without implementing AI behavior.

## Actual BOQ Import and Mapping Reconciliation
- Analyzed the two supplied contractor workbooks and documented their sheet/header/hierarchy/pricing patterns.
- Updated XLSX import to select the detailed worksheet, detect multi-row BOQ headers, flatten section hierarchy, preserve real source rows, derive total rates, and retain unpriced scope.
- Added governed aliases for actual source UOM spellings while treating `lm` as linear meter rather than the database lumen code.
- Added batch reconciliation fields/reporting for selected sheet, parsed priced total, declared total, variance, and unpriced lines.
- Verified both real workbooks through stage, commit, and mapping-screen access with zero invalid rows; removed all verification data and uploaded test copies afterward.

## Phase 18 — Cost Benchmarking
- Added read-only comparisons across cost item, project, location, period, contractor/vendor, CSI division, and trade.
- Added filters for the available dimensions, UOM, date range, and validated-only observations.
- Added minimum, P25, median, average, P75, maximum, sample size, context coverage, date range, and underlying observation details.
- Prevented misleading mixed-UOM/currency aggregation and limited charts to one selected UOM.
- Exposed the existing `benchmarking.view` navigation/route and verified HTTP 403 for a role without access.
- Documented that all 276 current records are context-light reference baselines; no values were invented to fill missing project/location/period/vendor data.

## Phase 19 — Cost Intelligence
- Added read-only, deterministic standard-item suggestion ranking with term, phrase, UOM, confidence-band, and per-result explanations.
- Added comparable P25–P75 range signals, evidence-based confidence scoring, and 1.5 × IQR abnormal-rate flags.
- Added evidence-aware annual trends and explicit warnings when authoritative dates, projects, locations, or validations are absent.
- Linked BOQ mapping items to prefilled suggestions for authorized users without creating, selecting, or confirming mappings.
- Verified authorization, read-only database behavior, application lint, and health using disposable accounts that were removed.

## Phase 20 — Hardening and Release
- Added CSP and defensive browser/cache headers, externalized theme/dashboard data scripts, and denied HTTP access to repository, SQL, documentation, and operational-script artifacts.
- Made production configuration fail closed for missing base URL/encryption key, forced secure cookies, enabled error logging, supported trusted proxy configuration, and hid production backtraces.
- Hardened XLSX XML parsing against DTD/entities/network access while preserving both actual contractor workbook mappings.
- Reviewed authorization, CSRF/method enforcement, validation, audit integrity, indexes/query plans, DataTables usage, upload controls, and error handling without changing the authoritative schema.
- Added tested database backup/checksum and release-check automation plus security, performance, recovery, deployment, user, and release-checklist documentation.

## Post-Release Role Account Provisioning
- Added one active, single-role account for each built-in role that previously had no assigned user.
- Required all seven accounts to replace unique temporary passwords at first login and verified the forced-change route through HTTP authentication.
- Left the existing System Administrator account and role assignment unchanged.

## Post-Release Branding and Navigation
- Renamed the user-facing system to Project Nexus UCD while preserving stable database, route, cookie, and environment-variable identifiers.
- Removed the disabled PSMM sidebar placeholder; PSMM classification data remains available where implemented in governed item/reference screens.
- Reordered permitted sidebar sections per role so operational work appears first and supporting Master Cost Library/Reference Tables appear last.
- Changed the current host's default application URL to `http://172.30.128.49/ucd/` while retaining the `UCD_BASE_URL` deployment override.
- Declared the CodeIgniter URI/router core dependencies explicitly to prevent PHP 8.2 dynamic-property deprecation output while retaining PHP 7.4 syntax compatibility.
- Added a protected, git-ignored local database override and configured this host to connect as `nexuscandy` at `172.30.128.49`; environment variables still take precedence.
- Added confirmed permanent deletion for Draft/Validated BOQs with explicit transactional cleanup of dependent import and mapping records plus generated upload files.
- Added exact-code-confirmed deletion for unreferenced Projects; projects with BOQ, rate, source-document, or metric dependencies are blocked and must be deactivated instead.
- Expanded the Project Nexus UCD user guide into a role-aware operating manual covering every implemented module, governed workflow, deletion safeguard, common response, and support procedure.
- Updated each Standard Cost Item detail page to show its calculated Final Unit Rate in the Enterprise Item card instead of Lifecycle; retained the complete Material association table after review.

## Phase 21 — ML and Crew Productivity Design
- Audited live BOQ, mapping, crew, productivity, resource, revision, and permission structures without modifying the database.
- Defined manual versus crew-derived labor modes, the day-based crew derivation formula, lineage/snapshot requirements, stale/reapply behavior, and double-counting controls.
- Defined a guided Draft Standard Cost Item assembly flow combining materials with manual or crew-derived labor, equipment, allowances, and the existing Final Unit Rate formula.
- Designed a private Python ML service/worker boundary, immutable dataset/model lifecycle, deterministic fallbacks, evaluation gates, permission proposal, schema-impact proposal, and Phases 22–30 roadmap.
- Retained human mapping confirmation and all existing revision, validation, security, and database-governance constraints.

## Phase 22 — Crew Productivity Management
- Added additive build-up and derivation lineage tables for labor costing method, selected productivity/crew, stale state, and exact crew-member calculation snapshots.
- Added sourced, effective-dated, day-based productivity maintenance for current Draft Standard Cost Item revisions.
- Added crew-derived labor preview/application using member quantity × duration days ÷ output and existing current governed labor rates.
- Prevented labor double counting by locking derived rows until explicit Manual conversion; retained converted labor values while removing derivation lineage.
- Marked selected build-ups stale after productivity or crew changes and extended governed successor-revision cloning to retain remapped lineage.

## Phase 23 — Guided Standard Cost Item Draft Assembly
- Added a six-step creation workflow covering identity, governed classifications/scope, materials, Manual or Crew Derived labor, equipment/allowances, and reconciliation preview.
- Added server validation for active references, duplicates, exactly one primary material, positive quantities/day-based labor, valid dates, rate evidence warnings, and mixed-currency prevention.
- Added one-transaction creation of the internal UID/sequence, Draft revision 01, Ready-for-Code placeholder, resources, labor method/derivation lineage, and audit event.
- Preserved coding, approval, publication, BOQ mapping, equipment-rate, and hour-conversion boundaries.

## Phase 24 — ML Data and Governance Foundation
- Added additive dataset, version, record, feedback, asynchronous job, and model-registry metadata tables plus four role-assigned ML permission contracts.
- Added capability-specific immutable snapshots for reviewed BOQ extraction rows, confirmed/rejected mapping labels, and governed resource templates with source cutoff, split grouping, canonical JSON, and SHA-256 lineage.
- Added label review feedback, freeze gates, idempotent export jobs, a CLI JSONL worker, checksum-verified artifacts outside the web root, and export-before-approval enforcement.
- Added ML Governance screens while deliberately withholding model training, inference, registration actions, activation, rollback, automatic mapping, and authoritative business writes.

## Phase 25 — Reviewed ML-Assisted BOQ Extraction
- Added an optional, timeout-bounded private-service inference call over safe normalized BOQ rows, with active-model lineage and a strict `extraction-v1` response contract.
- Added extraction run, row-prediction, and append-only feedback tables plus accept/correct/reject review on the import batch screen.
- Kept deterministic parsing authoritative, recorded explicit no-confidence fallback reasons, revalidated applied values, and blocked commit until all proposals are reviewed.
- Enforced combined `boq.manage` and `ml.review` authorization and verified the complete flow using disposable data.

## Post-Phase 25 Encoding Compatibility
- Added a presentation-only repair for legacy double-encoded diameter and degree symbols while preserving correct UTF-8 content and leaving authoritative database rows unchanged.
- Verified authenticated Material views render `Ø` and `°` correctly and removed the disposable verification account.

## Post-Phase 25 Standard Item Register Detail
- Expanded the Standard Item column to show the unabridged standard description plus attribute class, work type, strength/grade, size/dimension, application, finish, and governed typed attribute values.
- Preserved one row per current revision by loading dynamic attributes separately, and verified built-in and disposable dynamic attributes through the authenticated register.
- Replaced the register's Coding Status column with Final Unit Rate using the same material, labor, and governed-allowance calculation as the detail page; removed the Coding Status filter.
- Added confirmation screens and CSRF-protected deletion for Draft Material, Manual Labor, Equipment, and Allowance build-up components; retained the crew-derived labor lock and updated Manual labor lineage transactionally.

## Post-Phase 25 Reference Catalog Expansion
- Added allowlisted CRUD for the live `ref_market_segment` dictionary and surrogate-keyed Project Type–Market Segment applicability hierarchy.
- Organized all 48 Reference Table cards into Classification and Standards, Resources and Attributes, Projects and Market Segments, Locations and Demographics, and Rates and Cost Governance groups without exposing table names.

## Post-Phase 25 Standard Cost Item Project-Market Structure
- Added required Project Type and Market Segment fields to Standard Cost Item revisions with a composite applicability foreign key.
- Backfilled all 371 existing revisions to Residential Subdivision (`RES-SUB`) / Socialized (`MKT-004`).
- Added dependent create/edit selection, server validation, register filtering, and item-detail visibility.
- Included both classification IDs in newly generated ML Resource Template snapshots so future training evidence retains its project-market context.

## Post-Phase 25 Process-Focused Sidebar and RBAC
- Limited Reference Tables and Material, Equipment, Labor, and Crew catalogs to System and UCD Administrators at both navigation and endpoint levels.
- Reorganized each operational role around its own Cost Item Development, Technical Review, Approval, Project Delivery, Cost Analysis, or Executive Insights process.
- Prevented role editing from restoring administrator-only catalog permissions to non-administrator roles.

## Localhost Configuration
- Changed the local base URL to `http://localhost/ucd/` and local MariaDB connection to `localhost`, user `root`, no password, database `nexus_ucd`.

## SCI-Independent Elemental Costing
- Added project-market/UniFormat elemental plans, element lines, scope applicability, normalized summaries, historical elemental-rate evidence, permissions, routes, sidebar access, and workflow actions.
- Kept SCI unit-rate build-ups separate and removed any implied SCI dependency from elemental screens and labels.
- Corrected Level 3 reporting joins that multiplied one elemental line by its Level 4 child count.
- Added Project Master Market Segment selection so linked projects can be checked against elemental plan scope.
