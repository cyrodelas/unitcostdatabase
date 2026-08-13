# Nexus UCD Web-Based System — Codex Master Prompt

## 1. Project Context

Build a **Unit Cost Database (UCD) Web-Based System** using the following stack:

- Backend: **CodeIgniter 3**
- Database: **MySQL**
- Existing database: `nexus_ucd`
- Admin Template: **AdminLTE 4**
- UI: Bootstrap 5.3
- Tables: DataTables
- Alerts/Confirmations: SweetAlert2
- Enhanced Selects: Select2
- Charts: Chart.js
- Local Development: Laragon

The existing MySQL database schema is already prepared and must be treated as the baseline.

Do **not** recreate, redesign, drop, rename, or migrate existing UCD tables unless explicitly instructed.

The database SQL dump is:

`/mnt/data/nexus_ucd.sql`

Before implementation, inspect the existing schema and reuse existing tables, relationships, and views.

---

# 2. Critical Working Rules

## 2.1 Work by Phase Only

Never implement the entire system in one response/run.

Only execute the **current requested phase**.

At the end of each phase:

1. Update the project markdown files.
2. List completed items.
3. List files created/changed.
4. List database changes, if any.
5. List tests performed.
6. List unresolved issues.
7. State the recommended next phase.
8. STOP.

Do not automatically continue to the next phase.

Wait for the instruction:

`PROCEED TO NEXT PHASE`

---

## 2.2 Token / Context Optimization

Use persistent markdown files as the project memory.

Do not repeatedly reread or restate the entire project.

At the beginning of a new task, read only the markdown files required for that task.

Do not output full source files unless:
- creating the file for the first time;
- a full rewrite is required; or
- explicitly requested.

For modifications:
- inspect the existing file;
- edit only the required sections;
- avoid rewriting unaffected code.

Keep terminal output concise.

Do not dump large database tables or SQL results into the response.

---

# 3. Required Project Markdown Files

Create this folder in the project root:

```text
/docs
```

Create and maintain the following files.

## `/docs/PROJECT_CONTEXT.md`

Permanent project information only.

Include:

- project name;
- technology stack;
- environment;
- architectural approach;
- database name;
- database rules;
- selected libraries;
- coding conventions;
- important permanent decisions.

Do not use this file as a work log.

---

## `/docs/DB_REFERENCE.md`

Compact reference for the existing `nexus_ucd` database.

Include:

- table name;
- purpose;
- primary key;
- important foreign keys;
- important business fields;
- useful views;
- major relationships.

Do not copy complete CREATE TABLE statements.

Example:

```md
### standard_cost_item
Purpose: Enterprise standard cost item master.
PK: standard_cost_item_id
Important fields:
- enterprise_code
- standard_description
- uom_id
- status
Relationships:
- references UOM
- connected to cost resources
```

Update this only when database understanding changes.

---

## `/docs/MODULE_MAP.md`

Track system modules and implementation status.

Use:

```md
| Module | Status | Controller | Model | Main Views | Notes |
|---|---|---|---|---|---|
| Authentication | Planned | | | | |
| Dashboard | Planned | | | | |
```

Allowed status:

- Planned
- In Progress
- Completed
- Blocked

---

## `/docs/PHASE_STATUS.md`

This is the main continuation file.

Keep it short.

Template:

```md
# Current Phase
Phase X — Name

## Completed
- ...

## In Progress
- ...

## Pending
- ...

## Files Changed
- ...

## Database Changes
- None

## Issues
- None

## Next Recommended Step
- ...
```

Overwrite/update this file after every phase.

Do not accumulate a long historical log here.

---

## `/docs/DECISIONS.md`

Record only important design decisions.

Format:

```md
## DEC-001 — Use AdminLTE 4
Decision:
Use AdminLTE 4 as the base administrative UI.

Reason:
Bootstrap 5 support and suitable for CI3 CRUD applications.

Impact:
Shared layouts and components follow AdminLTE 4 conventions.
```

Do not record trivial implementation details.

---

## `/docs/ROUTES.md`

Maintain a concise route/module reference.

Example:

```md
| URL | Controller/Method | Access |
|---|---|---|
| /login | Auth/login | Public |
| /dashboard | Dashboard/index | Authenticated |
```

---

## `/docs/CHANGELOG.md`

Record one compact entry per completed phase.

Do not include code.

Example:

```md
## Phase 1
- Initialized CodeIgniter 3.
- Connected nexus_ucd.
- Integrated AdminLTE 4.
```

---

# 4. CodeIgniter 3 Architecture

Use standard CodeIgniter 3 MVC.

Suggested structure:

```text
application/
├── controllers/
├── models/
├── views/
│   ├── layouts/
│   ├── auth/
│   ├── dashboard/
│   ├── standard_cost_item/
│   ├── materials/
│   ├── equipment/
│   ├── labor/
│   ├── crews/
│   ├── rates/
│   ├── projects/
│   ├── boq/
│   ├── governance/
│   └── admin/
├── helpers/
├── libraries/
└── config/

assets/
├── adminlte/
├── css/
├── js/
├── plugins/
└── images/

docs/
```

Use reusable partials:

```text
application/views/layouts/
├── header.php
├── navbar.php
├── sidebar.php
├── content_header.php
├── footer.php
└── scripts.php
```

Do not duplicate the AdminLTE layout in individual pages.

---

# 5. Development Standards

## Controllers

Controllers should:

- receive requests;
- validate input;
- enforce access;
- call models/services;
- return views/JSON.

Do not place large SQL queries in controllers.

---

## Models

Models should handle:

- database access;
- reusable queries;
- transactions where required.

Use CodeIgniter Query Builder when practical.

Use existing database views for read-heavy screens where appropriate.

---

## Views

Views should contain presentation logic only.

Use:

- AdminLTE 4;
- Bootstrap 5;
- DataTables;
- Select2;
- SweetAlert2.

Avoid large PHP business logic inside views.

---

## JavaScript

Module-specific scripts should be stored separately where practical.

Example:

```text
assets/js/modules/material-master.js
```

Do not place hundreds of lines of JavaScript directly in PHP views.

---

## Security

Apply:

- server-side validation;
- XSS-safe output;
- CSRF protection;
- session validation;
- role/permission checks;
- parameterized/Query Builder database access;
- password hashing;
- authorization on both pages and AJAX endpoints.

Never rely only on hidden buttons for security.

---

# 6. Existing Database Rule

The existing schema is authoritative.

Before adding a table or field:

1. Search the existing schema.
2. Search existing views.
3. Confirm the requirement cannot be fulfilled using existing structures.
4. Document the reason in `/docs/DECISIONS.md`.
5. Only then propose the database change.

Do not execute destructive SQL without explicit approval.

Never automatically run:

```sql
DROP TABLE
DROP DATABASE
TRUNCATE
```

Do not rename existing columns without explicit approval.

---

# 7. Application Modules

Target modules:

## Core
- Authentication
- Dashboard
- User Management
- Role and Permission Management

## Master Cost Library
- Standard Cost Items
- Material Master
- Equipment Master
- Labor Master
- Crew Master

## Cost Management
- Unit Rate Build-Up
- Material Rates
- Labor Rates
- Equipment Rates
- Rate History

## Classification / Reference
- PSMM
- Work Sections
- Cost Categories
- UOM
- Other governed reference tables

## Projects
- Project Master
- BOQ
- BOQ Import
- BOQ Mapping

## Governance
- Draft
- Review
- Approval
- Publish
- Revision History
- Audit Trail

## Cost Intelligence
- Cost Benchmarking
- Rate Comparison
- Cost Trends
- Mapping Suggestions

Do not implement all modules immediately.

---

# 8. Implementation Phases

## Phase 0 — Repository and Database Assessment

Objectives:

- inspect project directory;
- inspect `nexus_ucd.sql`;
- identify existing tables/views;
- identify current CI3 state;
- create `/docs`;
- create project markdown reference files;
- identify schema gaps without changing schema.

Deliverables:

- PROJECT_CONTEXT.md
- DB_REFERENCE.md
- MODULE_MAP.md
- PHASE_STATUS.md
- DECISIONS.md
- ROUTES.md
- CHANGELOG.md

No feature development yet.

STOP after assessment.

---

## Phase 1 — CodeIgniter 3 Base Application

Objectives:

- initialize/verify CI3;
- configure Laragon-compatible base URL;
- configure MySQL connection;
- configure autoload;
- configure sessions;
- enable CSRF;
- configure routes;
- create common helpers if required;
- verify connection to `nexus_ucd`.

Deliverable:

A working CI3 base application connected to the existing database.

STOP.

---

## Phase 2 — AdminLTE 4 Integration

Objectives:

- integrate AdminLTE 4 locally;
- create reusable layout partials;
- implement navbar;
- implement sidebar;
- implement footer;
- configure assets;
- implement responsive content wrapper.

No business modules yet.

STOP.

---

## Phase 3 — Authentication

Objectives:

- inspect whether user/security tables already exist;
- reuse them if suitable;
- otherwise propose minimal authentication tables;
- implement login;
- logout;
- session security;
- password hashing;
- access guard.

Do not implement detailed RBAC yet unless needed.

STOP.

---

## Phase 4 — Roles and Permissions

Objectives:

- role master;
- permissions;
- role-permission assignment;
- user-role assignment;
- menu visibility;
- controller authorization.

Suggested initial roles:

- System Administrator
- UCD Administrator
- Cost Engineer / QS
- Reviewer
- Approver
- Project User
- Data Analyst
- Executive / Viewer

STOP.

---

## Phase 5 — Dashboard

Use existing database views where possible.

Suggested KPI cards:

- Standard Cost Items
- Materials
- Equipment
- Labor Crafts
- Crews
- Published Items
- Items for Review
- Items for Approval

Add only useful summary charts.

Do not implement heavy analytics yet.

STOP.

---

## Phase 6 — Reference Data Management

Build reusable CRUD pattern for reference tables.

Requirements:

- DataTables;
- modal or dedicated forms;
- validation;
- active/inactive status;
- duplicate checking;
- audit fields where supported.

Once the reusable reference CRUD pattern is stable, reuse it.

STOP.

---

## Phase 7 — Material Master

Implement:

- list;
- search;
- filters;
- view;
- add;
- edit;
- status;
- references;
- rate visibility where relevant.

Use existing material master tables/views.

STOP.

---

## Phase 8 — Equipment Master

Follow the same stabilized pattern as Material Master.

Avoid duplicating code where reusable components can be applied.

STOP.

---

## Phase 9 — Labor Master

Implement labor craft records using existing governed labor data.

Include rate history/read views where supported.

STOP.

---

## Phase 10 — Crew Master

Implement:

- crew header;
- crew composition;
- labor craft references;
- quantity;
- calculated cost where applicable.

Crew records should reference Labor Master rather than duplicate labor craft data.

STOP.

---

## Phase 11 — Standard Cost Item Master

Implement enterprise standard cost item screens.

Include:

- enterprise code;
- standard description;
- classification;
- UOM;
- specification where available;
- resource associations;
- status;
- revision;
- approval state.

Do not block enterprise codes merely because optional specification is empty unless the existing governance rule requires it.

STOP.

---

## Phase 12 — Cost Build-Up / Unit Rate

Implement resource-based build-up:

```text
Standard Cost Item
   ├── Material
   ├── Labor
   └── Equipment
```

Calculate:

- resource quantity;
- resource rate;
- component amount;
- total direct cost;
- applicable additions where defined by existing schema/rules;
- final unit rate.

All monetary displays should default to **2 decimal places**.

STOP.

---

## Phase 13 — Rate Management and History

Implement:

- current rates;
- historical rates;
- effective dates;
- revision history;
- source/context fields already supported by schema.

Never overwrite historical rate records.

STOP.

---

## Phase 14 — Governance Workflow

Implement:

```text
Draft
→ For Review
→ For Approval
→ Published
→ Superseded / Revised
```

Include:

- reviewer comments;
- approver comments;
- timestamps;
- user;
- status transitions;
- audit trail.

STOP.

---

## Phase 15 — Project Master

Implement project master maintenance using the existing project structure.

STOP.

---

## Phase 16 — BOQ Management

Before implementing, inspect whether BOQ tables already exist.

If missing, propose the minimal required normalized schema before writing migrations/SQL.

Functions:

- BOQ header;
- BOQ item;
- Excel/CSV import;
- validation;
- import staging;
- error report.

STOP.

---

## Phase 17 — BOQ-to-UCD Mapping

Implement:

```text
BOQ Item
→ Candidate Standard Cost Items
→ Selected Mapping
→ Mapping Status
```

Begin with manual mapping.

Prepare architecture for similarity/AI suggestions later.

STOP.

---

## Phase 18 — Cost Benchmarking

Implement comparisons by available dimensions such as:

- cost item;
- project;
- location;
- period;
- contractor/vendor where data exists;
- classification.

Use historical UCD data.

STOP.

---

## Phase 19 — Cost Intelligence

Only after stable historical data exists.

Potential features:

- suggested standard item mapping;
- similarity scores;
- confidence scores;
- abnormal rate detection;
- rate trends;
- recommended benchmark ranges.

AI recommendations must be explainable and must not automatically modify governed master data.

STOP.

---

## Phase 20 — Hardening and Release

Perform:

- security review;
- validation review;
- permission review;
- performance optimization;
- index review;
- DataTables server-side optimization;
- error handling;
- audit verification;
- backup procedure;
- deployment documentation;
- user documentation.

STOP.

---

# 9. Standard Phase Execution Prompt

When asked to execute a phase, follow this exact process:

```text
1. Read:
   - /docs/PROJECT_CONTEXT.md
   - /docs/PHASE_STATUS.md
   - /docs/MODULE_MAP.md

2. Read DB_REFERENCE.md only if the phase touches database entities.

3. Inspect only the source files relevant to the current phase.

4. Implement only the current phase.

5. Test the implementation.

6. Update:
   - PHASE_STATUS.md
   - MODULE_MAP.md
   - ROUTES.md if applicable
   - DECISIONS.md if a real architecture decision occurred
   - DB_REFERENCE.md only if database understanding changed
   - CHANGELOG.md

7. Respond with a concise summary.

8. STOP.
```

---

# 10. Response Format After Each Phase

Keep responses concise:

```md
## Phase X Complete

### Completed
- ...

### Files Changed
- ...

### Database
- No schema changes.

### Tests
- ...

### Issues
- None.

### Next
Phase X+1 — ...

Waiting for `PROCEED TO NEXT PHASE`.
```

Do not paste unchanged source code.

---

# 11. Error Handling Rule

If an error occurs:

1. Diagnose the specific error.
2. Inspect the affected file/table.
3. Fix only the relevant issue.
4. Test again.
5. Update PHASE_STATUS.md.
6. Do not restart or regenerate unrelated modules.

For database errors such as:

```text
Unknown column
Missing table
Foreign key failure
```

inspect the real `nexus_ucd` schema before modifying application code.

Never invent a column that is not confirmed in the schema.

---

# 12. UI Standards

Use a consistent UCD enterprise interface.

## Sidebar

```text
Dashboard

Master Cost Library
- Standard Cost Items
- Materials
- Equipment
- Labor
- Crews

Cost Management
- Unit Rate Build-Up
- Material Rates
- Labor Rates
- Equipment Rates
- Rate History

Classification
- PSMM
- Work Sections
- Cost Categories
- Units of Measure

Projects
- Project Master
- BOQ
- BOQ Mapping

Governance
- For Review
- For Approval
- Published Items
- Revisions
- Audit Trail

Cost Intelligence
- Benchmarking
- Cost Trends
- Rate Comparison
- Mapping Suggestions

Administration
- Users
- Roles & Permissions
- Reference Tables
- System Settings
```

Hide menu entries that the current user is not authorized to access.

---

# 13. Table Standards

For large master tables use DataTables server-side processing when appropriate.

Common functions:

- search;
- pagination;
- sorting;
- filtering;
- export where needed;
- column visibility where useful;
- status badges;
- row actions.

Do not load very large tables entirely into the browser.

---

# 14. Formatting Standards

Currency:

```text
₱ 1,234.56
```

Default decimals:

```text
2 decimal places
```

Percentages:

```text
95.00%
```

Dates:

```text
dd-MMM-yyyy
```

Datetime:

```text
dd-MMM-yyyy hh:mm AM/PM
```

Preserve Unicode symbols correctly, including:

```text
Ø
```

Use UTF-8 / utf8mb4 throughout.

---

# 15. First Instruction to Codex

Start with:

```text
EXECUTE PHASE 0 ONLY.

Inspect the repository and `/mnt/data/nexus_ucd.sql`.

Do not write business features yet.

Create the `/docs` project memory files described in this master prompt.

Build a compact database reference instead of copying the entire SQL schema.

Identify any application-level gaps such as authentication, roles, permissions, or BOQ structures, but do not alter the database.

Update PHASE_STATUS.md and stop after Phase 0.
```

---

# 16. Continuation Commands

Use short commands after the master prompt has been established.

Examples:

```text
PROCEED TO NEXT PHASE
```

```text
EXECUTE PHASE 5 ONLY
```

```text
FIX CURRENT PHASE ONLY:
DataTables returns HTTP 500 on Material Master.
Read PHASE_STATUS.md and relevant files first.
```

```text
REVIEW CURRENT PHASE ONLY.
Do not modify code unless a defect is found.
```

This avoids repeatedly sending the full project specification to Codex.
