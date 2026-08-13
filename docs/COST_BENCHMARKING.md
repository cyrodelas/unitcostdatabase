# Cost Benchmarking

Phase 18 benchmarks append-only `cost_item_rate_history` observations without creating a second analytical datastore.

## Dimensions

- Exact cost item identity across revisions
- Project
- Governed or legacy location
- Governed price period or rate-date year
- Contractor/vendor
- CSI division
- Trade

Filters also include UOM, date range, and validation state. Each comparison group is split by UOM and currency. Charts require a single selected UOM so incomparable quantities are never plotted on one rate scale.

## Statistics

Each group reports observation count, minimum, 25th percentile, median, average, 75th percentile, maximum, project/location/contractor coverage, and date range. The underlying observations remain visible for traceability.

## Current Coverage

The current 276 records are reference baselines across 276 cost items. None currently has project, location, period/date, contractor, currency, or validated status context. The UI reports these gaps explicitly; those dimensions will populate automatically as governed observations are appended through Rate Management.
