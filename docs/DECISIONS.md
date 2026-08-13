# Architecture Decisions

## DEC-001 — Preserve the Existing Database Baseline
Decision:
Treat the `nexus_ucd` schema as authoritative and reuse its tables, relationships, views, and procedures.

Reason:
The database is already designed and populated; Phase 0 is assessment-only.

Impact:
No schema mutation may occur without inspecting the live schema and obtaining authorization where required.

## DEC-002 — Use Standard CodeIgniter 3 MVC
Decision:
Use controllers for request orchestration, models for database work, and reusable views/layout partials for presentation.

Reason:
This matches the installed framework and the required project architecture.

Impact:
Business SQL will not be placed in controllers, and page layouts will not be duplicated.

## DEC-003 — Use Existing Read Models Where Suitable
Decision:
Prefer the supplied database views for read-heavy screens and summaries after verifying them against the live schema.

Reason:
The dump already provides complete UCD, resource-rate, master-data, classification, and diagnostic views.

Impact:
Future modules can avoid duplicating complex aggregation logic in application code.

## DEC-004 — Use Environment-Overridable Local Configuration
Decision:
Use `UCD_BASE_URL`, `UCD_ENCRYPTION_KEY`, and `UCD_DB_*` environment variables with XAMPP-compatible development defaults.

Reason:
The same codebase must run under the current XAMPP path and Laragon or deployment environments without committed machine-specific credentials.

Impact:
The current shared host uses `http://172.30.128.49/ucd/`; other environments override URLs, credentials, and secrets outside source control.

## DEC-005 — Vendor Pinned AdminLTE Runtime Assets
Decision:
Serve AdminLTE 4.0.0, Bootstrap 5.3.8 JavaScript, Bootstrap Icons 1.13.1, OverlayScrollbars 2.11.0, and Popper 2.11.8 from local `assets` paths.

Reason:
Phase 2 requires local AdminLTE integration, and Node/npm is unavailable on the workstation.

Impact:
The application has no CDN runtime dependency. Asset upgrades must be deliberate and recorded in `assets/VERSIONS.md`.

## DEC-006 — Compose Pages Through Shared Layout Partials
Decision:
Use `layouts/main.php` to compose the standard AdminLTE header, navbar, sidebar, content header, footer, and scripts around a page content view.

Reason:
This avoids duplicated application-shell markup and gives future modules one consistent responsive layout.

Impact:
Controllers provide page metadata and a controlled content-view name to the main layout.

## DEC-007 — Add a Minimal Authentication Identity Table
Decision:
Add `app_user` with login identity, password hash, active state, forced-password-change state, lockout counters, and authentication timestamps.

Reason:
The authoritative baseline has no user/security table, while Phase 3 requires secure authentication. Roles and permissions are explicitly deferred to Phase 4.

Impact:
Authentication can operate without prematurely designing RBAC. Phase 4 tables can reference `app_user.user_id`.

## DEC-008 — Enforce Authentication in a Base Controller
Decision:
Require protected controllers to extend `Authenticated_Controller`, which validates both the session and current active database user.

Reason:
Every protected endpoint needs a consistent server-side guard; menu visibility alone is not authorization.

Impact:
Future protected controllers inherit authentication enforcement and forced-password-change gating by default.

## DEC-009 — Provision Users Only Through CLI Until User Management
Decision:
Provide `Auth_cli`, restricted to CLI requests, with initial user attributes and password supplied through temporary `UCD_INITIAL_*` environment variables.

Reason:
No public registration is required, and environment inputs keep strong passwords out of URI routing.

Impact:
Administrators can bootstrap users without exposing a web setup endpoint. Full user management remains a later module.

## DEC-010 — Use Many-to-Many Database-Backed RBAC
Decision:
Use `app_role`, `app_permission`, `app_role_permission`, and `app_user_role`, with permissions resolved from active database assignments on each protected request.

Reason:
Users may need multiple roles, roles need reusable permission sets, and access changes must take effect without relying on stale session permissions.

Impact:
Controller guards and menu visibility use the same current permission set. Assignment changes apply on the next request.

## DEC-011 — Treat Permission Codes as Application Contracts
Decision:
Seed permission codes in Phase SQL and expose them read-only in the UI; role records and assignments remain manageable.

Reason:
Controllers and navigation reference permission codes directly. Arbitrary code edits would silently break authorization contracts.

Impact:
New permissions are introduced alongside the application feature that enforces them, then assigned through role management.

## DEC-012 — Preserve a System Administrator Safety Floor
Decision:
Keep `SYS_ADMIN` active with every active permission and prevent removal of the final System Administrator user assignment.

Reason:
An RBAC configuration must not allow administrators to lock every user out of security administration.

Impact:
The `SYS_ADMIN` permission matrix is read-only in the UI, and user-role updates enforce the last-administrator safeguard.

## DEC-013 — Query Base Tables for Dashboard Aggregates
Decision:
Build Phase 5 dashboard aggregates with CodeIgniter Query Builder against authoritative base tables, and load pinned Chart.js only on dashboard pages.

Reason:
The 17 intended database views are currently empty placeholder tables and cannot be replaced without separate destructive-SQL authorization.

Impact:
The dashboard is operational without altering the schema. Its aggregate queries can be migrated to verified views later without changing the page contract.

## DEC-014 — Use an Allowlisted Reference CRUD Registry
Decision:
Define the 16 supported reference entities, fields, relationships, labels, validation limits, and duplicate keys in one server-side registry, then reuse one controller, model, and view set.

Reason:
Reference tables share a CRUD shape but expose different authoritative columns and parent relationships. An allowlist provides reuse without permitting request-controlled table or column names.

Impact:
New reference entities require an explicit registry entry. Phase 6 supports add, edit, and active/inactive status changes but intentionally provides no hard-delete action.

## DEC-015 — Keep Material Children Read-Only in Phase 7
Decision:
Manage `material_master` records in Phase 7 while presenting variants, current rates, schedules, and rate history as read-only material details.

Reason:
Phase 7 requires material CRUD and rate visibility, while rate history is append-oriented and no separate variant/rate maintenance workflow is specified for this phase.

Impact:
Material identity and references can be maintained without overwriting historical rates. Dedicated variant and rate mutation workflows remain outside Phase 7.

## DEC-016 — Reuse Master-Table Behavior and Surface the Equipment Rate Gap
Decision:
Use one shared DataTables/filter module for material and equipment master screens, and explicitly report that equipment rate schedules/history are unavailable.

Reason:
Phase 8 must follow the stabilized Material Master pattern without duplicating client behavior, while the authoritative schema provides no governed equipment-rate structures.

Impact:
Equipment CRUD and cost-item usage are operational without fabricated rates or columns. Future master modules can reuse the same declarative filter hooks.

## DEC-017 — Keep Governed Labor Rates Read-Only in Phase 9
Decision:
Manage labor craft identity, governed category, and active status while presenting labor schedules, rate history, component amounts, source aliases, and cost-item usage as read-only context.

Reason:
Phase 9 requires labor craft management and supported rate-history views, while historical rates and their component breakdowns are append-oriented governed data.

Impact:
Labor records can be maintained without overwriting source-derived rate history. A future rate-management workflow can add controlled append operations separately.

## DEC-018 — Derive Crew Cost from Labor Master Rates
Decision:
Store only crew-to-labor references, member quantities, and source role labels; calculate daily crew cost from each member's current governed labor total rate.

Reason:
Crew records must reference Labor Master rather than duplicate labor craft or rate data, and the schema provides no crew-rate column.

Impact:
Crew costs automatically reflect current labor rates. Member removal is not exposed because `crew_member` has no inactive flag and destructive SQL has not been explicitly authorized.

## DEC-019 — Separate Definition Editing from Code and Workflow Governance
Decision:
Allow managers to edit descriptive and classification fields only on the current Draft revision, while treating enterprise codes, revision creation, coding status, approval state, and resource associations as read-only in Phase 11.

Reason:
The schema and stored procedures show governed coding and revision workflows, and dedicated later phases cover cost build-up and governance. Phase 11 must not invent transition or generation rules.

Impact:
Standard item screens are complete and safe for current-definition maintenance. An empty optional specification does not block draft editing or invalidate an existing enterprise code.

## DEC-020 — Reproduce the Supplied Unit-Rate Formula
Decision:
Calculate material cost as quantity per item unit × current material-variant rate, labor cost as labor days per item unit × current governed labor total rate, and add the three defined allowance amounts to obtain the final unit rate.

Reason:
This exactly matches the authoritative `vw_cost_item_resource_unit_rate` definition in the supplied dump while the local view remains a placeholder table.

Impact:
Waste percentage is displayed but not multiplied into the formula. Equipment quantities are visible but non-monetized because no governed equipment-rate table exists. Draft components support add/edit but no hard delete.

## DEC-021 — Append Rates Through New Effective Schedules
Decision:
Append material and labor changes by atomically creating a new effective schedule and rate record, then moving the `is_current` designation from the prior record without editing its value. Append cost-item observations directly with their supported context.

Reason:
Material/labor uniqueness is resource plus schedule, and Phase 13 explicitly prohibits overwriting historical rate records.

Impact:
Prior values and timestamps remain immutable. No rate-edit or delete endpoint exists. Newly appended cost-item observations start `PENDING` and unvalidated for later governance.

## DEC-022 — Derive For Approval from Approval History
Decision:
Represent For Approval with the latest `TECHNICAL_REVIEW / APPROVED` history action while retaining the authoritative `FOR_REVIEW` revision status. Approver acceptance sets `APPROVED`; publication then sets `PUBLISHED`.

Reason:
The schema check constraint does not permit `FOR_APPROVAL`, while the approval-history table explicitly supports technical-review stages and approved actions.

Impact:
The workflow matches the required user-facing stages without schema changes. Publication requires a governed enterprise code in `CODED` state. Creating a revision atomically supersedes the published revision and clones its governed build-up into a new current Draft.

## DEC-023 — Keep Project Rates in Append-Only History
Decision:
Maintain project identity, classification, location, scale, schedule, and active state in Project Master while displaying its linked `cost_item_rate_history` observations read-only.

Reason:
The existing project structure is authoritative, and Phase 13 already owns append-only creation of cost-item rate observations.

Impact:
Project records support create/edit and reversible active-state changes without hard deletion. Historical rate values are not edited from Project Master.

## DEC-024 — Gate BOQ Implementation on a Four-Table Schema
Decision:
Propose separate BOQ header, BOQ item, import batch, and import staging tables. Derive the import error report from staging validation fields and reserve the stable BOQ item identifier for Phase 17 mappings.

Reason:
The live authoritative schema has no BOQ structures. These four responsibilities are the minimum normalized foundation for headers, priced lines, CSV/XLSX processing, validation, staging, and error reporting.

Impact:
The authorized four-table foundation is implemented. Mapping fields/tables remain excluded from Phase 16.

## DEC-025 — Parse XLSX Locally Without Formula Evaluation
Decision:
Use native CSV parsing and a small read-only ZIP/XML XLSX parser for the first worksheet. Do not evaluate formulas or accept macro-enabled workbooks.

Reason:
Composer blocked the PHP 7.4-compatible PhpSpreadsheet release because of active security advisories. The runtime already provides ZIP and XML extensions.

Impact:
CSV/XLSX imports remain dependency-free and limited to tabular values. Uploads are extension-, MIME-, structure-, size-, and row-limited and stored under an application cache directory with generated names.

## DEC-026 — Reconcile the Updated Dump Additively
Decision:
Inspect the 12-Aug-2026 dump in an isolated database, then apply only missing tables, columns, valid constraints, and reference data to the live schema. Extend completed modules and the allowlisted reference registry without starting Phase 17.

Reason:
The supplied dump contains destructive `DROP` and `DELETE` statements and two foreign-key targets that it does not define. Direct execution would violate the project database rules and could destroy live data.

Impact:
Live data and the 17 placeholder tables remain intact. Forty-six maintainable references have generic CRUD; composite applicability/closure relationships are not forced into the single-key controller. `ref_standard_item_name` and `ref_uniformat_assembly` remain explicit upstream schema gaps.

## DEC-027 — Map BOQ Items to Exact Governed Revisions
Decision:
Store candidate mappings against `standard_cost_item_revision.cost_item_revision_id`, then keep one selected candidate per `boq_item_id` with a controlled Proposed/Confirmed/Rejected state and append-only history.

Reason:
A stable cost-item identity can receive successor revisions. Mapping to the exact revision preserves the definition reviewed for the BOQ and prevents later revision changes from silently altering historical meaning.

Impact:
Phase 17 starts with manual candidates and confirmation. Candidate source, score, rank, and explanation fields support later suggestion engines, but automated processes may not select or confirm mappings. A composite candidate/item foreign key prevents cross-line selection.

## DEC-028 — Normalize Actual BOQ Workbooks Before Mapping
Decision:
For recognized XLSX BOQs, inspect every worksheet, select the one yielding the most valid item rows, detect the header, flatten its C-F hierarchy, and normalize detail lines into Phase 16 staging. Preserve unpriced lines and reconcile parsed priced totals against the workbook's declared total.

Reason:
The supplied contractor files are presentation workbooks rather than row-1 flat imports. They include summary sheets, merged/multi-row headers, split material/labor pricing, owner-supplied scope, and inconsistent but repeatable UOM labels.

Impact:
Mapping receives actual detail lines rather than summary categories. UOM matching uses an explicit alias allowlist, including `lm` as linear meter in this source context. Cached formula results are used without evaluating formulas, and material total differences are reported instead of silently adjusted.

## DEC-029 — Benchmark Authoritative History Without Mixing Units
Decision:
Calculate benchmarking statistics directly from `cost_item_rate_history`, group by the selected business dimension plus UOM and currency, and only chart results when one UOM is selected.

Reason:
Rates for pieces, linear meters, and other units are not numerically comparable. A separate analytics table would duplicate append-only source history without a current need.

Impact:
Benchmarking remains read-only and traceable to individual observations. Project, location, period, contractor, currency, and validation gaps are shown explicitly and will populate naturally as Rate Management receives governed history.

## DEC-030 — Keep Cost Intelligence Explainable and Read-Only
Decision:
Rank mapping suggestions with deterministic term/phrase/UOM signals, calculate comparable rate ranges and IQR outliers from authoritative history, and interpret evidence quality through `ref_confidence_band`. Never persist or automatically apply an intelligence result.

Reason:
Governed master data and BOQ mappings require accountable human decisions. Current baseline rates also lack the project, location, date, and validation coverage needed for high-confidence trend or benchmark claims.

Impact:
Every candidate exposes its score components, ranges expose their confidence basis, and weak evidence is labeled for review. Users must use the existing controlled mapping workflow to create/select/confirm a mapping.

## DEC-031 — Fail Closed for Production and Protect Release Artifacts
Decision:
Require explicit production base URL/encryption-key configuration, secure cookies, defensive response headers, no inline executable scripts, protected repository/support files, safe XML parsing, and a repeatable release/backup verification process.

Reason:
Development defaults and web-readable SQL/documentation are inappropriate for a deployed governed-cost system. Release controls must be testable and must not depend solely on operator memory.

Impact:
Misconfigured production startup stops early, dynamic pages enforce a self-hosted-script CSP, sensitive file requests return 403, errors are logged without browser backtraces, and deployment requires release/backup checklists. Runtime modernization and a universal audit schema remain explicit future decisions.

## DEC-032 — Separate ML Computation from Governed Application Actions
Decision:
Run future extraction, ranking, training, and inference in a private Python service/worker while CodeIgniter remains authoritative for authorization, deterministic validation, workflow transitions, business persistence, and audit. Preserve deterministic/manual fallback and require human confirmation of mappings.

Reason:
PHP 7.4 is not an appropriate training runtime, and model output is probabilistic. The existing governed workflow must remain usable during model failure and must validate every suggested identifier and commercial value.

Impact:
Training will be asynchronous against immutable approved dataset exports. Model artifacts are versioned and checksum-verified outside the web root. The ML service cannot write authoritative business tables directly.

## DEC-033 — Expand Crews into One Traceable Labor Build-Up
Decision:
Treat materials, labor, equipment, allowances, and productivity as sibling resources of a Standard Cost Item revision. A new build-up uses either manual labor or crew-derived labor. Crew-derived labor snapshots member quantities and expands them into `cost_item_labor`; it is not costed again as a separate crew total.

Reason:
The existing unit-rate formula costs labor days per item unit and does not use `crew_quantity` as another multiplier. Without explicit derivation lineage and one selected costing method, crew application could double-count labor or allow later crew changes to alter historical meaning.

Impact:
Day-based derivation uses member quantity × duration days ÷ output quantity. Hour conversion remains unavailable until hours per workday is governed. Existing unclassified build-ups remain legacy manual until reviewed, and Published revisions are never regenerated in place.
