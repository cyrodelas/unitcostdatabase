# ML, Crew Productivity, and Resource Assembly Roadmap

Status: Phases 22–25 implemented (17-Aug-2026); Phase 26 is next

## Objective

Extend Project Nexus UCD with governed crew-productivity management, guided Standard Cost Item resource assembly, ML-assisted BOQ extraction, and learned BOQ-to-UCD mapping. CodeIgniter and MySQL remain authoritative for users, workflow, validation, and business records. ML may recommend or prefill work but may not bypass validation, authorization, revision governance, or human mapping confirmation.

## Current Readiness

The counts below are the Phase 21 readiness snapshot. At the Phase 23 handoff, the connected database retains reference data but its resource/item master tables are empty, so governed masters and rates must be populated before operational Draft assembly or ML dataset work.

The live authoritative database was inspected without modification.

| Evidence | Current count / condition | Readiness implication |
|---|---:|---|
| BOQs / BOQ lines | 1 / 537 | One source format is retained; extraction generalization cannot yet be demonstrated. |
| Import batches / staged rows | 1 / 537 | Useful baseline, insufficient as a varied extraction corpus. |
| Mapping candidates / confirmed mappings | 1 / 1 | Insufficient for supervised mapping training; labeling must precede training. |
| Current / Published cost-item revisions | 731 / 1 | Candidate coverage exists, but governed publication coverage is very low. |
| Crews / crew members | 60 / 124 | All crews have members and all members have a current labor rate. |
| Productivity records | 276 | All use days and their output UOM matches the related item revision. |
| Productivity records linked to crews | 35 | Crew association requires completion and review. |
| Productivity records with source/date/benchmark | 0 | Existing productivity is not yet evidence-qualified for training. |
| Labor / material associations | 840 / 251 | Resource examples exist but lack explicit manual-versus-crew derivation lineage. |

## Approved Functional Boundary

### Included

- CSV/XLSX structural extraction: worksheet, header, column, section/item row, hierarchy, description, quantity, UOM, rate, and amount.
- Ranked BOQ-to-UCD candidates with confidence and explanations.
- Crew productivity maintenance on the current Draft Standard Cost Item revision.
- A guided Standard Cost Item creation/build-up flow using materials and either manual or crew-derived labor.
- Versioned datasets, training runs, model evaluation, controlled activation, rollback, prediction review, and feedback capture.

### Excluded until separately approved

- PDF/image OCR.
- Autonomous creation or publication of Standard Cost Items.
- Automatic confirmation of BOQ mappings.
- Silent changes to quantities, rates, amounts, UOMs, crew composition, or resource associations.
- Training directly inside a PHP web request.
- Direct writes from the ML service to authoritative business tables.
- Monetized equipment cost without an authoritative equipment-rate structure.

## Resource Assembly Model

Materials, labor, equipment, allowances, and productivity are sibling components of one `standard_cost_item_revision`; labor is not owned by a material.

```text
Standard Cost Item Revision
|- Material components
|- Selected productivity and crew
|  `- Crew members -> Labor Master -> current labor rates
|- Equipment components
|- Resource allowances
`- Calculated Final Unit Rate
```

### Labor build-up methods

Every newly managed Draft revision must use exactly one costing method:

1. `MANUAL`: users maintain `cost_item_labor` directly; productivity is informational and does not add a second labor cost.
2. `CREW_DERIVED`: one costing productivity record supplies a crew whose members are expanded into `cost_item_labor`; direct duplicate edits are blocked until the user explicitly converts the build-up to manual.

Existing revisions without lineage remain `LEGACY_MANUAL` until reviewed. They are not silently reclassified or recalculated.

### Day-based calculation

For each selected crew member:

```text
labor_days_per_item_unit
  = member_count_snapshot * duration_days / output_quantity

labor_component_cost
  = labor_days_per_item_unit * current_labor_total_daily_rate

crew_labor_cost_per_item_unit
  = sum(labor_component_cost)
```

`crew_quantity` records the member-count snapshot for display and traceability. It is not multiplied again by the current unit-rate formula because member count is already included in `labor_days_per_item_unit`.

### Hour-based calculation

Hour-based productivity stays unavailable until Phase 22 introduces an approved hours-per-workday source. The system must not assume eight hours. When governed, conversion is:

```text
duration_days = duration_hours / governed_hours_per_workday
```

The conversion value and source must be snapshotted with the derivation.

### Material and final-rate calculation

The existing authoritative formulas remain unchanged:

```text
material_component_cost = quantity_per_item_unit * current_variant_rate

final_unit_rate
  = material_cost
  + labor_cost
  + tools_equipment_allowance
  + other_consumables_allowance
  + non_material_activity_allowance
```

`waste_percentage` remains visible but is not monetized by the current formula. Any change requires separate approval and regression reconciliation.

### Crew application workflow

1. Create or open the current Draft revision.
2. Select `MANUAL` or `CREW_DERIVED` labor costing.
3. For crew-derived costing, select one active crew and a costing productivity record.
4. Validate output UOM against the Standard Cost Item UOM and validate duration conversion.
5. Preview crew members, snapshot quantities, current rates, days/unit, hours/unit, component costs, missing rates, inactive members, and currency conflicts.
6. Apply the preview transactionally to the Draft labor rows and lineage records.
7. If the crew or productivity changes, mark the build-up stale and require an explicit preview/reapply action.
8. Never update a Published revision; create a successor Draft.

## Standard Cost Item Creation Flow

Phase 23 will add a governed Draft wizard because the current module edits existing Draft revisions but does not expose item creation.

1. Identity and description.
2. CSI/UniFormat, trade, UOM, material group, specification, and scope.
3. Material variants, quantities, UOMs, waste information, and primary flag.
4. Manual labor or crew/productivity selection and crew-derived preview.
5. Equipment and defined allowances.
6. Final Unit Rate, missing-evidence warnings, and reconciliation preview.
7. Atomic Draft save followed by the existing coding and governance workflow.

The wizard creates a Draft only; it does not approve, publish, or confirm a BOQ mapping.

## ML Architecture Decision

```text
CodeIgniter 3 / PHP 7.4
  |- authoritative UI, RBAC, validation, workflow, audit, and persistence
  |- synchronous inference request with strict timeout and deterministic fallback
  `- asynchronous dataset/training job submission

Private Python ML service and worker
  |- extraction inference
  |- mapping retrieval/ranking
  |- resource-template recommendations
  `- offline training/evaluation

Versioned artifact storage outside the web root
  `- immutable dataset/model files with SHA-256 checksums
```

- The service is private and authenticated; it receives only the minimum job/prediction payload.
- Training is asynchronous and never holds a PHP request open.
- A database-backed job queue is preferred initially to avoid adding a message-broker dependency to the XAMPP deployment.
- The worker reads an immutable, approved export rather than unrestricted live tables.
- Model output returns identifiers, scores, ranks, explanations, model version, and feature/schema version.
- CodeIgniter revalidates every identifier and persists any approved candidate or correction.
- Inference timeout or service failure falls back to the current deterministic parser/search without blocking manual work.
- Prefer ONNX artifacts where the selected estimator is supported. Any Python-native serialized artifact must be produced internally, checksum-verified, stored outside the web root, and loaded only by the isolated trusted worker.
- A lightweight application-owned model registry is the initial target. MLflow may be evaluated later if operational complexity warrants it.

## ML Tasks and Training Labels

### BOQ extraction

The existing hardened parser remains the baseline and security boundary. ML may predict worksheet, header row, column roles, row roles, and hierarchy. Deterministic code remains responsible for XML safety, limits, numeric parsing, UOM validation, arithmetic, staging, and commit.

Training labels come from reviewed source cells/rows and their corrected staging fields. A split is grouped by workbook/import batch so rows from one workbook cannot leak across train and test sets.

### BOQ mapping

Use two stages:

1. Candidate retrieval from normalized text, keywords, approved synonyms, UOM, classification, trade, material, labor, and crew/productivity signals.
2. Learned ranking from confirmed mappings and reviewed suggestions.

A confirmed mapping is a positive label. Rejected candidates and replaced selections are hard negatives. Proposed or merely viewed candidates are not positives. Train the stable `cost_item_id` identity, then resolve to the eligible current active revision at inference and persist the exact selected revision.

### Resource-template recommendation

After enough governed examples exist, a separate model may suggest likely material variants, crew, and productivity range for a Draft Standard Cost Item. Suggestions never create associations until reviewed and applied by an authorized user. Published/approved build-ups are higher-quality labels than incomplete Drafts.

## Evaluation and Promotion Gates

Threshold values are baselined in the pilot; the following gates are mandatory:

| Capability | Primary measures | Promotion requirement |
|---|---|---|
| Extraction | worksheet/header/row classification, field exact match, numeric fidelity, reconciliation | Better than deterministic baseline on unseen workbooks; zero silent numeric corruption. |
| Mapping | Top-1 accuracy, Top-3/Top-5 recall, MRR, high-confidence precision, coverage, UOM-conflict rate | Better than the Phase 19 deterministic ranker on project/workbook-grouped holdout data. |
| Crew recommendation | accepted crew rate, Top-k recall, incompatible-trade/UOM rate | No incompatible recommendation may be auto-applied. |
| Productivity | absolute/percentage error by trade and UOM, interval coverage | Used as a range suggestion only until evidence-qualified performance is approved. |
| Operations | latency, timeout/failure rate, fallback success, artifact integrity | Manual and deterministic workflows remain available during service failure. |

Promotion sequence: offline evaluation -> shadow mode -> visible suggestions -> authorized proposal creation -> controlled production. Automatic mapping confirmation is prohibited by the current governance decision.

## Schema Implementation Status

Phase 22 implemented labor lineage. Phase 24 implemented immutable datasets, feedback, jobs, and registry metadata. Phase 25 implemented reviewed extraction runs, row proposals, and append-only extraction feedback; mapping prediction entities remain for Phase 26.

### Crew/labor lineage entities

- One labor-build-up header per revision: method, selected costing productivity, selected crew, conversion snapshot, applied actor/time, and stale state.
- Derivation lines linking generated `cost_item_labor` rows to the source crew member with member-count and conversion snapshots.
- A governed hours-per-workday reference or setting if hour-based productivity is enabled.

This avoids changing the meaning of existing labor rows and permits `LEGACY_MANUAL` handling.

### ML governance entities

- Dataset and immutable dataset-version metadata.
- Dataset record membership and label quality/status.
- Training job/run, configuration, metrics, logs, and failure state.
- Model version, artifact checksum/path, feature schema, approval, activation, and rollback metadata.
- Extraction prediction/correction records tied to import batch, sheet, row, and field.
- Mapping prediction/feedback records tied to BOQ item, candidate revision, score/rank/explanation, and model version.

Existing `boq_mapping_candidate` already supports `AI` source, score, rank, and explanation. Existing selected mappings already support `AI_ACCEPTED`; model/version lineage must be added through a related entity rather than weakening current constraints.

### Data rules

- Foreign keys use the same identifier types as authoritative parents.
- Prediction and training histories are append-oriented.
- One active model per capability/environment is enforced transactionally.
- Dataset and model artifacts store paths and checksums, not arbitrary executable uploads through the web UI.
- Destructive cleanup, retention periods, and any cascade behavior require explicit approval.

## Permission Implementation

Phase 24 added the following application permission contracts and default built-in-role assignments. `ml.deploy` remains reserved because no model activation endpoint exists yet.

| Capability | Proposed permission | Default roles |
|---|---|---|
| View predictions and model metrics | `ml.view` | SYS_ADMIN, UCD_ADMIN, COST_ENGINEER, REVIEWER, APPROVER, DATA_ANALYST |
| Review/correct predictions and labels | `ml.review` | SYS_ADMIN, UCD_ADMIN, COST_ENGINEER |
| Create dataset versions and training runs | `ml.train` | SYS_ADMIN, UCD_ADMIN, DATA_ANALYST |
| Approve/activate/rollback models | `ml.deploy` | SYS_ADMIN, UCD_ADMIN |

Existing `crews.manage`, `unit_rates.manage`, `standard_cost_items.manage`, and `boq.map` continue to protect their business actions. An ML permission never grants the underlying business permission.

## Delivery Phases

| Phase | Scope | Exit outcome |
|---|---|---|
| 21 | Requirements, data readiness, calculation/lineage rules, architecture, evaluation, permission and schema proposals | Approved design; no runtime/schema change. |
| 22 | Crew productivity management and approved additive lineage migration | Governed Draft productivity and traceable crew-derived labor. |
| 23 | Guided Standard Cost Item creation and resource assembly | Complete Draft from materials plus manual or crew-derived labor without double counting. |
| 24 | Dataset, labeling, feedback, job, and model-governance foundation | Reproducible approved datasets; no production ML action. |
| 25 | ML-assisted CSV/XLSX extraction | Reviewed predictions with deterministic validation/fallback. |
| 26 | Hybrid learned mapping and resource-template models | Evaluated ranked suggestions; no automatic confirmation. |
| 27 | In-system asynchronous training, registry, approval, activation, and rollback | Controlled model lifecycle. |
| 28 | Prediction integration into extraction, mapping, and Draft assembly | Human-reviewed operational assistance. |
| 29 | Offline, shadow, suggestion, and controlled proposal pilot | Evidence-based go/no-go decision. |
| 30 | Monitoring, drift, retraining, recovery, documentation, and release | Supportable production capability. |

## Phase 22 Entry Checklist

- Approve the two labor build-up modes and `LEGACY_MANUAL` handling.
- Approve the day-based formula and decide whether hour-based productivity is deferred or governed.
- Approve whether only one productivity record per revision is selected for costing while others remain evidence/benchmarks.
- Approve the lineage entities and stale/reapply behavior.
- Approve roles allowed to manage productivity and apply crew-derived labor.
- Re-inspect the live schema and prepare additive SQL without dropping, renaming, or recreating existing objects.

## Technical References

- scikit-learn model persistence and ONNX tradeoffs: https://scikit-learn.org/stable/model_persistence.html
- ONNX Runtime Python inference: https://onnxruntime.ai/docs/get-started/with-python.html
- MLflow model registry workflow (optional later evaluation): https://www.mlflow.org/docs/latest/ml/model-registry/workflow/
