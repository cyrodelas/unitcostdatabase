# BOQ-to-UCD Mapping Schema

Phase 17 uses the stable `boq_item.boq_item_id` and maps it to an exact `standard_cost_item_revision.cost_item_revision_id`. Mapping to the revision rather than only the item preserves the governed definition that was selected even when a successor revision is later created.

## Tables

| Table | Purpose |
|---|---|
| `boq_mapping_candidate` | Candidate standard-item revisions for one BOQ item. Manual candidates use source `MANUAL`; nullable score, rank, and explanation fields reserve a controlled path for later system/AI suggestions. |
| `boq_item_mapping` | The single selected candidate for a BOQ item and its `PROPOSED`, `CONFIRMED`, or `REJECTED` status. |
| `boq_item_mapping_history` | Append-only record of candidate, selection, replacement, confirmation, rejection, and reopen actions. |

The selected mapping has a composite foreign key to candidate plus BOQ item, preventing a candidate belonging to one BOQ line from being selected for another. Candidate `(boq_item_id, cost_item_revision_id)` is unique.

## Phase Boundary

Phase 17 implements manual candidate selection and mapping review only. It does not generate similarity scores or AI candidates. Later suggestion services may populate candidate source/score/rank/explanation, but they must not select or confirm mappings automatically.
