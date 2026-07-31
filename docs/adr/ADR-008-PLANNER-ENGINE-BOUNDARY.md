# ADR-008 — Planner Engine Boundary

**Status:** Accepted (Product Office + Architecture Office, `PO-U07-AC-001`, 2026-07-27) — no amendment required; found fully compatible with every Product Decision recorded since it was authored (`docs/02-architecture/U-07.4-ADR-RECONCILIATION-REPORT.md`).

## Context

`docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` proposes a new Planner bounded context (`app/Domain/Planner/`) for Programme U-06 — Intelligent Accumulator Planning. Before any implementation begins, the same discipline `ADR-007` established for the Risk Engine (a pure calculator, wrapped by persistence, never invoked directly by presentation) needs to be decided for the planner's own evaluation logic, or the boundary will be improvised ad hoc during implementation instead of decided up front.

## Proposed Decision

The planner's candidate-evaluation logic — whatever compares, scores, or ranks candidate legs within a `PlannerSession` — has exactly one responsibility: evaluate candidates using the existing, already-bounded `App\Domain\Risk\Engine\CalculateStructuralRisk`, and never reads/writes the database, HTTP request, UI framework, clock, queue, event, cache, session, or auth layer. Identical constraint to ADR-007 §Decision, applied to a new domain.

**Proposed required call flow:** `PlannerSession` (candidates) → per-candidate `CalculateStructuralRisk` call (read-only, no new math) → `PlannerEvaluationResult` (in-memory) → orchestration layer persists `PlannerSelection.evaluation_snapshot` → presentation reads persisted snapshots only.

**Proposed forbidden call flow:** identical in shape to ADR-007's — no presentation surface may invoke `CalculateStructuralRisk` directly for planner purposes, and no planner screen may recalculate on read.

**Immutability:** a `PlannerRegenerationEvent` is a historical record once written, identical in spirit to `SlipAnalysis`'s immutability under ADR-007.

## Open Dependency

This ADR's "Regenerate" semantics depend on resolving the same re-analysis gap ADR-007 already flagged as unresolved (`slip_analyses.betting_slip_id`'s unique constraint, `Analysed` as a terminal lifecycle state). This ADR does not resolve that gap — it is named here as a precondition, per `U-06.1-ARCHITECTURE-DISCOVERY.md` §9 R2.

## Consequences (if accepted)

- The planner never becomes a second place where structural risk is calculated — it always calls the one certified engine, preserving Data Science Lab's sole mathematical authority (`docs/offices/DATA_SCIENCE_LAB.md`).
- Every future planner-adjacent surface is bound by the same forbidden call flow ADR-007 already established, without re-deriving it.
- Does not resolve, and should not be read as resolving, the Model A/B scope fork in `U-06.1-ARCHITECTURE-DISCOVERY.md` §3 — this ADR applies to either model equally.

## Status Note

**Accepted** — `PO-U07-AC-001` (2026-07-27), recorded in `docs/00-governance/DECISION_LOG.md`. Product Office reviewed and agreed with Architecture Office's `U-07.4` finding that this ADR required no reconciliation. The "Proposed required call flow," "Proposed forbidden call flow," and "Open Dependency" language elsewhere in this document describe this ADR's own history accurately and are retained as originally written; acceptance ratifies the decision, it does not require rewording it.
