# Module Map

| Module | Status | Controller | Model | Main Views | Notes |
|---|---|---|---|---|---|
| Base Application | Completed | `Welcome` | | `welcome_message` | Database/session autoload, CSRF, environment-aware config, and health route complete |
| AdminLTE Layout | Completed | `Welcome` | | `layouts/*`, `welcome_message` | AdminLTE 4.0.0 local assets, responsive shell, shared partials, and theme toggle complete |
| Authentication | Completed | `Auth`, `Account`, `Auth_cli` | `User_model` | `auth/login`, `account/*` | Login/logout, password change, lockout, session guard, and CLI provisioning complete |
| Roles and Permissions | Completed | `Roles`, `Permissions`, `User_roles` | `Rbac_model` | `roles/*`, `permissions/index`, `user_roles/*` | 8 system roles, process-specific sidebar workflows, administrator-only reference/resource catalogs, assignment guards, and endpoint enforcement |
| Dashboard | Completed | `Dashboard` | `Dashboard_model` | `dashboard/index` | Permission-aware KPIs, revision-status chart, resource coverage, and operational snapshot |
| Reference Data | Completed | `References` | `Reference_model` | `references/*` | Allowlisted CRUD for 49 maintainable entities; catalog uses six business groups and omits table names; large Locations data uses database paging/search |
| Material Master | Completed | `Materials` | `Material_model` | `materials/*` | Filtered master list, governed attribute subject class, CRUD/status, variants, current rates, and rate history |
| Equipment Master | Completed | `Equipment` | `Equipment_model` | `equipment/*` | Filtered list, detail/group context, CRUD/status, cost-item usage; rate gap surfaced explicitly |
| Labor Master | Completed | `Labor` | `Labor_model` | `labor/*` | Filtered craft list, governed category CRUD/status, current/history rates, components, aliases, and usage |
| Crew Master | Completed | `Crews` | `Crew_model` | `crews/*` | Header CRUD/status, labor-referenced composition add/edit, calculated daily cost, and productivity usage |
| Standard Cost Items | Completed | `Standard_cost_items` | `Standard_cost_item_model` | `standard_cost_items/*` | Revision-level governed Project Type / Market Segment classification, filtered current list with full descriptions/attributes and Final Unit Rate, resources, revisions, approval state, controlled draft edit/lifecycle |
| Unit Rate Build-Up | Completed | `Unit_rates` | `Unit_rate_model` | `unit_rates/*` | Component quantities/rates/amounts, authoritative totals/additions, baseline reconciliation, and Draft add/edit/confirmed-delete maintenance with crew-derived labor protection |
| Rate Management | Completed | `Rates` | `Rate_model` | `rates/*` | Append-only workflows with governed location, source type, rate basis, price period, and validation references |
| Governance Workflow | Completed | `Governance` | `Governance_model` | `governance/*` | Review/approval queues, controlled publication and revision, comments, actors/timestamps, approval history, and audit trail |
| Project Master | Completed | `Projects` | `Project_model` | `projects/*` | Governed project type/location plus preserved legacy context, validated maintenance/status, and linked observations |
| BOQ Management | Completed | `Boq` | `Boq_model` | `boq/*` | Header/item maintenance, hardened CSV/XLSX normalization, optional reviewed extraction proposals, deterministic fallback/revalidation, reconciliation, staging, and transactional import |
| BOQ-to-UCD Mapping | Completed | `Boq_mapping` | `Boq_mapping_model` | `boq_mapping/*` | Manual candidates, exact revision selection, Proposed/Confirmed/Rejected workflow, progress, search/UOM context, and append-only history |
| Cost Benchmarking | Completed | `Benchmarking` | `Benchmarking_model` | `benchmarking/index` | Historical multidimensional filters, safe UOM/currency groups, percentiles/ranges, coverage, chart, and observation traceability |
| Cost Intelligence | Completed | `Cost_intelligence` | `Cost_intelligence_model` | `cost_intelligence/*` | Explainable mapping ranking, confidence-scored provisional ranges, IQR outliers, evidence-aware trends, and no automatic governed-data changes |
| Elemental Costing | Completed | `Elemental_costs` | `Elemental_cost_model` | `elemental_costs/*` | SCI-independent project-market/UniFormat cost plans, scope applicability, direct element lines, normalized summaries, append-oriented rate evidence, and controlled plan workflow |
| Release Hardening | Completed | Shared controller/config | | Shared layout and operational docs | CSP/security headers, protected source artifacts, hardened XLSX XML, production guards/logging, release checks, backup/recovery, deployment, and user guidance |
| Crew Productivity Management | Completed | `Crew_productivity` | `Crew_productivity_model` | `crew_productivity/*`, `unit_rates/view` | Governed day-based Draft productivity, selected costing crew, derivation preview/application, lineage, and stale/reapply controls |
| Guided Cost Item Assembly | Completed | `Standard_cost_items/create` | `Standard_cost_item_assembly_model` | `standard_cost_items/create`, `cost-item-assembly.js` | Six-step preview/save workflow atomically creating a Draft from materials, manual or crew-derived labor, equipment, and allowances |
| ML Data Governance | Completed — Phase 24 | `Ml_governance`, `Ml_worker` | `Ml_governance_model` | `ml_governance/*` | Immutable source snapshots, reviewed labels/feedback, freeze/export/approval gates, JSONL checksums, job queue, and read-only registry metadata |
| ML Extraction and Model Lifecycle | Planned — Phases 25–30 | TBD | TBD | TBD | Reviewed extraction, learned ranking, controlled training/registry lifecycle, integration, pilot, and monitoring |
