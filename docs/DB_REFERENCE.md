# Database Reference

Phases 22 and 24 additively added crew-derived labor lineage and ML governance foundations after inspecting the live schema. Backup/recovery operations are documented in `BACKUP_AND_RECOVERY.md` and must target explicitly confirmed databases.

Assessment source: updated `nexus_ucd.sql` supplied 12-Aug-2026. The live database now includes two Phase 22 lineage tables in addition to the previously documented base tables; the 17 intended-view placeholders remain unchanged.

Phase 1 created the previously absent local `nexus_ucd` database from the authoritative dump while filtering all `DELETE` and `DROP` statements. The 52 tables, seed data, and 2 procedures loaded. The dump's 17 temporary view placeholders remain base tables because converting them requires the explicitly prohibited `DROP TABLE` operation; view finalization awaits authorization.

## Standard Cost Items and Governance

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `standard_cost_item` | Stable enterprise item identity | `cost_item_id` | `cost_item_uid`, `item_sequence`, `lifecycle_status`, audit fields |
| `standard_cost_item_revision` | Versioned item definition and classification | `cost_item_revision_id` | FK `cost_item_id`; classifications including attribute subject class, specification, UOM, description, effective dates, revision/coding status, approvals; self-FK `supersedes_revision_id` |
| `cost_item_code_component` | Generated enterprise-code segments per revision | `cost_item_code_component_id` | FKs to revision, CSI, UniFormat, specification, and trade; `generated_cost_code`, `coding_status` |
| `cost_item_code_sequence` | Code sequence by division and trade | `cost_item_code_sequence_id` | FKs `division_id`, `trade_id`; `last_sequence`, `is_active` |
| `cost_item_approval_history` | Workflow action history | `approval_history_id` | FK revision; `workflow_stage`, `action`, actor/date/comments |
| `cost_item_audit_log` | Item and revision field/event audit | `audit_id` | FKs item and revision; old/new values, actor, timestamp, source |
| `cost_item_keyword` | Weighted item search terms | `keyword_id` | FK item; `keyword`, `keyword_type`, `weight` |
| `cost_item_synonym` | Approved aliases and normalized search text | `synonym_id` | FK item; source and approval fields |
| `cost_item_relation` | Typed relationships between items | `relation_id` | Self-domain FKs source/target item; `relation_type` |

## Application Security

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `app_user` | Application login identity and access state | `user_id` | Unique username/email; display name, password hash, active/forced-change flags, failed attempts, lock expiry, login/password timestamps |
| `app_role` | Application role master | `role_id` | Unique role code; name, description, system/active flags |
| `app_permission` | Application permission catalog | `permission_id` | Unique permission code; permission name, module, description, active flag |
| `app_role_permission` | Many-to-many role permission grants | Composite: role, permission | FKs role, permission, optional granting user; grant timestamp |
| `app_user_role` | Many-to-many user role assignments | Composite: user, role | FKs user, role, optional assigning user; assignment timestamp |

Phase 4 seeds 8 system roles and 35 permission codes. `SYS_ADMIN` receives every active permission.

## ML Data and Model Governance

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `ml_dataset` | Dataset definition by governed capability | `dataset_id` | Unique code, Extraction/Mapping/Resource Template capability, active state, creator |
| `ml_dataset_version` | Immutable snapshot lifecycle | `dataset_version_id` | Dataset/version, source cutoff, counts, Draft/Frozen/Approved state, manifest/artifact checksums |
| `ml_dataset_record` | Canonical source snapshot and label | `dataset_record_id` | Version, source identity, split group, payload/label JSON, review state, record checksum, source lineage IDs |
| `ml_feedback` | Append-only label review | `feedback_id` | Record, prior/new state, event, comments, actor/time |
| `ml_job` | Asynchronous export/training/evaluation queue foundation | `ml_job_id` | Type/capability, dataset/model references, idempotency key, state, attempts, result/error |
| `ml_model_version` | Model registry metadata foundation | `model_version_id` | Model/version/environment/capability, dataset lineage, artifact, feature, and metric metadata |
| `ml_extraction_run` | Per-import inference or fallback lineage | `extraction_run_id` | Batch, optional model, method/status, request checksum, counts, latency/fallback reason, actor/time |
| `ml_extraction_prediction` | Reviewable normalized-row proposal | `extraction_prediction_id` | Run and exact staging row, proposal/confidence JSON, review state, applied snapshot, reviewer/time |
| `ml_extraction_feedback` | Append-only extraction review | `extraction_feedback_id` | Prediction, event/state transition, proposed/applied JSON, comments, actor/time |

Phase 24 implements dataset exports only. Training, evaluation, model mutation, activation, and rollback remain unavailable. Snapshot source IDs are intentionally non-FK lineage so immutable payloads survive separately authorized source cleanup; governance-parent and actor relationships use foreign keys.

Phase 25 adds `ml_extraction_run`, `ml_extraction_prediction`, and `ml_extraction_feedback`. Runs reference the BOQ import batch and optional active model; proposals reference exact staging rows; feedback is append-only. Batch/source cleanup cascades only through these dependent assistance records.

## Elemental Costing

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `ref_elemental_cost_basis` | Governed normalization/direct-amount basis | `elemental_cost_basis_id` | Unique basis code, UOM label, description, display order, active state |
| `ref_elemental_scope_element` | Optional allowed UniFormat scope by project-market pair | `elemental_scope_element_id` | FK applicability bridge and Level 3; optional Level 4; active state |
| `elemental_cost_plan` | Project-market elemental plan header | `elemental_cost_plan_id` | Project Type, Market Segment, basis quantity, currency, status, dates, workflow actors |
| `elemental_cost_plan_element` | Direct elemental amount/rate line | `elemental_cost_plan_element_id` | FK plan, Level 3, optional Level 4, basis, quantity/rate/amount, active state |
| `elemental_rate_history` | Append-oriented elemental benchmark evidence | `elemental_rate_history_id` | Project-market, Level 3/4, basis, project/location/source, rate, date, current state |

These tables intentionally have no Standard Cost Item foreign key. Elemental scope and values are classified directly by project market and UniFormat.

## Resource Build-Up and Rates

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `cost_item_material` | Material quantities for a revision | `cost_item_material_id` | FKs revision, material, variant, UOM; quantity, waste, primary flag |
| `cost_item_labor` | Labor inputs for a revision | `cost_item_labor_id` | FKs revision and labor; crew quantity, labor days/hours |
| `cost_item_equipment` | Equipment inputs for a revision | `cost_item_equipment_id` | FKs revision and equipment; quantity, hours per unit |
| `cost_item_productivity` | Output/duration and crew productivity | `productivity_id` | FKs revision, output/duration UOM, crew; source, benchmark flag, effective date |
| `cost_item_labor_build_up` | Labor costing mode and selected productivity per revision | `cost_item_revision_id` | Method, selected productivity/crew, duration/output snapshots, stale state, application actor/time |
| `cost_item_labor_derivation` | Crew-derived labor lineage | `labor_derivation_id` | FKs revision/labor/productivity/crew member; member, duration, output, rate, currency, and calculated-day snapshots |
| `cost_item_resource_allowance` | Non-resource direct-cost allowances | `cost_item_resource_allowance_id` | FKs revision and allowance type; amount per item unit |
| `cost_item_rate_history` | Observed/baseline item rates | `rate_history_id` | FKs revision/project plus governed source type, basis, price period, location, and validation status |
| `material_master` | Material master | `material_id` | FKs category, group, default UOM, specification, and attribute subject class; code/name/status |
| `material_variant` | Size/UOM variant of a material | `material_variant_id` | FKs material and UOM; variant code and size |
| `material_rate_schedule` | Dated material rate context | `material_rate_schedule_id` | Schedule code/name, currency, effective dates, status |
| `material_rate_history` | Material variant rate by schedule | `material_rate_history_id` | FKs variant and schedule; unit rate, current/status fields |
| `labor_master` | Governed labor craft master | `labor_id` | FK category; labor code/name/status |
| `labor_rate_schedule` | Dated labor rate context | `labor_rate_schedule_id` | Currency, admin fee, effective dates, source/status |
| `labor_rate_history` | Labor rates by schedule | `labor_rate_history_id` | FKs labor and schedule; daily/monthly/OT totals and current/status fields |
| `labor_rate_component_amount` | Component amounts for a labor rate | `labor_rate_component_amount_id` | FKs labor rate history and component; amount/source value |
| `labor_rate_component_assumption` | Schedule-level component assumptions | `labor_rate_component_assumption_id` | FKs schedule and component; purchase cost, quantity, lifespan, source |
| `labor_source_alias` | Source-system labor-name mapping | `labor_source_alias_id` | FK labor; source system/name and mapping status |
| `equipment_master` | Equipment master | `equipment_id` | FK equipment group; code/name/scope/category/status |
| `crew_master` | Reusable crew header | `crew_id` | Crew code/name/trade/status |
| `crew_member` | Labor composition of a crew | `crew_member_id` | FKs crew and labor; member count and source role |

## PSMM Classification

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `psmm_section` | PSMM edition and section | `psmm_section_id` | Edition, section code/title, status |
| `psmm_classification` | Classification row within a section | `psmm_classification_id` | FKs section and UOM; reference and classification columns |
| `psmm_rule` | Information/measurement/definition rule | `psmm_rule_id` | FK section; rule code/type/text |
| `psmm_rule_inheritance` | Rule inheritance between sections | `psmm_rule_inheritance_id` | FKs source and target sections; basis/status |
| `cost_item_psmm_classification` | Revision-to-classification mapping | Composite: revision, classification | Mapping type, treatment, confidence, status/basis |
| `cost_item_psmm_rule` | Revision-to-rule mapping | Composite: revision, rule | Application, scope, primary flag, confidence/status |
| `cost_item_psmm_mapping_review` | Review state for a revision's PSMM map | `cost_item_revision_id` | Category, reason, action, review status/date |

## Project and Reference Data

| Table | Purpose | Primary Key | Important Relationships / Fields |
|---|---|---|---|
| `project_master` | Project context for historical rates | `project_id` | Code/name, governed Project Type–Market Segment pair, location, legacy location text, floor area/count, dates, status |
| `ref_market_segment` | Governed project market-segment dictionary | `market_segment_id` | Unique code/name, display order, description, status |
| `ref_project_type_market_segment` | Project Type–Market Segment applicability hierarchy | `project_type_market_segment_id` | Unique project-type/segment pair, display order, default and active flags |
| `boq_header` | Project BOQ document | `boq_id` | FK project; unique code, document metadata, currency, revision, status, actors |
| `boq_item` | Normalized priced BOQ line | `boq_item_id` | FK BOQ and optional UOM; stable line, description, quantity/rate/amount |
| `boq_import_batch` | Uploaded BOQ import control | `boq_import_batch_id` | FK BOQ; file identity, selected sheet, processing counters, source/parsed total reconciliation, unpriced count, actor/timestamps |
| `boq_import_staging` | Parsed and validated import row | `boq_import_staging_id` | FK batch, optional UOM/committed item; raw data, parsed values, status/errors |
| `boq_mapping_candidate` | Candidate UCD revisions for a BOQ line | `boq_mapping_candidate_id` | FKs BOQ item and exact cost-item revision; source, optional score/rank/explanation, availability |
| `boq_item_mapping` | Selected BOQ-to-UCD mapping | `boq_item_mapping_id` | Unique BOQ item; composite candidate/item integrity; method and Proposed/Confirmed/Rejected state |
| `boq_item_mapping_history` | Mapping audit history | `boq_item_mapping_history_id` | FKs item/candidate; action, old/new status, comments, actor/time |
| `ref_csi_division` | CSI division | `division_id` | Code/name/status |
| `ref_csi_section` | CSI section | `section_id` | FK division; code/name/status |
| `ref_uniformat_level1` | UniFormat level 1 | `uniformat_level1_id` | Code/name/status |
| `ref_uniformat_level2` | UniFormat level 2 | `uniformat_level2_id` | FK level 1; code/name/status |
| `ref_uniformat_level3` | UniFormat level 3 | `uniformat_level3_id` | FK level 2; code/name/status |
| `ref_uniformat_level4` | UniFormat level 4 | `uniformat_level4_id` | FK level 3; code/name/domain/status |
| `ref_specification` | Governed specification | `specification_id` | Source/code/title/edition/effective dates/status |
| `ref_specification_code_segment` | Enterprise code segment for specification | `specification_code_segment_id` | FK specification; segment/status/remarks |
| `ref_trade` | Trade lookup | `trade_id` | FK governed trade division; code/name/status |
| `ref_uom` | Units of measure | `uom_id` | Code/name/quantity type/status |
| `ref_material_category` | Material category | `material_category_id` | Code/name/description/status |
| `ref_material_group` | Material group | `material_group_id` | Code/name/status |
| `ref_equipment_group` | Equipment group | `equipment_group_id` | Code/name/status |
| `ref_labor_category` | Labor category | `labor_category_id` | Code/name/description/status |
| `ref_labor_rate_component` | Labor rate component definition | `labor_rate_component_id` | Code/name/category/amount basis/status |
| `ref_resource_allowance_type` | Resource allowance type | `resource_allowance_type_id` | Code/name/description/status |

## Useful Views

| View | Intended Use |
|---|---|
| `vw_ucd` | Primary UCD read model |
| `vw_ucd_complete_data` | Complete current-revision UCD data and calculated rate context |
| `vw_ucd_complete_data_all_revisions` | Complete UCD data across revision history |
| `vw_cost_item_resource_unit_rate` | Resource-based unit-rate summary |
| `vw_standard_cost_item_code_readiness` | Enterprise-code readiness checks |
| `vw_enterprise_code_generation_diagnostic` | Code-generation diagnostics |
| `vw_material_master_complete` | Material master with reference descriptions |
| `vw_equipment_master_complete` | Equipment master with group details |
| `vw_current_labor_rate` | Current labor rates |
| `vw_crew_composition` | Flattened crew membership |
| `vw_uniformat_hierarchy` | Flattened UniFormat levels 1-3 |
| `vw_project_type_market_segment_hierarchy` | Active Project Type–Market Segment applicability with category/group context |
| `vw_project_master_classification` | Project classifications including Project Type, Market Segment, category, and group |
| `vw_cost_item_psmm_classification_mapping` | Item-to-PSMM classification details |
| `vw_cost_item_psmm_mapping_status` | PSMM mapping/review status |
| `vw_cost_item_psmm_rule_complete` | Complete mapped PSMM rules |
| `vw_cost_item_psmm_rule_review` | PSMM rule review data |
| `vw_cost_item_psmm_rule_summary` | Aggregated PSMM rule coverage |
| `vw_psmm_effective_rule` | Effective PSMM rules including inheritance |
| `vw_elemental_classification_hierarchy` | Active UniFormat Level 1–4 elemental hierarchy |
| `vw_elemental_scope_hierarchy` | Active project-market and allowed UniFormat scope |
| `vw_elemental_cost_plan_detail` | One row per active direct elemental plan line |
| `vw_elemental_cost_summary_level3` | Level 3 plan totals |
| `vw_elemental_cost_plan_total` | Plan grand totals |
| `vw_elemental_cost_summary` | Plan totals and normalized basis metrics |
| `vw_elemental_rate_history` | Elemental evidence with hierarchy and scope labels |
| `vw_elemental_cost_benchmark` | Comparable elemental benchmark aggregates |
| `vw_elemental_residential_subdivision_socialized` | Current Residential Subdivision / Socialized elemental evidence slice |

## Stored Procedures
- `sp_publish_enterprise_cost_code` publishes one ready enterprise cost code.
- `sp_publish_all_ready_enterprise_cost_codes` publishes all ready enterprise cost codes.

## Confirmed Schema Gaps / Risks
- Authentication and RBAC structures now exist in the five `app_*` security tables.
- Phase 16 added the four BOQ/import tables and Phase 17 added the three manual-mapping tables; see `docs/BOQ_SCHEMA_PROPOSAL.md` and `docs/BOQ_MAPPING_SCHEMA.md`.
- No equipment rate schedule/history table exists, so equipment resources cannot yet contribute a governed rate in the same way as materials and labor.
- `standard_cost_item_revision.assembly_id` declares an FK to `ref_uniformat_assembly.assembly_id`, but `ref_uniformat_assembly` is absent from the dump. This must be reconciled against the live authoritative schema before application use or any proposal.
- The updated dump also adds `standard_cost_item_revision.standard_item_name_id` and references `ref_standard_item_name`, but does not define that table. The column was retained without fabricating the missing target or constraint.
- Actor fields such as `created_by`, `approved_by`, `action_by`, and `changed_by` are not foreign-keyed to a user table.
- The local MariaDB instance contains the authoritative tables and reference seed data. At the Phase 23 handoff, resource/item master tables are empty and must be populated through governed workflows. Its 17 intended views are still empty HeidiSQL placeholder tables pending explicit authorization to replace them.
- Phase 22 resolves labor method and crew-member/productivity lineage through `cost_item_labor_build_up` and `cost_item_labor_derivation` without changing existing labor-row semantics.
- Phase 23 adds no schema objects. Guided creation writes the existing item, revision, code-component, resource, labor-lineage, and audit tables in one transaction.
- Phase 24 adds the six `ml_*` governance tables and four permission contracts without changing existing business-table definitions.
- Phase 25 adds reviewed extraction-run, prediction, and feedback lineage. Learned mapping predictions, model training/activation operations, and automatic business actions remain unavailable.
- `standard_cost_item_revision.project_type_id` and `.market_segment_id` are required and use composite FK `fk_cost_item_revision_project_market` to the unique key in `ref_project_type_market_segment`. The initial 371 revisions were backfilled through stable codes `RES-SUB` and `MKT-004`.
- `ref_elemental_cost_basis` governs GFA, site, saleable, residential-unit, element-quantity, and direct-amount bases; `ref_elemental_scope_element` optionally confirms UniFormat applicability for a Project Type / Market Segment bridge row.
- `elemental_cost_plan` is the project-market plan header; `elemental_cost_plan_element` prices UniFormat Level 3/4 directly; `elemental_rate_history` retains append-oriented elemental evidence. None has a Standard Cost Item foreign key.
- Corrected elemental reporting views join a nullable Level 4 value by its exact ID so a Level 3-only line contributes once rather than once per child sub-element.
