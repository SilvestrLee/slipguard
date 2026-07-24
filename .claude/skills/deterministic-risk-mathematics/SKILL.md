---
name: deterministic-risk-mathematics
description: >
  This skill should be used automatically for SlipGuard's Risk Intelligence Engine
  work: taxonomy, sport/market normalization, snapshot construction, factor
  definitions, thresholds, weights, caps, weakest-leg logic, data-quality scoring,
  reason codes, fixed-precision odds/decimal calculations, and mathematical test
  vectors. Trigger phrases include "risk engine", "normalize this market", "factor
  weight", "risk score", "reason code", "market taxonomy", "weakest leg". Do not
  use for ordinary CRUD, unrelated UI work, or anything that predicts a sporting
  outcome — that is out of scope for every version of this engine.
user-invocable: false
---

# Deterministic Risk Mathematics (SlipGuard)

## The One Rule That Overrides Everything Else

The engine measures structural decision risk. It never predicts outcomes, never says a bet is "safe," and never uses AI/ML/fuzzy-matching anywhere in scoring or normalization. This is ADR-002 (locked) and repeated throughout `docs/03-data-science/RISK_ENGINE.md`. If a task description implies guessing, inferring intent, or classifying "close enough," stop and treat it as out of scope rather than approximating.

## Current State of the Engine (check before assuming otherwise)

- **Domain validation and lifecycle**: `App\Domain\BettingSlip\*` — a slip must be `Ready` before anything downstream touches it; it becomes `Analysed` only via `BettingSlip::markAnalysed()`, which nothing currently calls.
- **Taxonomy and normalization**: `App\Domain\Risk\Taxonomy\*` and `App\Domain\Risk\Normalization\*`. Football-only in v1 (`FootballMarketTaxonomyV1::VERSION === '1.0'`), nine market families with fixed complexity, exact alias matching, controlled pattern extraction only (no Levenshtein, no ML). Full policy record: `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md`.
- **Not yet implemented**: any actual score, band, weakest-leg selection, data-quality formula, `SlipAnalysis`/`LegAnalysis` persistence, input fingerprinting, or idempotency handling. These require an approved Stage 2 rule set before any code is written — see `docs/03-data-science/RISK_ENGINE.md`'s Blocker section.

## Non-Negotiable Technical Rules

1. **Fixed-precision decimals, never floats**, for odds and any business-critical calculation. `brick/math` (`BigDecimal`) is the proposed but not-yet-added dependency for this — check `composer.json` before assuming it exists.
2. **Four independent version axes**: engine version, rule-set version, market-taxonomy version, input-schema version. Do not conflate them, and do not add a fifth "slip version" — ADR-006 explicitly rejected content-versioning `BettingSlip` itself; the lifecycle already makes a locked slip immutable.
3. **Reason codes come from one approved catalogue.** Check `docs/03-data-science/RISK_ENGINE.md` before inventing a new code; if a new one is genuinely needed (e.g. `MARKET_UNRECOGNIZED`), add it there, don't let it exist only in code.
4. **The pure calculator (when it exists) must not touch the database, the clock, randomness, or feature flags during calculation** — orchestration and persistence are a separate layer. This is what makes reproduction tests possible.
5. **Idempotency**, once implemented, needs a database-level unique constraint on the request identifier, not just an application-level check-then-insert — a check-then-insert race is a classic TOCTOU bug under concurrent duplicate requests.

## Normalization-Specific Rules

- Prefer the **selection text** over the **market text** when both could indicate the same fact (e.g. direction/line for a total-goals market) — the selection is what the user actually chose; the market label can be ambiguous (e.g. an alias literally containing both "over" and "under"). This exact bug was found and fixed once in `NormalizeFootballMarket`.
- Distinguish `unsupported` (a real, named thing intentionally out of scope, e.g. Tennis) from `unrecognized` (matched nothing at all) — collapsing the two loses a real signal for future data-quality scoring.
- A family that offers no extracted facts beyond its market code (Match Result, Double Chance, Draw No Bet, Half-Time Result, Half-Time/Full-Time, Team Total Goals) reports `NormalizationStatus::Complete` by design — this is not a bug, it means nothing further is expected of that family in the current version. Confirm this is still correct before Stage 2 assumes those families carry structured facts they don't currently have.
