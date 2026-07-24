# Review: Deterministic Risk Intelligence Engine — Stage 1 Contract

**Status of this document:** Engineering review only. No implementation has occurred — the source contract's own approval gate (Section 44) blocks score calculations, production rule weights, placeholder persistence, and weakest-leg generation until Product Office and Data Science approve. This file records that review so it isn't lost, per the same gate's explicit permission to "review, challenge assumptions, produce worked contract examples, identify Laravel implementation risks, propose corrections."

## Overall Assessment

This is a well-formed contract. It's internally consistent, and — importantly — it doesn't ask to change anything already locked (ADR-002 determinism, ADR-006 no-slip-versioning). Most of it either confirms what E-03B already built or adds a layer strictly above it. Two concrete conflicts need resolving, several Laravel-specific implementation risks are worth deciding now rather than mid-Stage-3, and a worked example follows at the end.

## Confirmed Alignment (no changes needed)

- **Lifecycle (§39)** matches `BettingSlipStatus` exactly: Draft ⇄ Ready → Analysed, any-of-three → Archived. `markAnalysed()` already only transitions from Ready, and `Analysed→Analysed` is already impossible (`allowedTransitions()` for Analysed is `[Archived]` only) — §26's "slip already analysed → no engine run" is already enforced today, for free.
- **Four separate version axes (§6)** — engine/rule-set/taxonomy/schema — directly vindicates ADR-006's reasoning that `BettingSlip` itself doesn't need versioning; the *analysis* carries all the versioning. No conflict, good confirmation.
- **Pure-calculator isolation (§29)** is consistent with ADR-002 and with how `SaveBettingSlip` already keeps persistence out of validation logic.
- **Display order as tie-breaker only, never a risk factor (§9.2)** — matches how `display_order` is already treated purely as a UI/ordering concern in E-03B.

## Two Concrete Conflicts to Resolve Before Sign-Off

### 1. §40 Deletion Policy directly contradicts what E-03B shipped

E-03B's `BettingSlipManagementTest`/`BettingSlipLifecycleTest` explicitly test and allow **deleting an Analysed slip** — treated as a deliberate, reviewed decision at the time ("Persistence Hardening... deletion allowed at any lifecycle stage") because no `SlipAnalysis` record existed yet to be orphaned by it.

§40 is right that this needs to change once analysis results exist: deleting a slip that a persisted `SlipAnalysis`/`LegAnalysis` references either cascade-deletes historical results or leaves a dangling reference — neither is acceptable for an auditable engine. Recommend adopting §40's rule (Analysed slips may not be deleted; archive instead). This is a domain-policy change, not "engine implementation," so it isn't blocked by §44's gate, but it wasn't implemented as part of this review — it needs an explicit go-ahead since it changes already-shipped, Product-Office-approved behavior.

### 2. The leg schema has no normalized taxonomy — §7–9's input contract assumes one exists

This is the bigger gap. `RiskAnalysisLegInput` expects `sportCode`, `marketCode`, `eventIdentifier`, and `normalizationStatus`. The actual `BettingSlipLeg` table only has **free-text** `sport`, `market_name`, `event_name` — whatever the user typed. There is no sport/market taxonomy anywhere in the codebase yet, and `eventStartTime` isn't even a column.

This was foreseen, not missed — `docs/03-data-science/RISK_ENGINE.md` already listed "market taxonomy used in MVP" as a required Data Science decision before analysis work begins, and the original E-03 blocker language in `docs/08-operations/DELIVERY_ROADMAP.md` gates E-04 on exactly this kind of formula/taxonomy approval. But it means Stage 1 can't be implemented as written without one of:

- **(a)** A snapshot-builder that classifies free text into codes at analysis time (fuzzy match "Football" → `football`, "Match Result" → some market code) — needs its own approved taxonomy and matching rules, and needs to produce `normalizationStatus: unrecognized` gracefully when it can't classify something (§27 already anticipates this).
- **(b)** Changing the slip builder to capture normalized codes at entry time (e.g., a sport dropdown, a market picker) instead of free text — a real UI/UX scope change to something E-03/E-03B already shipped and Product Office already closed out.

Recommendation: (a). It keeps manual entry simple (matches the original UX call for plain-language input) and confines the taxonomy problem to the engine's snapshot-construction step, which is where this contract already expects normalization to happen. But this needs an explicit decision, and probably its own small taxonomy-definition stage before Stage 2's math, since Stage 2 formulas (e.g., "market complexity classification") depend on markets already being classified.

## Laravel Implementation Risks / Proposed Corrections

1. **`DecimalValue` isn't a PHP primitive.** PHP has no native arbitrary-precision decimal type. Given §7.10's explicit "do not use floating-point arithmetic," recommend adopting `brick/math` (`BigDecimal`) rather than hand-rolling a wrapper around `bcmath` — mature, narrowly scoped to exactly this problem, widely used for money/odds-style arithmetic in PHP. This is a new dependency and should get the explicit justification `ENGINEERING_STANDARDS.md` asks for, but it clears that bar easily.

2. **Idempotency needs a database constraint, not just application logic.** §21/§23 describe the right behavior, but "check if `analysis_request_id` exists, then insert" is a classic TOCTOU race under concurrent duplicate requests (§23's exact scenario). The fix is a **unique index on `analysis_request_id`** at the database level, with insert-or-fetch-existing logic wrapped to catch the unique-constraint violation — not just `lockForUpdate()` on the slip row, which doesn't prevent two analysis rows from both being created if the request IDs happen to differ.

3. **Rule sets should be versioned PHP classes, not external config files**, despite §30's phrasing ("version-controlled structured files loaded and validated at boot"). PHP classes/enums get static analysis and unit-testability that YAML/JSON configs don't; `config()` is fine for *which* rule-set is active (an operational toggle), but the weights/thresholds themselves belong in code, consistent with ADR-002.

4. **`docs/02-architecture/DOMAIN_MODEL.md`'s `SlipAnalysis`/`LegAnalysis` sketch is now under-specified relative to this contract.** It has `summary_payload` (singular JSON blob) where this contract implies at least the input snapshot, the calculation trace, and `factor_results`/`reason_codes` as distinct structured data. That doc will need expanding once Stage 1 is approved — flagged now so it isn't a surprise at Stage 3.

5. **New reason code `MARKET_UNRECOGNIZED` (§27) isn't in the existing approved catalogue** (`docs/03-data-science/RISK_ENGINE.md` lists eight codes; this isn't one of them). §14.13 says reason codes must come from "the approved catalogue" — worth reconciling explicitly when Stage 2 lands rather than letting the two documents drift.

6. **`config('slipguard.max_legs')` (currently 20) is a domain/UI cap, not yet an "approved maximum" for engine purposes.** §33's 100ms performance target and §43's open "maximum supported leg count" decision both need a number — worth confirming whether 20 is that number or whether Data Science intends something else, since the domain layer is already enforcing 20 today.

## Worked Example

Given a hypothetical Ready slip (as it would actually persist today):

| sport | competition | event_name | market_name | selection_name | decimal_odds |
|---|---|---|---|---|---|
| Football | Premier League | Arsenal vs Chelsea | Match Result | Arsenal to win | 1.90 |
| Football | Premier League | Liverpool vs Everton | Over 2.5 Goals | Over 2.5 | 1.65 |
| Tennis | Wimbledon | Djokovic vs Alcaraz | Match Winner | Djokovic to win | 1.40 |

The `RiskAnalysisInput` this contract expects, built purely from what actually exists today:

```json
{
  "analysis_request_id": "01J...",
  "slip_id": "42",
  "user_id": "7",
  "input_schema_version": "1.0",
  "leg_count": 3,
  "combined_decimal_odds": "4.3889",
  "legs": [
    {
      "leg_id": "101", "display_order": 0,
      "sport_code": null, "competition_code": null,
      "event_name": "Arsenal vs Chelsea", "event_identifier": null, "event_start_time": null,
      "market_name": "Match Result", "market_code": null,
      "selection_name": "Arsenal to win", "decimal_odds": "1.90",
      "normalization_status": "unrecognized"
    }
  ]
}
```

Note every `*_code` field and `normalization_status` is forced to `null`/`unrecognized` for *every* leg today — that's the gap from point 2 above made concrete: right now, 100% of slips would enter the engine with unrecognized market/sport classification, which under §27's own policy is fine (limited analysis, reduced data quality, `MARKET_UNRECOGNIZED` emitted) but means **no slip could ever get a full data-quality score** until taxonomy classification exists. Worth confirming that's an acceptable interim state for early Engine v1 releases, or whether taxonomy needs to land before Stage 2 ships.

## Recommendation

Contract is sound and mostly implementable as written. Before Stage 1 sign-off, want explicit answers on: the deletion-policy conflict (agree with §40, ready to implement on go-ahead), where taxonomy classification lives, and confirmation that near-100%-unrecognized-market data quality is acceptable for early releases. Everything else here is refinement, not blockers.
