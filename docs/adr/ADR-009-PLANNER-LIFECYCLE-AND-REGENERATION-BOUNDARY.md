# ADR-009 — Planner Lifecycle & Regeneration Boundary

**Status:** Accepted (Product Office + Architecture Office, `PO-U07-AC-001`, 2026-07-27). `PD-009` (2026-07-26) closed this ADR's one outstanding sub-decision (Export Option 2). No open item remains.

## Context

`ADR-008` established that the planner's evaluation logic must be exactly as pure as the Risk Engine, but explicitly declined to resolve one dependency: whether "Regenerate" is reachable at all, given `ADR-007`'s known gap (`SlipAnalysis` is one-per-slip; `Analysed` has no path back to `Ready`). `docs/02-architecture/U-07.1-PLANNER-ORCHESTRATION-ARCHITECTURE.md` §3 resolves this for Capability A. This ADR records the resulting lifecycle and call-flow decision.

## Decision

**Lifecycle** (`PlannerSessionStatus`, mirroring `BettingSlipStatus`'s own shape):

```text
Draft      -> [Evaluated, Abandoned]
Evaluated  -> [Evaluated, Complete, Abandoned]
Complete   -> [Evaluated, Exported, Abandoned]
Exported   -> []
Abandoned  -> []
```

**Required call flow:**
`Ready BettingSlip` → seed `PlannerSelection` rows (snapshot, not a live reference) → `RankLegsByStructuralWeakness` (read-only, unmodified engine) → persist one immutable `PlannerRegenerationEvent` per evaluation cycle → presentation reads only the latest event. Every lock/unlock/remove/replace edit re-runs evaluation and appends a new event; no event is ever mutated or deleted.

**Export (the only point of contact with `BettingSlip`, at most once per session) — decided (`PD-009`, 2026-07-26):** the source slip is never transitioned out of `Ready` at all; a new `BettingSlip` is created (via the existing `SaveBettingSlip` action, on a fresh `Draft` record) from the session's final `PlannerSelection` set, and the `PlannerSession` records which slip it exported *to* (a new, simple, nullable foreign key) as well as which slip it was seeded *from*. The original slip is left untouched, permanently — it continues to represent the customer's initial submission; the new slip represents the customer's approved Planner outcome. Both options share the same precondition, unaffected by this choice: `SlipAnalysis` and `AnalyzeBettingSlip` are never invoked by the planner at any point; a session can only be seeded from a `Ready`, not-yet-`Analysed` slip, which is what makes `ADR-007`'s re-analysis gap inapplicable here rather than resolved by a schema change.

**Decision history:** `U-07.4` (2026-07-26) found this ADR's original single-option description (silently written before `PD-008` existed) in tension with `PD-008`'s "baseline reference"/"never rewrites history" language, and presented two fully-specified alternatives — update-in-place, or export-creates-a-new-slip — without choosing between them, since the choice was a product judgement, not an architectural one. `PD-009` (2026-07-26, `docs/00-governance/DECISION_LOG.md`) selected the latter directly, on exactly those grounds: "every approved Planner outcome represents a new decision" and "customers shall always be able to distinguish between what they originally intended to bet [and] what they ultimately chose after Planner guidance." No further choice remains on this point.

**Forbidden call flow:** identical in shape to `ADR-007`/`ADR-008` — no presentation surface may invoke `CalculateStructuralRisk` or `RankLegsByStructuralWeakness` directly for planner purposes, and no planner screen may recalculate on read.

**Immutability:** a `PlannerRegenerationEvent`, once persisted, is a historical record — identical in spirit to `SlipAnalysis` under `ADR-007`.

**Source-slip lock representation (`PD-008`'s hard-lock rule, architectural definition only — added U-07.4):** whether a `BettingSlip` is currently locked against direct editing is a **computed fact, never a stored flag** — a slip is locked if and only if it has a `PlannerSession` referencing it (`PlannerSession.source_betting_slip_id`) whose `status` is not in a terminal state (`Exported`, `Abandoned`). This extends the same pattern `BettingSlip::analysisEligibility()` already established (E-03B) — a computed value object derived from current state, not a redundant column that could desync from it — rather than introducing a new mechanism. Concretely: `BettingSlip` gains an equivalent computed check (e.g. `plannerLockStatus()`, naming is Engineering's call) that E-03's builder consults before permitting an edit; no new stored column, no event, no signal is needed, and the lock releases itself automatically the instant the referencing session reaches a terminal status, because the check is re-evaluated fresh every time rather than cached. What the customer sees when an edit is blocked, and the exact copy involved, is explicitly **not** decided here — that is UX Studio's domain, unchanged.

**Regeneration cycle count (`DR-02`, clarification only — added U-07.4):** the "cycle" a regeneration limit would count is exactly the existing `PlannerSelection`/`PlannerRegenerationEvent` `sequence_number` (§ above) — no new counter is introduced regardless of what numeric limit, if any, Product Office sets for `DR-02`.

## Consequences

- `ADR-008`'s Open Dependency is resolved for Capability A: regeneration is reachable today, using only already-legal `BettingSlipStatus` transitions, with zero change to that enum or to `SaveBettingSlip`.
- The planner never touches `SlipAnalysis`'s unique-per-slip constraint or the `Analysed` terminal state — the two systems remain fully decoupled except at the single, one-directional export step, exactly as `U-06.1` §4 originally proposed.
- If a future capability needs to re-plan an *already-planned-and-exported* slip, that slip must be a **new** or **re-opened-via-Archive** `BettingSlip`, not a resurrection of the exported one — consistent with the existing immutability rule for `Analysed` slips, not a new one invented here.
- **Consequence of `PD-009`'s Export Option 2:** the source slip is never consumed by exporting — it remains `Ready`, fully usable, indefinitely. A customer may therefore end up with the original slip and one or more exported slips (one per completed session, since a session exports at most once) coexisting side by side, each independently `Analyse`-able through the existing, unchanged E-05 report flow. This is the direct, intended effect of "every approved Planner outcome represents a new decision" (`PD-009` Principle 3), not an incidental side effect.
- This ADR does not decide any customer-facing stopping, completion-suggestion, or abandonment-recommendation policy — see `U-07.1-PLANNER-ORCHESTRATION-ARCHITECTURE.md` §8's Open Decisions Register (OD-1–OD-3). Those remain Product Office / Compliance Office decisions regardless of this ADR's acceptance.

## Status Note

**Accepted** — `PO-U07-AC-001` (2026-07-27), recorded in `docs/00-governance/DECISION_LOG.md`. Fully reconciled against `PD-007`/`PD-008` under `U-07.4`, its one remaining open item (Export Option 1/2) closed by `PD-009` (Option 2), and formally accepted by Product Office alongside `ADR-008`. Per the acceptance's own terms: this ADR, together with `ADR-008`, `PD-007`, `PD-008`, and `PD-009`, now forms the constitutionally frozen Planner architecture. Future changes require a new Product Office strategic decision, a verified production defect, a material regulatory requirement, or an approved strategic programme — not routine implementation.
