# Project Nexus UCD User Guide

Version: August 2026

Project Nexus UCD is the governed Unit Cost Database for reference data, resource masters, standard cost items, rate history, projects, Bills of Quantities (BOQs), mapping, benchmarking, and cost intelligence.

## 1. Accessing the System

Open:

`http://localhost/ucd/`

Sign in using the username and password provided by the administrator. Do not share accounts or passwords.

### First login

New users are redirected to **Change Password** before other pages can be opened. Enter the temporary password, choose a strong new password, confirm it, and save. After a successful change, the system allows access to the modules assigned to the user's role.

### Account lockout

Five unsuccessful login attempts temporarily lock the account for 15 minutes. Wait for the lockout to expire or contact the system administrator.

### Signing out

Use **Sign Out** in the user menu. Closing the browser without signing out is not recommended on a shared computer.

## 2. Navigation and Roles

The Dashboard remains at the top of the sidebar. Each operational role sees only its own process sections. Reference Tables and the Material, Equipment, Labor, and Crew catalogs are available only to the System Administrator and UCD Administrator. A menu that is not permitted is hidden, and direct access to its URL is also denied.

| Role | Primary responsibility | Reference data | Master data |
|---|---|---|---|
| System Administrator | Security and complete system administration | Manage | Manage |
| UCD Administrator | Govern UCD records and workflows | Manage | Manage |
| Cost Engineer / QS | Cost-item development, project costing, BOQ mapping, and analysis | Hidden | Hidden |
| Reviewer | Technical review, audit, and review insights | Hidden | Hidden |
| Approver | Approval, publication, audit, and approval insights | Hidden | Hidden |
| Project User | Project delivery, BOQ management, and mapping | Hidden | Hidden |
| Data Analyst | Benchmarking, intelligence, ML governance, and audit analysis | Hidden | Hidden |
| Executive / Viewer | Dashboard, benchmarking, and trends | Hidden | Hidden |

Visible buttons also depend on the record state. For example, a user with management permission can edit a Standard Cost Item only while its current revision is Draft.

## 3. Dashboard

The Dashboard summarizes the records the user is authorized to see, including:

- standard cost items and current revision states;
- material, equipment, labor, and crew totals;
- published, review, and approval counts;
- resource build-up coverage;
- coded revisions, rate observations, validation, and active projects.

Dashboard cards are informational. Use the applicable sidebar module to inspect or maintain the source records.

## 4. Reference Tables

Reference Tables contain governed codes, classifications, statuses, units of measure, locations, project types, rate evidence, confidence bands, and other controlled selections used throughout the system.

The catalog is grouped into **Classification and Standards**, **Resources and Attributes**, **Projects and Market Segments**, **Locations and Demographics**, and **Rates and Cost Governance**. Market Segments maintains the governed dictionary; Project Type–Market Segments controls which segments are applicable to each Project Type.

### Viewing reference data

1. As a System Administrator or UCD Administrator, open **Reference Data → Reference Tables**.
2. Select a reference category.
3. Search, sort, or page through the records.
4. Review the code, name, parent references, status, and other configured fields.

### Maintaining reference data

Users with `references.manage` can:

1. Select **Add Record** to create a value.
2. Complete required fields and parent selections.
3. Save the record.
4. Use Edit to correct an existing record.
5. Activate or deactivate records instead of deleting them.

Duplicate values and invalid parent references are rejected. Only the configured reference categories are accessible; users cannot enter an arbitrary database table name.

## 5. Master Cost Library

### Standard Cost Items

Use **Master Cost Library → Standard Cost Items** to search and filter the governed enterprise item library.

The **Standard Item** column shows the item name, full standard description, populated descriptive attributes, attribute class, and any governed item attribute values. The **Project / Market** column and filter identify the applicable Project Type and Market Segment.

The register displays the calculated **Final Unit Rate** for each current revision. Coding Status remains available on the individual item detail page.

The detail page shows:

- item identity and enterprise code;
- the calculated Final Unit Rate based on current material and labor rates plus governed resource allowances;
- Project Type, Market Segment, description, classification, trade, UOM, and specification;
- PSMM classification where recorded;
- materials and their association details/current rates, plus labor, equipment, allowances, and productivity;
- code governance and revision status;
- approval and revision history.

Only the current Draft revision can be edited. Published definitions are changed by creating a successor revision through Governance, never by overwriting the published record.

#### Create a guided Draft

Users with both Standard Cost Item and Unit Rate management permissions can select **Create Standard Cost Item** and complete six guided steps:

1. Enter the identity and description.
2. Select a governed Project Type and its applicable Market Segment, then CSI, UniFormat, trade, UOM, specification, and scope values.
3. Add material variants and quantities, selecting exactly one primary material.
4. Choose either Manual Labor or Crew Derived labor. Manual labor requires positive days per item unit. Crew Derived requires an active crew, output, duration in days, source/reference, and effective date.
5. Optionally add equipment and the three governed allowances.
6. Select **Preview Draft**, resolve validation or evidence warnings, review the Final Unit Rate, and select **Save Atomic Draft**.

The save creates revision 01 as Draft / Ready for Code in one transaction. It does not generate the enterprise code, approve, publish, or map the item. Priced resources must use one currency. Equipment remains visible but unpriced until a governed equipment-rate structure exists.

### Materials

Materials provides searchable material codes, names, categories, groups, variants, UOMs, current rates, schedules, and rate history. Authorized maintainers can add/edit a material and activate or deactivate it. Historical rates are read-only here.

### Equipment

Equipment provides equipment codes, names, governed groups, scope/category information, status, and Standard Cost Item usage. Equipment costing remains unavailable until a governed equipment-rate structure exists; the system does not invent a rate.

### Labor

Labor provides governed crafts, categories, current and historical rates, rate components, aliases, status, and usage in Standard Cost Items and crews. Authorized maintainers can add/edit crafts and change active status.

### Crews

Crew records group Labor Master crafts and member quantities. The calculated daily crew cost is derived from current governed labor rates.

To maintain a crew:

1. Create or open a crew.
2. Add a Labor Master craft and quantity.
3. Edit member quantities when required.
4. Review the calculated daily cost and productivity usage.

Crew members cannot be hard-deleted because the authoritative table has no inactive flag. Adjust the composition through the supported edit workflow.

## 6. Unit Rate Build-Up

Use **Cost Management → Unit Rate Build-Up** to inspect calculated costs for Standard Cost Item revisions.

For a current Draft, authorized maintainers can add, edit, or select **Delete** beside Material, Manual Labor, Equipment, and Allowance components. Deletion opens a confirmation page and recalculates the Final Unit Rate after confirmation. Crew-derived labor cannot be deleted row by row; first use **Convert to Manual Labor** if manual maintenance is intended.

The calculation contains:

- material quantity multiplied by the current material-variant rate;
- labor days per item unit multiplied by the governed labor total rate;
- equipment quantities shown as context while governed rates are unavailable;
- Tools & Equipment, Other & Consumables, and Non-Material Activity allowances;
- reference baseline and calculated variance.

### Crew productivity

On a current Draft build-up, authorized users can add a day-based productivity record with an active crew, output per item UOM, duration in days, evidence source/reference, and effective date. Select **Preview and Apply Crew** to review each member craft, quantity, calculated days per item unit, current labor rate, and component cost before replacing the Draft labor rows.

Crew-derived labor is locked against direct edits to prevent duplicate costing. Editing the selected productivity or its crew marks the build-up **Reapply required**. Use **Convert to Manual Labor** only when the generated labor rows should be retained and maintained manually. Published revisions remain read-only, and hour-based productivity remains unavailable until hours per workday is governed.

Authorized users can add or edit components only on the current Draft revision. Missing resource rates and unrated equipment are displayed explicitly.

## 7. Rate Management and History

Rate records are append-only. Never replace a historical value to represent a new effective rate.

Authorized users may append:

- a new material schedule and rate;
- a new labor schedule and component totals;
- a Standard Cost Item rate observation with available project, location, date, source, basis, period, supplier, and validation context.

When a new material or labor rate becomes current, the previous record remains in history. Enter as much governed context as possible because benchmarking and intelligence confidence depend on these fields.

## 8. Governance Workflow

The Standard Cost Item workflow is:

`Draft → For Review → For Approval → Approved → Published`

### Cost Engineer or UCD Administrator

1. Complete the Draft definition and build-up.
2. Open the Standard Cost Item.
3. Select **Submit for Review**.

### Reviewer

1. Open **Governance → Review Queue**.
2. Inspect the definition, classifications, build-up, and history.
3. Recommend it for approval or return it with comments.

### Approver

1. Open **Governance → Approval Queue**.
2. Review the technical recommendation and governed code.
3. Approve, return, or publish as permitted.

Comments, users, timestamps, status transitions, and publication actions are retained in approval and audit history. A Published item is changed only by creating a new Draft revision.

## 9. Project Master

Projects provide the context for BOQs and observed rates.

### Creating a project

1. Open **Projects → Project Master**.
2. Select **Add Project**.
3. Enter a unique project code and project name.
4. Select governed project type and location where available.
5. Enter building type, area, floors, and dates as applicable.
6. Save.

The completion date cannot precede the start date.

### Deactivation and deletion

Use deactivation when a project must remain available for history but should no longer be used as active data.

Permanent deletion is available only to users with Project management permission and only when the project has no:

- BOQs;
- rate observations;
- source documents;
- project metrics.

The exact project code must be entered to confirm deletion. Referenced projects display their dependency counts and must be deactivated unless those dependencies are resolved through their governed workflows.

## 10. BOQ Management

### Creating a BOQ

1. Create the Project first.
2. Open **Projects → BOQ**.
3. Select **Add BOQ**.
4. Select the Project and enter the unique BOQ code, name, currency, revision, and status.
5. Save.

BOQ items may be entered manually or imported while the BOQ is Draft or Validated.

### Manual BOQ items

Select **Add Item**, then provide the line number, description, quantity, UOM, optional rate/amount, references, and notes. If both rate and amount are entered, the system verifies the arithmetic.

### CSV/XLSX imports

Imports accept CSV or XLSX files up to 10 MB and 10,000 rows.

1. Open the BOQ and select **Import CSV/XLSX**.
2. Select the source file.
3. Upload and wait for validation.
4. Review the selected worksheet, reconciliation totals, unpriced count, staged rows, and validation messages.
5. Correct an invalid source file and upload it as a new batch.
6. Select **Import Validated Rows** only when the batch status is Ready.

If an active extraction model and private service are configured, the batch shows **Extraction Assistance** after deterministic parsing. A reviewer with both BOQ management and ML review access must accept, correct, or reject every proposal. Accepted/corrected fields are validated again; rejected proposals leave the parsed row unchanged. Import is disabled while proposals are pending. If ML is unavailable, the screen states that deterministic fallback was retained and normal import remains available.

The XLSX importer recognizes actual contractor BOQ patterns including multi-row headers, detailed sheets, hierarchical descriptions, split pricing, and controlled UOM aliases. Formula cells use workbook-cached values; the application does not calculate spreadsheet formulas.

An unpriced line remains unpriced. Do not interpret it as zero unless that is the approved commercial decision.

### Deactivation and deletion

Deactivation retains the BOQ and history.

Permanent deletion is restricted to Draft or Validated BOQs and requires the exact BOQ code. It removes that BOQ's:

- items;
- import batches and staging;
- candidate and selected mappings;
- mapping history;
- generated uploaded files.

The linked Project, Standard Cost Items, references, and master data are not deleted. Deletion cannot be undone.

## 11. BOQ-to-UCD Mapping

Use **Projects → BOQ Mapping** to monitor progress and map BOQ descriptions to exact Standard Cost Item revisions.

For each BOQ line:

1. Open the line.
2. Review existing candidates and the current selected mapping.
3. Search by enterprise code, UID, name, or description.
4. Confirm the UOM and revision state.
5. Add an appropriate candidate.
6. Select the candidate to create a Proposed mapping.
7. Confirm the mapping, or reject it with comments.
8. Reopen a rejected mapping if reassessment is required.

A selected candidate cannot be disabled. Every selection and status change is retained in mapping history.

If available, **Explainable Suggestions** opens Cost Intelligence with the BOQ description and UOM prefilled. Suggestions do not add, select, or confirm mappings automatically.

## 12. Benchmarking

Benchmarking compares historical observations by cost item, project, location, period, contractor/vendor, CSI division, trade, and UOM where data exists.

1. Open **Cost Intelligence → Benchmarking**.
2. Select the comparison dimension and optional filters.
3. Apply the benchmark.
4. Review sample count, minimum, P25, median, average, P75, maximum, and context coverage.
5. Inspect the underlying observations before making a decision.

The system keeps UOM and currency groups separate. A chart is shown only when one UOM is selected; combining unlike units would be misleading.

## 13. Cost Intelligence

Cost Intelligence is decision support, not an autonomous approval or mapping engine.

### Mapping suggestions

Enter a BOQ description and optional UOM. Results are ranked using explainable term coverage, specificity, phrase matching, and UOM agreement. Each result shows its score, confidence band, and explanation.

### Range and abnormal-rate signals

Comparable observations are grouped by classification, UOM, and currency. Eligible groups show P25, median, P75, confidence, and IQR-based abnormal-rate signals. A flagged rate is a prompt for review, not proof of an error.

### Trends

Annual trends appear only when authoritative dated rate observations exist. The application reports missing evidence instead of inventing dates or context.

No Cost Intelligence screen automatically changes master data or BOQ mappings.

## 14. ML Governance

Authorized users can open **Cost Intelligence → ML Governance** to prepare reproducible datasets. The governance screen does not train, activate, or deploy models. Phase 25 can only consume an already active Extraction model through the separately configured private service and requires row-by-row review in BOQ Management.

1. An `ml.train` user creates an Extraction, Mapping, or Resource Template dataset and a Draft version.
2. The system snapshots eligible source records with a cutoff, split group, immutable JSON, and SHA-256.
3. An `ml.review` user inspects each payload/label and approves, rejects, or reopens it with comments.
4. After all labels are reviewed and at least one is approved, an `ml.train` user freezes the version and queues its export.
5. An operator runs `php index.php ml_worker run` on the server.
6. After the checksum-verified export succeeds, an `ml.review` user approves the dataset version.

Rejected labels are excluded from export but remain in append-only feedback history. Frozen and Approved versions cannot be edited. Model registry information is read-only until the separately authorized lifecycle phase.

## 15. Elemental Costing

Open **Cost Management → Elemental Costing** to manage high-level project cost plans by Project Type, Market Segment, and UniFormat element. Elemental plans are separate from Standard Cost Items: an elemental line records a direct amount or rate against a Level 3/4 element and does not select or total SCI revisions.

Users with management access can create a Draft plan, add scoped element lines, and submit it for approval. Approvers can approve the submitted plan; authorized managers can then publish or archive it. The plan page shows elemental totals and normalized cost metrics based on the selected cost basis.

Use **Elemental Rate Evidence** to append benchmark observations. Existing evidence is retained; creating a newer current observation for the same scope replaces only its current designation. Administrators maintain allowed project-market/UniFormat combinations in **Scope Applicability** and maintain basis definitions under **Reference Tables → Elemental Costing → Elemental Cost Bases**.

## 16. Administration

### User-role assignments

Users with `users.manage` can open **Administration → User Role Assignments**, select a user, and assign one or more active roles. Use the least privilege required for the person's work.

The system prevents removal of the final active System Administrator assignment.

### Roles and permissions

System Administrators can inspect roles, permission counts, and the permission catalog. Authorized role administrators may maintain non-protected assignments. Permission codes are application contracts and are not editable through the user interface.

### Recommended account practice

- Give each person an individual account.
- Require a password change after temporary-password distribution.
- Deactivate unused accounts or remove unnecessary role assignments.
- Never share the System Administrator account.
- Review role assignments periodically.

## 17. Common Messages

| Message/status | Meaning and response |
|---|---|
| HTTP 403 Forbidden | The account lacks the required permission. Ask an administrator to review the role; do not share credentials. |
| HTTP 405 Method Not Allowed | A protected action was opened incorrectly, commonly through a copied action URL. Return to the record page and use its button. |
| HTTP 409 Conflict/Blocked | The record state or dependencies prevent the requested action. Review the displayed reason. |
| HTTP 422 Confirmation Required | The entered project or BOQ confirmation code does not exactly match. |
| Forced Change Password | A temporary password is still active; complete the password form before continuing. |
| Import batch Invalid | Correct the source data and upload a new batch. |
| Missing Rates | One or more build-up resources have no governed current rate. |
| Review required/Low confidence | Evidence is insufficient for an automatic recommendation; perform professional review. |

## 18. Data-Governance Rules

- Prefer deactivation to permanent deletion when a record has operational or historical value.
- Never overwrite historical rates; append a new effective record.
- Keep UOM and currency contexts separate.
- Use governed references rather than free text where a selection exists.
- Review source documents and reconciliation before committing an import.
- Require meaningful comments for returns and rejections.
- Confirm the exact revision when mapping a BOQ item.
- Treat intelligence outputs as evidence for human review.
- Report suspected data or access issues to the UCD/System Administrator.

## 19. Support Information

When reporting an issue, provide:

- username and assigned role, but never the password;
- page/module name;
- record code or ID;
- action attempted;
- exact message and time encountered;
- screenshot where permitted;
- source filename for import issues, without emailing sensitive data unless approved.

Administrators can use the Governance Audit Trail and server logs to investigate controlled workflow and application errors.
