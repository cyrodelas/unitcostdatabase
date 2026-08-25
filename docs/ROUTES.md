# Route Reference

| URL | Controller/Method | Access | Status |
|---|---|---|---|
| `/` | `Dashboard/index` | `dashboard.view` | Dashboard (default route) |
| `/dashboard` | `Dashboard/index` | `dashboard.view` | KPI, revision-status, resource-coverage, and operational dashboard |
| `/materials` | `Materials/index` | `materials.view` | Searchable/filterable material master list |
| `/materials/create` | `Materials/create` | `materials.manage` | Add a material master record |
| `/materials/{id}` | `Materials/view/{id}` | `materials.view` | Material details, references, variants, and rate history |
| `/materials/{id}/edit` | `Materials/edit/{id}` | `materials.manage` | Edit a material master record |
| `/materials/{id}/status` | `Materials/status/{id}` | `materials.manage`, POST only | Toggle material active/inactive status |
| `/equipment` | `Equipment/index` | `equipment.view` | Searchable/filterable equipment master list |
| `/equipment/create` | `Equipment/create` | `equipment.manage` | Add an equipment master record |
| `/equipment/{id}` | `Equipment/view/{id}` | `equipment.view` | Equipment details, group context, and cost-item usage |
| `/equipment/{id}/edit` | `Equipment/edit/{id}` | `equipment.manage` | Edit an equipment master record |
| `/equipment/{id}/status` | `Equipment/status/{id}` | `equipment.manage`, POST only | Toggle equipment active/inactive status |
| `/labor` | `Labor/index` | `labor.view` | Searchable/filterable governed labor craft list |
| `/labor/create` | `Labor/create` | `labor.manage` | Add a labor craft record |
| `/labor/{id}` | `Labor/view/{id}` | `labor.view` | Labor details, governed rates, components, aliases, and cost-item usage |
| `/labor/{id}/edit` | `Labor/edit/{id}` | `labor.manage` | Edit a labor craft record |
| `/labor/{id}/status` | `Labor/status/{id}` | `labor.manage`, POST only | Toggle labor active/inactive status |
| `/crews` | `Crews/index` | `crews.view` | Searchable/filterable crew list with calculated daily cost |
| `/crews/create` | `Crews/create` | `crews.manage` | Add a crew header |
| `/crews/{id}` | `Crews/view/{id}` | `crews.view` | Crew composition, calculated cost, and productivity usage |
| `/crews/{id}/edit` | `Crews/edit/{id}` | `crews.manage` | Edit a crew header |
| `/crews/{id}/status` | `Crews/status/{id}` | `crews.manage`, POST only | Toggle crew active/inactive status |
| `/crews/{id}/members/create` | `Crews/add_member/{id}` | `crews.manage` | Add a Labor Master reference and quantity to a crew |
| `/crews/{id}/members/{memberId}/edit` | `Crews/edit_member/{id}/{memberId}` | `crews.manage` | Edit a crew composition row |
| `/standard-cost-items` | `Standard_cost_items/index` | `standard_cost_items.view` | Filtered enterprise standard cost item list |
| `/standard-cost-items/create` | `Standard_cost_items/create` | `standard_cost_items.manage` and `unit_rates.manage` | Six-step guided preview and atomic initial Draft/resource assembly |
| `/standard-cost-items/{id}` | `Standard_cost_items/view/{id}` | `standard_cost_items.view` | Current definition, classification, resources, coding, revision, and approval state |
| `/standard-cost-items/{id}/edit` | `Standard_cost_items/edit/{id}` | `standard_cost_items.manage`; current Draft only | Edit descriptive/classification fields without changing governed code or workflow state |
| `/standard-cost-items/{id}/lifecycle` | `Standard_cost_items/lifecycle/{id}` | `standard_cost_items.manage`, POST only | Toggle item lifecycle Active/Inactive |
| `/unit-rates` | `Unit_rates/index` | `unit_rates.view` | Current build-up totals, baselines, and rate-completeness list |
| `/unit-rates/{id}` | `Unit_rates/view/{id}` | `unit_rates.view` | Detailed material/labor/equipment/allowance build-up and reconciliation |
| `/unit-rates/{id}/{type}/create` | `Unit_rates/add/{id}/{type}` | `unit_rates.manage`; current Draft only | Add a material, labor, equipment, or allowance component |
| `/unit-rates/{id}/{type}/{componentId}/edit` | `Unit_rates/edit/{id}/{type}/{componentId}` | `unit_rates.manage`; current Draft only | Edit a build-up component |
| `/unit-rates/{id}/{type}/{componentId}/delete` | `Unit_rates/delete/{id}/{type}/{componentId}` | `unit_rates.manage`; current Draft only; GET confirmation and CSRF-protected POST deletion | Delete a material, manual-labor, equipment, or allowance component |
| `/unit-rates/{id}/productivity/create` | `Crew_productivity/create/{id}` | `unit_rates.manage`; current Draft only | Add sourced day-based crew productivity |
| `/unit-rates/{id}/productivity/{productivityId}/edit` | `Crew_productivity/edit` | `unit_rates.manage`; current Draft only | Edit productivity and mark selected derived labor stale |
| `/unit-rates/{id}/productivity/{productivityId}/apply` | `Crew_productivity/apply` | `unit_rates.manage`; current Draft; GET preview/POST apply | Preview and transactionally replace labor with crew-derived components |
| `/unit-rates/{id}/labor/manual` | `Crew_productivity/manual` | `unit_rates.manage`; current Draft; POST only | Convert derived rows to manually editable labor while retaining values |
| `/rates` | `Rates/index` | `rates.view` | Current material/labor rates and complete material/labor/cost-item history |
| `/rates/material/create` | `Rates/create/material` | `rates.manage` | Append a new effective material schedule and rate |
| `/rates/labor/create` | `Rates/create/labor` | `rates.manage` | Append a new effective labor schedule and rate |
| `/rates/cost_item/create` | `Rates/create/cost_item` | `rates.manage` | Append a cost-item rate observation with supported context |
| `/governance/review` | `Governance/review` | `governance.review` | Technical-review queue |
| `/governance/approval` | `Governance/approval` | `governance.approve` | Approval and publication queue |
| `/governance/audit` | `Governance/audit` | `governance.audit` | Governance audit trail |
| `/governance/revisions/{id}/{action}` | `Governance/action` | Action-specific manage/review/approve/publish permission, POST only | Controlled workflow transition or successor revision creation |
| `/projects` | `Projects/index` | `projects.view` | Searchable/filterable project register |
| `/projects/create` | `Projects/create` | `projects.manage` | Add a project master record |
| `/projects/{id}` | `Projects/view/{id}` | `projects.view` | Project details and linked cost-item rate observations |
| `/projects/{id}/edit` | `Projects/edit/{id}` | `projects.manage` | Edit a project master record |
| `/projects/{id}/status` | `Projects/status/{id}` | `projects.manage`, POST only | Toggle project active/inactive state |
| `/projects/{id}/delete` | `Projects/delete/{id}` | `projects.manage`, POST only; exact code confirmation; no dependencies | Permanently delete an unreferenced project |
| `/elemental-costs` | `Elemental_costs/index` | `elemental_costs.view` | Project-market elemental cost-plan register |
| `/elemental-costs/create` | `Elemental_costs/create` | `elemental_costs.manage` | Create a Draft elemental cost plan |
| `/elemental-costs/{id}` | `Elemental_costs/view/{id}` | `elemental_costs.view` | Plan scope, direct element lines, summaries, and workflow |
| `/elemental-costs/{id}/edit` | `Elemental_costs/edit/{id}` | `elemental_costs.manage`; Draft only | Edit a Draft plan header |
| `/elemental-costs/{id}/elements/create` | `Elemental_costs/add_element/{id}` | `elemental_costs.manage`; Draft only | Add a direct UniFormat Level 3/4 cost line |
| `/elemental-costs/{id}/elements/{elementId}/edit` | `Elemental_costs/edit_element` | `elemental_costs.manage`; Draft only | Edit a direct elemental cost line |
| `/elemental-costs/{id}/action/{action}` | `Elemental_costs/action` | Action-specific manage/approve permission, POST only | Submit, return, approve, publish, or archive a plan |
| `/elemental-costs/rates` | `Elemental_costs/rates` | `elemental_costs.view` | Append-oriented elemental-rate evidence register |
| `/elemental-costs/rates/create` | `Elemental_costs/create_rate` | `elemental_costs.manage` | Append elemental rate evidence |
| `/elemental-costs/scope` | `Elemental_costs/scope` | `elemental_costs.manage` | Project-market UniFormat scope applicability |
| `/boq` | `Boq/index` | `boq.view` | Searchable BOQ register |
| `/boq/create` | `Boq/create` | `boq.manage` | Add a BOQ header |
| `/boq/{id}` | `Boq/view/{id}` | `boq.view` | BOQ header, items, totals, and import history |
| `/boq/{id}/edit` | `Boq/edit/{id}` | `boq.manage` | Edit a BOQ header |
| `/boq/{id}/status` | `Boq/status/{id}` | `boq.manage`, POST only | Toggle BOQ availability |
| `/boq/{id}/delete` | `Boq/delete/{id}` | `boq.manage`, POST only; Draft/Validated; exact code confirmation | Permanently delete one BOQ and its dependent import/mapping records |
| `/boq/{id}/items/create` | `Boq/add_item/{id}` | `boq.manage`; Draft/Validated only | Add a BOQ line |
| `/boq/{id}/items/{itemId}/edit` | `Boq/edit_item` | `boq.manage`; Draft/Validated only | Edit a BOQ line |
| `/boq/{id}/items/{itemId}/status` | `Boq/item_status` | `boq.manage`, POST only | Toggle BOQ-line availability |
| `/boq/{id}/import` | `Boq/import/{id}` | `boq.manage`; Draft/Validated only | Upload and validate CSV/XLSX rows |
| `/boq/{id}/imports/{batchId}` | `Boq/batch` | `boq.view` | Import validation/error report |
| `/boq/{id}/imports/{batchId}/predictions/{predictionId}/review` | `Boq/review_extraction` | `boq.manage` + `ml.review`, POST only | Accept, correct, or reject one pending extraction proposal and rerun deterministic validation |
| `/boq/{id}/imports/{batchId}/commit` | `Boq/commit` | `boq.manage`, POST only | Transactionally commit a Ready import batch |
| `/boq-mapping` | `Boq_mapping/index` | `boq.map` | BOQ mapping register and progress |
| `/boq-mapping/{boqId}` | `Boq_mapping/view` | `boq.map` | BOQ items with selected UCD revision and mapping status |
| `/boq-mapping/{boqId}/items/{itemId}` | `Boq_mapping/item` | `boq.map` | Candidate search, selection, status controls, and history |
| `/boq-mapping/{boqId}/items/{itemId}/candidates` | `Boq_mapping/add_candidate` | `boq.map`, POST only | Add or restore a manual candidate |
| `/boq-mapping/{boqId}/items/{itemId}/candidates/{candidateId}/status` | `Boq_mapping/candidate_status` | `boq.map`, POST only | Disable or restore an unselected candidate |
| `/boq-mapping/{boqId}/items/{itemId}/candidates/{candidateId}/select` | `Boq_mapping/select` | `boq.map`, POST only | Select or replace the proposed mapping |
| `/boq-mapping/{boqId}/items/{itemId}/action/{confirm|reject|reopen}` | `Boq_mapping/action` | `boq.map`, POST only | Controlled mapping-state transition |
| `/benchmarking` | `Benchmarking/index` | `benchmarking.view` | Historical rate comparisons and filters across available dimensions |
| `/cost-intelligence` | `Cost_intelligence/index` | `cost_intelligence.view` | Explainable range, outlier, confidence, and trend signals |
| `/cost-intelligence/suggestions` | `Cost_intelligence/suggestions` | `cost_intelligence.view` | Read-only standard-item candidate ranking with score explanations |
| `/ml-governance` | `Ml_governance/index` | `ml.view` | Dataset, job, label-state, and read-only model-registry overview |
| `/ml-governance/datasets/create` | `Ml_governance/create_dataset` | `ml.train` | Create an Extraction, Mapping, or Resource Template dataset definition |
| `/ml-governance/datasets/{id}` | `Ml_governance/dataset/{id}` | `ml.view` | Dataset definition and immutable versions |
| `/ml-governance/datasets/{id}/versions/create` | `Ml_governance/create_version/{id}` | `ml.train`, POST only | Snapshot eligible authoritative records into a Draft version |
| `/ml-governance/versions/{id}` | `Ml_governance/version/{id}` | `ml.view` | Record checksums, labels, feedback, manifest, and artifact state |
| `/ml-governance/versions/{id}/records/{recordId}/review` | `Ml_governance/review` | `ml.review`, POST only | Review a Draft label and append feedback |
| `/ml-governance/versions/{id}/freeze` | `Ml_governance/freeze/{id}` | `ml.train`, POST only | Freeze a reviewed version and queue idempotent export |
| `/ml-governance/versions/{id}/approve` | `Ml_governance/approve/{id}` | `ml.review`, POST only | Approve only after checksum-verified export |
| CLI `ml_worker run` | `Ml_worker/run` | CLI only | Process one queued JSONL dataset export outside the web root |
| `/references` | `References/index` | `references.view` | Reference-table catalog |
| `/references/{type}` | `References/index/{type}` | `references.view` | DataTables list for an allowlisted reference entity |
| `/references/{type}/create` | `References/create/{type}` | `references.manage` | Add a reference record |
| `/references/{type}/{id}/edit` | `References/edit/{type}/{id}` | `references.manage` | Edit a reference record |
| `/references/{type}/{id}/status` | `References/status/{type}/{id}` | `references.manage`, POST only | Toggle active/inactive status |
| `/login` | `Auth/login` | Public | Username/email and password login |
| `/logout` | `Auth/logout` | Authenticated, POST only | CSRF-protected logout |
| `/account` | `Account/index` | Authenticated | Current account details |
| `/account/password` | `Account/password` | Authenticated | Password change and forced first-login gate |
| `/roles` | `Roles/index` | `roles.view` | Role master |
| `/roles/create` | `Roles/create` | `roles.manage` | Create role |
| `/roles/{id}/edit` | `Roles/edit` | `roles.manage` | Edit role |
| `/roles/{id}/permissions` | `Roles/permissions` | `roles.manage` | Assign role permissions |
| `/permissions` | `Permissions/index` | `roles.view` | Read-only permission catalog |
| `/user-roles` | `User_roles/index` | `users.manage` | User-role assignment list |
| `/user-roles/{id}` | `User_roles/edit` | `users.manage` | Assign roles to a user |
| `/health` | `Health/index` | Public | Application/database readiness check |

`{type}` is restricted to the 49 entries in `application/config/reference_data.php`; arbitrary table access is not allowed. `Auth_cli` is CLI-only and has no web route. `Welcome/index` remains as a compatibility redirect to `/dashboard`.
