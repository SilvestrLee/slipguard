# ADR-013 — Capability B (Market-Wide Planning) Planner Integration Boundary

**Status:** Accepted (`U-17.8` implementation-scoping plan, 2026-07-29 — formally accepted at the point implementation began to depend on it, matching the identical `ADR-008`/`ADR-009` precedent named in this document's own original Status Note below). Originally proposed by Architecture Office discovery, `docs/02-architecture/U-17.1-DETERMINISTIC-MARKET-INTELLIGENCE-PLATFORM-ARCHITECTURE.md` §7, 2026-07-29.

## Context

`SD-001` and Product Office's acceptance of `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` (`PO-U06.1-AC-001`, 2026-07-26) deferred "Capability B" — market-wide accumulator construction, where SlipGuard sources candidate selections from provider evidence rather than the customer's own entered slip — to "a future, separately-scoped programme." That programme (renumbered U-17 — see the architecture document's own naming-correction note) has now been authorized for architecture discovery only (`PO-U08.1-001-R1`). Before any implementation begins, the same discipline `ADR-008`/`ADR-009` established for Capability A's own boundary needs a decision for how a Capability B-generated candidate meets the existing, accepted, frozen Planner (Programme U-07) — or the boundary will be improvised ad hoc during implementation instead of decided up front.

The risk being guarded against: without an explicit boundary, a natural but wrong instinct is to build a second, parallel session/lifecycle/review system for "generated but not yet accepted" candidates — duplicating `PlannerSession`, `PlannerSelectionLockState`, compare/confirm/export semantics, and every test that already exists for them.

## Proposed Decision

**A Capability B-generated candidate never has its own session, revision, lock, compare, or export system.** It exists in exactly one of two states, with nothing in between:

1. **Pre-`BettingSlip`** — still inside Capability B's own construction pipeline (fixture discovery → eligibility → ranking → construction → whole-slip evaluation). No `PlannerSession` exists yet. No code in `app/Domain/Planner/*` or `app/Actions/Planner/*` is aware this candidate exists.
2. **An ordinary `Ready` `BettingSlip`, inside an ordinary Planner session** — once a customer accepts a constructed candidate as their starting point, a new `App\Models\BettingSlip` is created from its legs via the existing, unmodified `App\Actions\BettingSlip\SaveBettingSlip`, marked `Ready`, and a `PlannerSession` is started against it via the existing, unmodified `App\Actions\Planner\StartPlannerSession`. From this point, the session is indistinguishable from one seeded by a customer-entered slip — the same `PlannerSessionStatus` lifecycle, the same revision/lock/compare/confirm/export workflow, the same `ADR-008`/`ADR-009` boundary, entirely unmodified.

**Proposed required call flow:** Capability B's Accumulator Construction Service (out of this ADR's scope — see `U-17.1`'s own architecture document) → a new, narrow `CreateCandidateBettingSlip`-shaped action, converting the accepted candidate's legs into the attribute array `SaveBettingSlip` already accepts → `SaveBettingSlip` (unmodified) → `StartPlannerSession` (unmodified).

**Proposed forbidden call flow:** no new `PlannerSession`-adjacent status, table, or lock concept for "generated, pending customer acceptance." No Planner-side code may ever need to ask "was this session's Revision 1 generated or customer-entered" — by the time a `PlannerSession` exists, that question has no meaningful answer and should not be representable in the schema.

## Consequences (if accepted)

- Capability B's entire footprint on Programme U-07 is one new action and zero new Planner domain concepts — `ADR-008`/`ADR-009` require no amendment, exactly as `U-17.1`'s discovery recommends.
- The "original slip" panel's existing copy ("This slip stays exactly as you entered it... SlipGuard never changes it") remains honestly true for a generated candidate, since by the time a Planner session exists, the `BettingSlip` functionally *is* the customer's accepted starting point — no different in kind from one produced by Paste Text, PDF Upload, or Screenshot Upload, all of which already converge on the same `SaveBettingSlip` → Builder → Planner path (bounded-scope intake work, 2026-07-28).
- Every future Capability B consumer inherits Capability A's entire, already-tested revision/compare/confirm/export machinery for free, rather than needing a parallel implementation and a parallel test suite.
- Does not resolve, and should not be read as resolving, any of Capability B's own upstream questions (evidence sourcing, ranking mathematics, construction algorithm) — this ADR governs only the single handoff point where a Capability B candidate becomes a Capability A `BettingSlip`.

## Status Note

Recorded here per `U-17.1`'s own recommendation that this specific decision — unlike most of that document's more exploratory content — is durable and consequential enough to warrant a standalone ADR rather than living only in prose, matching exactly how `ADR-008`/`ADR-009` themselves originated as U-07's own discovery-phase boundary decisions before later formal Product Office/Architecture Office acceptance (`PO-U07-AC-001`, 2026-07-27). **Accepted 2026-07-29**, at the `U-17.8` implementation-scoping plan — the same point in the lifecycle `ADR-008`/`ADR-009` were themselves accepted, once code actually began depending on the boundary rather than merely describing it.
