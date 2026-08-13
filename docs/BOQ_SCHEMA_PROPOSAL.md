# Phase 16 — BOQ Schema Design and Implementation Record

## Finding

The live `nexus_ucd` schema originally contained no BOQ header, BOQ item, import batch, import staging, or BOQ mapping structures. The user authorized this design, and Phase 16 created the four proposed tables through `database/phase16_boq_schema.sql`.

## Minimal Normalized Structure

### `boq_header`

One governed BOQ document belonging to a project.

| Column | Proposed type | Rules / purpose |
|---|---|---|
| `boq_id` | `BIGINT UNSIGNED` | Primary key, auto increment |
| `project_id` | `BIGINT UNSIGNED` | Required FK to `project_master.project_id` |
| `boq_code` | `VARCHAR(50)` | Required unique business identifier |
| `boq_name` | `VARCHAR(255)` | Required display name |
| `description` | `TEXT` | Optional document context |
| `document_reference` | `VARCHAR(255)` | Optional source/tender/contract reference |
| `currency_code` | `CHAR(3)` | Required ISO-style currency code |
| `revision_no` | `VARCHAR(20)` | Optional source-document revision |
| `boq_status` | `VARCHAR(30)` | `DRAFT`, `VALIDATED`, `ACTIVE`, or `ARCHIVED` |
| `is_active` | `TINYINT(1)` | Reversible application availability |
| `created_at`, `updated_at` | timestamp columns | Creation and last-change timestamps |
| `created_by`, `updated_by` | `BIGINT UNSIGNED` | Application actor identifiers; consistent with existing non-FK actor fields |

Indexes: unique `boq_code`; `(project_id, boq_status, is_active)`.

### `boq_item`

One normalized priced line within a BOQ. Section headings and blank source rows remain staging-only rather than becoming priced items.

| Column | Proposed type | Rules / purpose |
|---|---|---|
| `boq_item_id` | `BIGINT UNSIGNED` | Primary key, auto increment |
| `boq_id` | `BIGINT UNSIGNED` | Required FK to `boq_header.boq_id` |
| `line_no` | `INT UNSIGNED` | Required stable order within the BOQ |
| `item_reference` | `VARCHAR(100)` | Optional source item number/code |
| `section_reference` | `VARCHAR(255)` | Optional source section/path |
| `item_description` | `TEXT` | Required BOQ description |
| `uom_id` | `BIGINT UNSIGNED` | Nullable FK to `ref_uom.uom_id` |
| `source_uom_text` | `VARCHAR(50)` | Preserves the original UOM text for traceability |
| `quantity` | `DECIMAL(18,6)` | Required, non-negative |
| `unit_rate` | `DECIMAL(18,6)` | Nullable, non-negative source rate |
| `line_amount` | `DECIMAL(18,2)` | Nullable source amount; validation compares it with quantity × rate |
| `notes` | `TEXT` | Optional source/user notes |
| `is_active` | `TINYINT(1)` | Reversible availability |
| `created_at`, `updated_at` | timestamp columns | Creation and last-change timestamps |
| `created_by`, `updated_by` | `BIGINT UNSIGNED` | Application actor identifiers |

Indexes: unique `(boq_id, line_no)`; `(boq_id, item_reference)`; `uom_id`.

The item primary key is the stable Phase 17 mapping anchor. Mapping columns do not belong in this table.

### `boq_import_batch`

One uploaded CSV or Excel workbook and its processing result.

| Column | Proposed type | Rules / purpose |
|---|---|---|
| `boq_import_batch_id` | `BIGINT UNSIGNED` | Primary key, auto increment |
| `boq_id` | `BIGINT UNSIGNED` | Required FK to `boq_header.boq_id` |
| `original_file_name` | `VARCHAR(255)` | Sanitized client filename for display |
| `stored_file_name` | `VARCHAR(255)` | Server-generated non-public filename |
| `file_type` | `VARCHAR(10)` | `CSV` or `XLSX` |
| `file_sha256` | `CHAR(64)` | Upload identity/integrity check |
| `import_status` | `VARCHAR(30)` | `UPLOADED`, `VALIDATING`, `INVALID`, `READY`, `IMPORTED`, or `FAILED` |
| `total_rows`, `valid_rows`, `invalid_rows`, `imported_rows` | `INT UNSIGNED` | Batch result counters |
| `uploaded_at` | `DATETIME` | Upload timestamp |
| `uploaded_by` | `BIGINT UNSIGNED` | Application actor identifier |
| `completed_at` | `DATETIME` | Nullable processing completion timestamp |

Indexes: `(boq_id, uploaded_at)`; `file_sha256`; `import_status`.

### `boq_import_staging`

One parsed source row, retained until the batch is accepted or archived. It supports validation and the error report without a redundant error table.

| Column | Proposed type | Rules / purpose |
|---|---|---|
| `boq_import_staging_id` | `BIGINT UNSIGNED` | Primary key, auto increment |
| `boq_import_batch_id` | `BIGINT UNSIGNED` | Required FK to `boq_import_batch` |
| `source_sheet` | `VARCHAR(100)` | Nullable Excel sheet name |
| `source_row_no` | `INT UNSIGNED` | Required original row number |
| `raw_row_data` | `LONGTEXT` | JSON-encoded original cell values for traceability |
| `item_reference`, `section_reference` | text columns | Parsed source identifiers |
| `item_description` | `TEXT` | Parsed description |
| `source_uom_text` | `VARCHAR(50)` | Parsed UOM text |
| `matched_uom_id` | `BIGINT UNSIGNED` | Nullable FK to `ref_uom.uom_id` after validation |
| `quantity_text`, `unit_rate_text`, `line_amount_text` | `VARCHAR(100)` | Original numeric text before conversion |
| `quantity` | `DECIMAL(18,6)` | Nullable validated quantity |
| `unit_rate` | `DECIMAL(18,6)` | Nullable validated rate |
| `line_amount` | `DECIMAL(18,2)` | Nullable validated amount |
| `validation_status` | `VARCHAR(20)` | `PENDING`, `VALID`, `INVALID`, `IMPORTED`, or `SKIPPED` |
| `validation_errors` | `TEXT` | JSON array of row-level error codes/messages |
| `boq_item_id` | `BIGINT UNSIGNED` | Nullable FK to the committed `boq_item` |
| `created_at`, `validated_at`, `imported_at` | datetime/timestamp columns | Processing timestamps |

Indexes: unique `(boq_import_batch_id, source_sheet, source_row_no)`; `(boq_import_batch_id, validation_status)`; `boq_item_id`.

## Relationships

```text
project_master
  └── boq_header
        ├── boq_item
        └── boq_import_batch
              └── boq_import_staging
                    └── boq_item (after successful commit)
```

Recommended delete behavior is restrictive for project/header/item relationships. Staging may use batch-owned cascading deletion only if destructive cleanup is separately authorized. Application workflows should archive or deactivate governed records rather than hard-delete them.

## Validation and Import Flow

1. Create or select a Draft BOQ header.
2. Accept only CSV/XLSX with configured size and MIME limits; store outside the public web root using a generated filename.
3. Create an import batch and parse every source row into staging without writing BOQ items.
4. Validate required description, positive/non-negative numeric rules, UOM matching, duplicate source rows, amount arithmetic, and row limits.
5. Present an error report from `validation_status = 'INVALID'` and `validation_errors`; allow a corrected file to be uploaded as a new batch.
6. Import only a wholly `READY` batch in one transaction, creating ordered BOQ items and linking each staging row to its item.
7. Mark the batch `IMPORTED`; never silently replace existing governed BOQ items.

## Authorization Record

The user authorized implementation by instructing the agent to proceed after receiving the schema-gate summary. A PHP 7.4-compatible PhpSpreadsheet version was rejected because Composer identified active security advisories; therefore, XLSX support uses a local read-only ZIP/XML parser that reads the first worksheet and does not evaluate formulas or macros. Phase 17 mapping tables remain deliberately excluded.
