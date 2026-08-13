# Cost Intelligence

Phase 19 adds a read-only, explainable intelligence layer over authoritative Project Nexus UCD data.

## Mapping Suggestions

`/cost-intelligence/suggestions` ranks current active standard-item revisions using a deterministic 100-point score:

- query-term coverage and specificity: 75 points;
- normalized phrase match: 10 points;
- matching governed UOM: 15 points.

Item names/descriptions, configured keywords, and approved synonyms are searchable. Every result states matched terms and score components. Suggestions never create a candidate, select a BOQ mapping, or modify master data; users return to the governed BOQ mapping workflow to act.

## Range and Outlier Signals

Rate observations are grouped by CSI division, trade, UOM, and currency. Groups require at least five observations. P25, median, and P75 define the middle-50% range. Rates outside `P25 - 1.5 × IQR` and `P75 + 1.5 × IQR` are flagged for review, not declared erroneous.

Range confidence combines sample size (35), governed source type (15), rate basis (15), validation (20), dated evidence (10), and location coverage (5). The score is interpreted using `ref_confidence_band`; only bands marked recommendable may be presented as such.

## Trends and Current Limitations

Annual trends use dated rate observations and remain separated by division and UOM. The current 276 baseline observations have no dated/project/location context and no validated records, so no trend is inferred and current ranges are explicitly provisional. This limitation will resolve through governed append-only Rate Management data rather than fabricated values.
