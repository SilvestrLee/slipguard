# ADR-007 — Analysis Persistence Boundary

**Status:** Accepted (Product Office + Architecture Office, 2026-07-25)

## Context

The Deterministic Risk Engine (E-06B) intentionally produces an in-memory `RiskAnalysisResult` only — no analysis exists beyond the request lifecycle. Sprint E-06C adds persistence. Before that work was written up, Product Office and Architecture Office jointly defined the permanent boundary between the engine and everything that stores, retrieves, or presents its output, so future sprints (Dashboard, History, Journal, Risk Report, API, premium features) can't drift into calling the engine directly or mutating a persisted result.

## Decision

The Risk Engine (`App\Domain\Risk\Engine\CalculateStructuralRisk` and everything it calls) has exactly one responsibility — calculate deterministic structural risk from a `NormalizedBettingSlip` — and never reads/writes the database, HTTP request, UI framework, clock, queue, event, cache, session, or auth layer. This was already true of the E-06B implementation; this ADR makes it a permanent constraint rather than an incidental property.

**Required call flow** (no alternative permitted):
Ready Slip → Normalize → `CalculateStructuralRisk` → `RiskAnalysisResult` → `AnalyzeBettingSlip` (persist) → mark slip Analysed → present result.

**Forbidden call flow** (permanent, applies to every future sprint): Dashboard/History/Journal/Report screens must never invoke the engine directly, recalculate on read, or re-run the engine on a customer-triggered refresh. Every presentation surface reads persisted `SlipAnalysis`/`LegAnalysis` records, never live calculations.

**Layer responsibilities:**
1. Betting Domain — lifecycle, validation, ownership, eligibility, normalization input. Produces a Ready slip. Never calculates risk.
2. Normalization — sport/market recognition, taxonomy lookup. Produces `NormalizedBettingSlip`. Never persists, never scores.
3. Risk Engine — factors, weights, reason codes, data quality, bands, trace. Produces `RiskAnalysisResult`. Never touches persistence, HTTP, UI, or any Laravel infrastructure concern.
4. Analysis Persistence (`App\Actions\Analysis\AnalyzeBettingSlip`) — receives a `RiskAnalysisResult`, persists `SlipAnalysis`/`LegAnalysis` (trace, reason codes, engine/rule-set/taxonomy/input-schema versions, timestamp included), returns the persisted record. Never modifies calculations, recalculates factors, or reinterprets results.
5. Presentation (Dashboard, History, Risk Report, Journal, API, exports, notifications, premium reports — none built yet) — reads persisted analyses only. Never invokes the engine or mutates a persisted analysis.

**Immutability:** once persisted, a `SlipAnalysis`/`LegAnalysis` pair is a historical record, not a working document. Score, bands, trace, reason codes, and every version field are fixed at creation.

**Traceability:** every persisted analysis must carry engine version, rule-set version, taxonomy version, input-schema version, and a timestamp — four independent axes, not one. (`engine_version` was missing from E-06C's original migration; added under this ADR — see `CHANGELOG.md`.)

**Journal/Dashboard/History (future sprints, architectural constraint only):** journal entries will reference `SlipAnalysis`, not `BettingSlip`, so a journal entry always reflects the exact analysis a customer saw. Dashboard/History will read persisted `SlipAnalysis` records, never run the engine when browsing.

**Weakest-leg ranking is explicitly out of E-06C's scope** — it consumes stored `LegAnalysis` rows in its own future sprint (E-06D; see `TASKS.md`'s Naming note), not persistence logic itself.

## Known Gap at Time of Acceptance — Re-analysis

This ADR states the general principle that re-analysing the same slip creates a new, independent record with historical records left untouched. As implemented today, this is **not yet reachable**: `slip_analyses.betting_slip_id` is a unique database constraint (one analysis per slip, ever) and `BettingSlipStatus::allowedTransitions()` makes `Analysed` terminal except for `Archived` — there is no path back to `Ready` once analysed, so `AnalyzeBettingSlip` can never run twice for the same slip. Enabling real re-analysis requires changing both the schema (drop the unique constraint, likely move `BettingSlip::analysis()` to a `latestOfMany()` `HasOne` alongside a plain `HasMany`) and the locked E-03B lifecycle rule that treats `Analysed` as terminal. That is a deliberate, separate decision, not implied by this ADR — flagged here rather than silently implemented or silently left inconsistent. No code changed under this ADR to close this gap; it awaits an explicit Product Office decision.

## Consequences

- `App\Domain\Risk\Engine\CalculateStructuralRisk` gets a versioned identity of its own (`ENGINE_VERSION`), separate from `RuleSet2026_1::VERSION` — a pure engine refactor can now bump one without touching the other.
- Every future sprint that touches Dashboard, History, Journal, Risk Report, API, or premium features is bound by the Forbidden Call Flow above without needing to re-derive it — this ADR is the standing reference.
- The re-analysis gap above is tracked, not resolved; it blocks nothing in E-06C's actual (single-analysis-per-slip) scope but must be revisited before any feature that implies "analyse again."
