# Performance and Index Review

Phase 20 reviewed the live indexes against authentication/RBAC, revision history, rate analytics, BOQ import/mapping, and the 43,768-row location reference queries.

## Findings

- RBAC joins are covered by user/role composite primary keys and foreign-key indexes.
- Rate history has revision/date plus project, location, source, basis, period, validation, and rate-type indexes.
- BOQ headers/items/imports/mappings have project/status, header/date, batch/status, item/line, candidate/revision, mapping/status, and history/item/time indexes.
- Standard revisions have current-item, workflow-status, classification, UOM/foreign-key, and unique code/revision indexes.
- Location paging/search uses database-side limits and indexed PSGC/name/parent/reference fields; it no longer sends the entire location table to the browser.

No new index was justified by the current query plans and data volumes, so Phase 20 makes no schema change. Free-text `%term%` matching cannot use an ordinary B-tree index; if the cost-item library grows materially, use a separately approved full-text/search design rather than speculative indexes.

Client-side DataTables remain appropriate for the current small/medium registers. New lists should move to server-side paging when response size or measured latency exceeds operational targets; location reference data already follows that pattern.
