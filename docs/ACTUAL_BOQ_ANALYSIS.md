# Actual BOQ Workbook Analysis

Analyzed read-only on 12-Aug-2026:

- `FINAL-NEGO-FUTURA-SHORES-BLDG-A_BOQ-_-BAPCDC_02.18.2026 (1).xlsx`
- `GENSAN-BLDG-B-REVISED-BID-PROPOSAL-BREAKDOWN-235M-(UPDATED).xlsx`

## Observed Structures

| Workbook | Detail Worksheet | Normalized Lines | Priced Total | Declared Total | Unpriced Lines |
|---|---:|---:|---:|---:|---:|
| Futura Shores Building A | `BOQ` (second sheet) | 537 | 325,602,095.04 | 325,602,095.00 | 128 |
| Gensan Building B | `Breakdown` | 307 | 234,434,418.00 | 235,000,000.00 | 0 |

Futura has a 0.04 normalization difference after storing 537 line amounts at the database's two-decimal precision; this falls within the displayed rounding tolerance. The Gensan workbook contains a material source-level difference of 565,582.00 between recognized priced detail lines and its declared total. The application reports material variance and does not manufacture an adjustment row.

Both formats use multi-row headers, merged cells, hierarchy across description columns C-F, material/labor split pricing, subtotal/total rows, and source text such as Included, OSM, Tradecon, and By Others. Futura also contains owner-supplied and trade-contractor quantities without prices.

## Import Changes

- Inspect all worksheets and select the recognized sheet with the most item rows.
- Detect the BOQ header row instead of requiring row 1.
- Flatten section hierarchy into `section_reference` and retain source worksheet/row traceability.
- Derive total unit rate as amount divided by quantity when a priced amount is present.
- Preserve intentionally unpriced lines with nullable rate/amount.
- Match controlled source aliases including `nos`, `lm`/`ln.m.` as linear meter, `m²`, `m³`, `sets`, `pcs`, `kgs`, `months`, `units`, `assy`, and `bags`.
- Persist selected worksheet, priced total, declared total, variance, and unpriced-row count in the import batch report.

Formula expressions are not evaluated. Existing cached workbook values are read, and this limitation is displayed in the normalization report.
