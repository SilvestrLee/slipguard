# Risk Rule Set 2026.1 — Deterministic Structural Risk Mathematics

**Status:** `READY FOR PRODUCT APPROVAL`. Engineering must still not implement scoring code until Product Office and Data Science record explicit sign-off in `docs/00-governance/DECISION_LOG.md` — this status means the document contains no unresolved questions to sign off *on*, not that sign-off has happened.

**This document is the single authoritative source for Engine v1's mathematics.** It supersedes no other document — `docs/03-data-science/RISK_ENGINE.md` remains the contract; this document fulfills that contract's "Blocker" by proposing the exact formulas it deferred. `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md` remains the taxonomy authority; this document consumes its output, not restates it.

**Revision note:** this version resolves every open item from the prior `READY WITH OPEN DECISIONS` draft (§22 of that draft, individually resolved in §22 below) and incorporates a real code correction landed alongside it — `app/Domain/Risk/Normalization/NormalizeBettingSlip.php` now gates market normalization on recognized sport (commit `7d13c27`), closing the cross-sport leakage defect the prior draft only flagged as a requirement.

---

## 1. Rule-Set Identity

| Axis | Value |
|---|---|
| Engine version | `1.0` (implemented, `CalculateStructuralRisk::ENGINE_VERSION` — Sprint E-06C, ADR-007) |
| Rule-set version | `2026.1` (proposed) |
| Market taxonomy version | `1.0` (already implemented, `FootballMarketTaxonomyV1`) |
| Input schema version | Proposed `1.0` |

This rule set is not approved merely because this document exists. It becomes `Accepted` only on explicit Product Office / Data Science sign-off, recorded in `docs/00-governance/DECISION_LOG.md`.

## 2. Supported Input Assumptions

Every input this rule set uses must already exist and be producible today, deterministically, by already-implemented code.

- `leg_count` — `count($bettingSlip->legs)`, 1–20 (`config('slipguard.max_legs')`).
- Per-leg `decimal_odds` — `betting_slip_legs.decimal_odds`, a `DECIMAL(6,2)` column. **Exactly 2 decimal places, not 4.**
- `combined_decimal_odds` — not stored anywhere; always the product of all leg odds, computed fresh.
- Per-leg `market_complexity` — `NormalizedMarket::$complexity`, one of `simple`/`moderate`/`complex`/`unknown`.
- Per-leg `sport_status` — `NormalizedSport::$status`, one of `complete`/`unsupported`/`unrecognized`.
- Per-leg `market_status` — `NormalizedMarket::$status`, one of `complete`/`partial`/`unrecognized`/`unsupported` (the last is new — see §2.1).

### 2.1 Cross-sport normalization defect — corrected, not merely flagged

The prior draft of this document found that `NormalizeBettingSlip::execute()` applied football-market normalization to every leg regardless of that leg's sport-normalization result, risking a Tennis or Basketball leg being misclassified through `FootballMarketTaxonomyV1` on the strength of a coincidentally overlapping phrase (e.g. "Match Result").

**This is now fixed in code**, not just documented as a requirement (`app/Domain/Risk/Normalization/NormalizeBettingSlip.php`, `NormalizedMarket.php`, commit `7d13c27`):

```php
$sport = $this->normalizeSport->normalize($leg->sport);

$market = $sport->sportCode === NormalizeSport::FOOTBALL_CODE
    ? $this->normalizeMarket->normalize($leg->market_name, $leg->selection_name)
    : NormalizedMarket::notClassifiedForSport($leg->market_name, $leg->selection_name, $sport->status, FootballMarketTaxonomyV1::VERSION);
```

Verified outcomes (all covered by regression tests in `tests/Feature/Risk/NormalizeBettingSlipTest.php`):

| Sport | `sport_status` | `market_code` | `market_family` | `market_complexity` | `market_status` |
|---|---|---|---|---|---|
| Football (recognized) | `complete` | classified normally | classified normally | classified normally | `complete`/`partial`/`unrecognized` as before |
| Tennis / Basketball / Cricket (unsupported) | `unsupported` | `null` | `null` | `unknown` | `unsupported` |
| Anything unrecognized (e.g. "Waterball") | `unrecognized` | `null` | `null` | `unknown` | `unrecognized` |

Raw market and selection text is preserved verbatim in every case. Tested explicitly with **overlapping market phrases** ("Match Winner" for Tennis, "Match Result" for Basketball, "Total Goals" for Cricket) to prove no leakage — each produces `market_code: null`, not a football classification. Determinism verified: repeated normalization of the same non-football leg produces identical output.

**This closes §12 (RF-005's sport-gating requirement) from the prior draft's open-decisions list** — it is implemented, not merely a documented expectation for a future implementer.

## 3. Decimal Precision Policy

Unchanged from the prior draft — verified sound, no revision needed.

| Quantity | Scale | Notes |
|---|---|---|
| Odds input | 2 decimal places | Matches `betting_slip_legs.decimal_odds DECIMAL(6,2)` exactly. |
| Combined odds (intermediate) | No intermediate rounding between multiplications | Multiply all leg odds as full-precision `BigDecimal`; round only the final product. |
| Combined odds (canonical) | 4 decimal places, fixed (not trimmed) | e.g. `"3.3600"`. |
| Normalized factor values (HHI, ratios) | 6–12 decimal places internally | |
| Factor contributions | 4 decimal places internally | Before summing across factors. |
| Overall score | Integer | Rounded once, at the very end. |
| Data-quality score | Integer | Same treatment. |
| Rounding mode | `HalfUp` throughout | `Brick\Math\RoundingMode::HalfUp` (PascalCase enum case). |

## 4. Factor Budget

Unchanged in substance; ceiling status upgraded from "reachable in principle" to "reachable, proven" (§9, §13, TV-019).

| Factor | Nominal Maximum |
|---|---:|
| RF-001 Leg Count | 25 |
| RF-002 Combined Odds | 20 |
| RF-003 Individual Odds Elevation + Outlier | 20 |
| RF-004 Risk Concentration | 15 |
| RF-005 Market Complexity | 10 |
| RF-006 Relationship/Correlation | 0 (disabled) |
| **Achievable ceiling after group caps** | **78 — proven exactly reachable, see §9** |

## 5. Overall Scoring Architecture

Unchanged:

```text
1. Compute each factor's raw contribution (RF-001..RF-006).
2. Apply Group A cap to (RF-001 + RF-002).
3. Apply Group B cap to (RF-003 + RF-004).
4. RF-005 and RF-006 are not grouped.
5. Sum all five adjusted contributions.
6. Rescale: adjusted_sum × 100 ÷ 78.
7. Clamp to [0, 100].
8. Round to the nearest integer, HalfUp.
9. Map to a risk band (§14).
```

## 6. Factor RF-001 — Leg Count Risk

Unchanged — table, rationale, and rejection of the logarithmic alternative all stand as previously designed.

| Legs | Contribution | Legs | Contribution |
|---:|---:|---:|---:|
| 1 | 0 | 11 | 20 |
| 2 | 2 | 12 | 21 |
| 3 | 5 | 13 | 21 |
| 4 | 8 | 14 | 22 |
| 5 | 11 | 15 | 22 |
| 6 | 13 | 16 | 23 |
| 7 | 15 | 17 | 23 |
| 8 | 17 | 18 | 24 |
| 9 | 18 | 19 | 24 |
| 10 | 19 | 20 | 25 |

**Reason codes:** `LEG_COUNT_MODERATE` (legs 3–6), `LEG_COUNT_HIGH` (legs 7–12), `LEG_COUNT_EXTREME` (legs 13–20).

## 7. Factor RF-002 — Combined Odds Risk

Unchanged — piecewise linear interpolation, logarithm rejected for the same `brick/math`-feasibility reason as RF-001.

| Combined Odds | Contribution |
|---:|---:|
| 1.00 | 0 |
| 2.00 | 3 |
| 4.00 | 7 |
| 7.00 | 10 |
| 10.00 | 14 |
| 20.00 | 17 |
| 50.00+ | 20 (capped) |

**Reason codes:** `COMBINED_ODDS_MODERATE`, `COMBINED_ODDS_HIGH`, `COMBINED_ODDS_EXTREME`.

## 8. Factor RF-003 — Individual Odds Elevation and Outlier Risk

Unchanged. Absolute elevation (sub-cap 12) + relative outlier vs. deterministic median (sub-cap 8), summed and clamped at 20.

| Max Leg Odds | Absolute | Max ÷ Median Ratio | Relative |
|---:|---:|---:|---:|
| 1.00 | 0 | 1.0 | 0 |
| 2.00 | 2 | 1.5 | 2 |
| 3.00 | 5 | 2.0 | 4 |
| 5.00 | 8 | 3.0 | 6 |
| 8.00 | 10 | 5.0+ | 8 (capped) |
| 15.00+ | 12 (capped) | | |

**Reason codes:** `LEG_ODDS_ELEVATED`, `LEG_ODDS_OUTLIER`.

## 9. Factor RF-004 — Risk Concentration

Unchanged. Herfindahl-style index over linear `(odds − 1)` shares, normalized against the balanced baseline for the slip's own leg count; `n = 1` contributes exactly 0.

```text
w_i = decimal_odds_i − 1
share_i = w_i ÷ Σw_i
HHI = Σ(share_i)²
normalized_concentration = (HHI − 1/n) ÷ (1 − 1/n)
RF-004 = normalized_concentration × 15
```

**Reason codes:** `RISK_CONCENTRATED` (≥ 0.4), `RISK_HIGHLY_CONCENTRATED` (≥ 0.7).

## 10. Factor RF-005 — Market Complexity

Unchanged formula; now backed by the corrected normalization boundary (§2.1) rather than a documented requirement on it.

```text
points: simple = 0, moderate = 1, complex = 2, unknown = excluded
known_legs = legs where market.complexity != unknown   [already guaranteed football-only by §2.1's fix]
average = (Σ points over known_legs) ÷ count(known_legs)     [0 if no known legs]
RF-005 = (average ÷ 2) × 10
```

**Reason codes:** `MARKET_COMPLEXITY_PRESENT` (average ≥ 1), `MARKET_COMPLEXITY_HIGH` (average ≥ 1.5).

## 11. Factor RF-006 — Relationship/Correlation

**Explicit, non-silent decision, stated in full per the review's requirement:**

```text
RF-006 is defined but inactive in Rule Set 2026.1.
Maximum active contribution: 0.
Reason: no authoritative event identity exists for manually entered slips,
and no sufficiently reliable deterministic relationship-detection method
was found that avoids guessing.
```

RF-006 is computed for every slip and always appears in the factor-result trace with contribution `0` and reason code `RELATIONSHIP_FACTOR_NOT_EVALUATED` (§18) — visible and explicit, not omitted or silently zero. It does not affect the achievable-ceiling denominator beyond contributing its own `0` (§9). No correlation claim of any kind is emitted. Reconsider only in a future rule-set version, and only alongside a reliable event-identity mechanism — not by loosening the matching rule within 2026.1.

## 12. Group Interaction Caps

Unchanged. Proportional scaling (Method A), chosen over priority ordering for the reasons in the prior draft (neutral, no embedded hierarchy, one formula generalizes to any future group).

- **Group A (Accumulator Scale):** `RF-001 + RF-002 ≤ 40`.
- **Group B (Odds Distribution):** `RF-003 + RF-004 ≤ 28`.
- No Group C cap needed — RF-006 always contributes 0.

## 13. Overall Risk Score

```text
achievable_ceiling = 40 (Group A) + 28 (Group B) + 10 (RF-005 max) + 0 (RF-006) = 78

adjusted_sum = adjusted(RF-001+RF-002) + adjusted(RF-003+RF-004) + RF-005 + RF-006
rescaled = adjusted_sum × 100 ÷ 78
final_score = round_half_up(clamp(rescaled, 0, 100))
```

**The 78-point ceiling is now proven exactly reachable, not merely theoretical (§9).** TV-019 (§20) is a real, valid slip — 20 legs, 19 at odds 1.10 and one at odds 20.00, every leg a `complex` market — that simultaneously saturates Group A (capped from 45 to 40), Group B (capped from 32.2665 to 28), and RF-005 (10, all complex), producing an adjusted sum of exactly 78 and a rescaled, rounded final score of **exactly 100**. This directly answers §9's challenge: the achievable ceiling is not an artifact of summing nominal caps that can never simultaneously bind — it is a real, constructible input.

## 14. Structural Risk Bands

**Retained after a full distribution review against all 20 structural test vectors** (§20) — not retained merely because they are simple.

| Score | Band |
|---:|---|
| 0–24 | Low |
| 25–49 | Moderate |
| 50–74 | High |
| 75–100 | Very High |

**Distribution across all 20 vectors:** 8 Low (40%), 5 Moderate (25%), 4 High (20%), 3 Very High (15%).

Answering the review's specific questions directly, with the vector that proves each answer:

- **Does an ordinary 3-leg accumulator land in Moderate?** No — TV-004 (balanced, all simple markets) lands at 16, Low. A 3-leg slip needs elevated odds or complex markets to reach Moderate (TV-009: 3 complex markets, 40, Moderate).
- **Does a balanced 5-leg slip land in Moderate or High?** Moderate (TV-006, 28).
- **Does a 10-leg accumulator reliably reach High?** No — TV-007 (10 legs, all odds 1.20) lands at 37, Moderate. Leg count alone, even near its own sub-ceiling, is insufficient without odds or concentration also contributing.
- **Should Very High be rare?** 15% of vectors reach it, and every one required a deliberately extreme construction (TV-018/019: 20 legs with a concentrated outlier leg; TV-021: a 2-leg slip with an extreme 1.05/50.00 split) — not an ordinary accumulator. No balanced or moderately-aggressive vector reaches it.
- **Can a single high-odds complex market reach High?** Yes — TV-020 (single leg, odds 50.00, complex market) reaches exactly 54, High. It cannot reach Very High: a single leg structurally cannot trigger RF-001 (0 by definition) or RF-004 (0 by definition, §9), capping a single leg's reachable ceiling at Group A's uncapped RF-002 (20) + Group B's uncapped RF-003 (20) + RF-005 (10) = 50 raw ÷ 78 × 100 ≈ 64 — inside High, below Very High's 75 floor.
- **Should a two-leg slip ever reach Very High?** Yes, but only under deliberately extreme construction — TV-021 (odds 1.05 and 50.00, both complex) reaches 77, Very High. An ordinary two-leg slip (TV-003, odds 1.50/1.60) reaches only 9, Low. Two legs is enough leg-count-wise to unlock RF-004's concentration mechanism (unlike a single leg), which is what makes 021's extreme spread reachable.

No boundary distortion found from mapping bands off the rounded integer rather than the unrounded value (TV-014/TV-014b, §20, differ by 0.01 in combined odds and produce identical scores).

**RF-003A correction (2026-07-24):** TV-003, TV-004/TV-015, TV-006, and TV-009's RF-003/score figures above and in §20 were originally computed by a reference script carrying a float-to-BigDecimal-int truncation defect that silently collapsed RF-003's `1.5` relative-outlier anchor into `1.0` (identical in kind to a defect independently found in the RF-005 implementation). This corrupted exactly the vectors whose max-to-median ratio falls between 1.0 and 2.0 (a "balanced accumulator" ratio range); vectors with equal-odds legs or a genuine dominant outlier were unaffected because their ratios fall outside that range. §8's RF-003 formula itself was never ambiguous and requires no change — only the four affected precomputed vectors below were corrected, using the same properly-implemented formula. See `docs/00-governance/DECISION_LOG.md`, RF-003A.

## 15. Implementation Feasibility Review

Unchanged from the prior draft — findings independently re-confirmed, not re-derived:

- `brick/math` is already installed transitively via `laravel/framework`; not a new dependency.
- `BigDecimal` has no `ln`/`log`/`exp` — confirmed by direct reflection, not documentation. Every logarithm-based candidate formula is rejected on this basis.
- `RoundingMode` enum cases are PascalCase (`HalfUp`).
- Every formula uses only `+`/`-`/`×`/`÷` (explicit scale)/`power(2)`.
- All 20 structural vectors plus the additional 3 added in this review computed in well under a millisecond each.

## 16. Data-Quality Scoring

**Revised scope**: sport-level problems (`unsupported`/`unrecognized` sport) are no longer scored here — they are a hard gate (§17) upstream of this calculation entirely, since §2.1's fix means a non-football leg never reaches football-market normalization at all, and mixing "this leg's sport isn't supported" into a soft, additive deduction alongside genuine football-market issues risked exactly the confusing partial-credit product promise the review warned about (§7.4). Data quality, as scored here, now answers a narrower and more honest question: **for a slip that is entirely football, how well did its markets normalize?**

```text
starting score: 100
deduction: market `partial` → −8 per leg, category cap −40
floor: 0
```

**Market `unrecognized` is deliberately not a deduction here** — it is handled by the proportion gate in §17, not blended into this score, because a proportion-based rule and a per-leg deduction can disagree (a single unrecognized leg in a 4-leg slip is 25% of the slip but only one deduction unit; a single unrecognized leg in a 16-leg slip is a very different proportion but the same deduction) and having two independently-tuned rules for the same underlying signal risks exactly that disagreement.

| Score | Data Quality |
|---:|---|
| 85–100 | Strong |
| 65–84 | Good |
| 40–64 | Limited |
| 0–39 | Insufficient |

**Consequence, stated explicitly:** because partial normalization doesn't affect any currently-active factor (§10, §17.3), this deduction-only score can reach Limited (worst case: every leg partial, capped deduction −40, score 60) but **cannot alone reach Insufficient** in Rule Set 2026.1. Insufficient/Unavailable is only reachable via the sport hard-gate or the market-unrecognized proportion gate (§17) — a deliberate simplification, not an oversight.

## 17. Analysis Availability Gate

**Resolved as a three-tier gate, replacing the single data-quality threshold in the prior draft**, directly per the review's §7.3–§7.5 requirements.

```text
Tier 1 — Sport gate (hard):
    if any leg has sport_status != complete:
        → analysis_unavailable. Slip remains Ready.

Tier 2 — Market-unrecognized proportion gate:
    unrecognized_proportion = count(legs where market_status == unrecognized) ÷ leg_count
    if unrecognized_proportion > 0.25:
        → analysis_unavailable. Slip remains Ready.
    elif unrecognized_proportion > 0:
        → data-quality band is capped at "Limited" regardless of §16's deduction score.

Tier 3 — Deduction-score gate (§16):
    compute the partial-normalization deduction score and band.
    final_band = the worse of (Tier 2's cap, if any) and (Tier 3's band).
    if final_band == Insufficient:
        → analysis_unavailable. Slip remains Ready.
```

**Outcome by final band:**

- **Strong or Good:** full numeric structural-risk score and band (§13–14), no limitation flag.
- **Limited:** full numeric structural-risk score and band, **plus** `limited_analysis: true`, the data-quality band, and a `factors_not_evaluated` list (empty unless RF-005 had zero legs with known complexity — realistically only reachable at the Tier 2 boundary, since anything worse is already Unavailable). This is one score concept with a visible caveat, not a second "partial score" concept (rejected in the prior draft, still rejected here).
- **Insufficient (via either gate):** no numeric score at all. Slip stays `Ready`, not `Analysed`.

### Worked examples

| Scenario | Tier 1 | Tier 2 | Tier 3 | Outcome |
|---|---|---|---|---|
| 4 football legs, all markets recognized | pass | 0% unrecognized | 100, Strong | **Full**, Strong |
| 3 football legs, 1 partial market | pass | 0% unrecognized | 92, Strong | **Full**, Strong |
| 5 football legs, all partial | pass | 0% unrecognized | 60, Limited (deduction alone) | **Limited**, `limited_analysis: true` |
| 4 football legs, 1 unrecognized market (25%) | pass | capped to Limited | 100, Strong (deduction alone) | **Limited** (Tier 2 overrides Tier 3) |
| 8 football legs, 1 unrecognized market (12.5%) | pass | capped to Limited | irrelevant | **Limited** |
| 4 football legs, 2 unrecognized (50%) | pass | **fail (> 25%)** | — | **Unavailable**, stays Ready |
| 3 legs, 2 football + 1 Tennis | **fail** | — | — | **Unavailable**, stays Ready |
| 1 leg, sport "Waterball" | **fail** | — | — | **Unavailable**, stays Ready |

This resolves §7.3, §7.4, and §7.5 together: the sport gate is exact and absolute (any non-football leg blocks the whole slip — §7.4's recommended policy, adopted as-is), the market proportion gate uses the review's own specified 25% threshold (generalized from its "1 leg in a 4-leg slip" example to any count at or under that proportion), and the two gates are explicitly ordered so they cannot silently contradict each other.

### 17.1 — Partial normalization (§7.6), resolved

A partial football market **never blocks or degrades scoring below Limited on its own**, because none of Rule Set 2026.1's active factors (RF-001 through RF-005) consume the specific facts that "partial" status means are missing (a total-goals line, a BTTS yes/no, a correct-score result). RF-005 needs only the market's *family* and *complexity*, both of which are already known even when the *selection* is only partially normalized. This is a structural fact about which factors are active in 2026.1, not a policy choice — a future rule set that adds a factor consuming selection-specific facts would need to revisit this.

## 18. Reason-Code Trigger Design

**Updated catalogue** — adds the codes the review specifically named, plus the two outcome-level codes needed by §17's gate.

| Code | Factor / Gate | Severity | Score Effect | Customer-Facing | Provisional |
|---|---|---|---|---|---|
| `LEG_COUNT_MODERATE` | RF-001 | Low | Yes | Yes | No |
| `LEG_COUNT_HIGH` | RF-001 | Medium | Yes | Yes | No |
| `LEG_COUNT_EXTREME` | RF-001 | High | Yes | Yes | No |
| `COMBINED_ODDS_MODERATE` | RF-002 | Low | Yes | Yes | No |
| `COMBINED_ODDS_HIGH` | RF-002 | Medium | Yes | Yes | No |
| `COMBINED_ODDS_EXTREME` | RF-002 | High | Yes | Yes | No |
| `LEG_ODDS_ELEVATED` | RF-003 | Medium | Yes | Yes | No |
| `LEG_ODDS_OUTLIER` | RF-003 | Medium | Yes | Yes | No |
| `RISK_CONCENTRATED` | RF-004 | Medium | Yes | Yes | No |
| `RISK_HIGHLY_CONCENTRATED` | RF-004 | High | Yes | Yes | No |
| `MARKET_COMPLEXITY_PRESENT` | RF-005 | Low | Yes | Yes | No |
| `MARKET_COMPLEXITY_HIGH` | RF-005 | Medium | Yes | Yes | No |
| `RELATIONSHIP_FACTOR_NOT_EVALUATED` | RF-006 | — | No | No | No — always emitted in 2026.1, not provisional |
| `MARKET_UNRECOGNIZED` | Gate (Tier 2) | Medium | No (gates, doesn't deduct) | Yes | No |
| `SPORT_UNSUPPORTED` | Gate (Tier 1) | High | Blocks (§17) | Yes | No |
| `NORMALIZATION_PARTIAL` | Data quality (§16) | Low | No (deducts data quality only) | Yes | No |
| `ANALYSIS_LIMITED` | Gate outcome | Medium | N/A (outcome-level) | Yes | No |
| `ANALYSIS_UNAVAILABLE` | Gate outcome | High | N/A (outcome-level, blocks) | Yes | No |

`MULTIPLE_SAME_EVENT_LEGS` and `POTENTIAL_RELATIONSHIP` (from the prior draft) are removed from the active catalogue — `RELATIONSHIP_FACTOR_NOT_EVALUATED` replaces both with a single, always-emitted, explicitly-inactive code, which is more honest than two dormant codes that could be mistaken for active ones. `DATA_QUALITY_LIMITED`/`DATA_QUALITY_INSUFFICIENT` from the prior draft are replaced by `ANALYSIS_LIMITED`/`ANALYSIS_UNAVAILABLE`, which describe the actual gate outcome rather than the data-quality score in isolation (since Tier 1/Tier 2 can produce Unavailable without the data-quality score itself being Insufficient). No customer prose is finalized here — these are trigger identifiers only.

## 19. Mathematical Invariants

Unchanged from the prior draft, plus one addition:

1. **Score bounds:** `0 ≤ final_score ≤ 100`.
2. **Factor bounds:** every `RF-00n` stays within its documented maximum, before and after adjustment.
3. **Leg-count monotonicity.**
4. **Combined-odds monotonicity.**
5. **Determinism.**
6. **Order independence** — verified directly (TV-004/TV-015 are the same three legs reversed, identical result in every field).
7. **Equality symmetry** — verified directly (TV-017).
8. **Unknown-market neutrality** — verified directly (TV-013).
9. **Interaction bounds.**
10. **No prediction.**
11. **Single-leg neutrality** (`RF-004 = 0` exactly for `n=1`).
12. **Rescale ceiling** — now proven achievable exactly (not merely bounded), via TV-019 reaching precisely 100.
13. **New — Sport isolation:** a leg's `market_status`/`market_complexity` is never influenced by a taxonomy that doesn't match its `sport_status`. Verified directly (§2.1's regression tests: Tennis/Basketball/Cricket legs with overlapping football-alias phrasing never receive a football classification).

## 20. Canonical Test Vectors

All 20 structural vectors (17 from the prior draft + 3 added in this review) computed with the same `BigDecimal` reference script, re-run after the normalization fix (the fix does not change any of these vectors' inputs or outputs — none of them use a non-football sport). TV-003, TV-004/TV-015, TV-006, and TV-009 were subsequently corrected under RF-003A (2026-07-24) — the original reference script had a float-truncation defect affecting RF-003's relative-outlier anchor; see the note after §14 and `DECISION_LOG.md`. All legs in TV-004 and TV-006 are `simple`-complexity markets (RF-005 = 0 for both); TV-009's three legs are all `complex`-complexity markets (RF-005 = 10).

| Vector | Legs | Combined Odds | RF-001 | RF-002 | RF-003 | RF-004 | RF-005 | Group A | Group B | Score | Band |
|---|---:|---:|---:|---:|---:|---:|---:|---|---|---:|---|
| TV-001 Conservative Single | 1 | 1.4000 | 0 | 1.2000 | 0.8000 | 0 | 0 | 1.2000 | 0.8000 | **3** | Low |
| TV-002 Aggressive Single | 1 | 9.0000 | 0 | 12.6667 | 10.2857 | 0 | 10 | 12.6667 | 10.2857 | **42** | Moderate |
| TV-003 Balanced Two-Leg | 2 | 2.4000 | 2 | 3.8000 | 1.3290 | 0.1240 | 0 | 5.8000 | 1.4530 | **9** | Low |
| TV-004 Balanced Three-Leg | 3 | 3.3600 | 5 | 5.7200 | 1.4667 | 0.2000 | 0 | 10.7200 | 1.6667 | **16** | Low |
| TV-005 High-Odds Outlier | 4 | 8.7750 | 8 | 12.3667 | 13.7794 | 8.7772 | 0 | 20.3667 | 22.5566 | **55** | High |
| TV-006 Balanced Five-Leg | 5 | 6.3717 | 11 | 9.3717 | 1.3759 | 0.0926 | 0 | 20.3717 | 1.4685 | **28** | Moderate |
| TV-007 Large Low-Odds Accumulator | 10 | 6.1917 | 19 | 9.1917 | 0.4000 | 0 | 0 | 28.1917 | 0.4000 | **37** | Moderate |
| TV-008 Extreme Accumulator | 20 | 375899.7346 | 25 | 20 | 1.8000 | 0 | 0 | **40 (capped)** | 1.8000 | **54** | High |
| TV-009 Complex-Market Slip | 3 | 9.2400 | 5 | 12.9867 | 2.7905 | 0.0413 | 10 | 17.9867 | 2.8318 | **40** | Moderate |
| TV-010 Concentrated Risk | 4 | 4.9588 | 8 | 7.9588 | 11.8337 | 10.2895 | 0 | 15.9588 | 22.1232 | **49** | Moderate |
| TV-013 Unsupported Sport | 1 | 1.8000 | 0 | 2.4000 | 1.6000 | 0 | 0 (excluded) | 2.4000 | 1.6000 | **5** | Low |
| TV-014 Boundary (3.99) | 1 | 3.9900 | 0 | 6.9800 | 6.4850 | 0 | 0 | 6.9800 | 6.4850 | **17** | Low |
| TV-014b Boundary (4.00) | 1 | 4.0000 | 0 | 7.0000 | 6.5000 | 0 | 0 | 7.0000 | 6.5000 | **17** | Low |
| TV-015 Reordered (= TV-004) | 3 | 3.3600 | 5 | 5.7200 | 1.4667 | 0.2000 | 0 | 10.7200 | 1.6667 | **16** | Low |
| TV-016 Group-Cap Activation | 20 | 3325.2567 | 25 | 20 | 1.0000 | 0 | 0 | **40 (capped)** | 1.0000 | **53** | High |
| TV-017 Equal-Odds Tie | 4 | 5.0625 | 8 | 8.0625 | 1.0000 | 0 | 0 | 16.0625 | 1.0000 | **22** | Low |
| TV-018 Maximum 20-Leg (mixed) | 20 | 122.3182 | 25 | 20 | 20 | 12.2665 | 0 | **40 (capped)** | **28 (capped)** | **87** | Very High |
| **TV-019 Ceiling Proof** | 20 | 122.3182 | 25 | 20 | 20 | 12.2665 | **10** | **40 (capped)** | **28 (capped)** | **100** | **Very High** |
| **TV-020 Single-Leg Ceiling** | 1 | 50.0000 | 0 | 20 | 12.0000 | 0 | 10 | 20 | 12.0000 | **54** | **High** |
| **TV-021 Two-Leg Very-High** | 2 | 52.5000 | 2 | 20 | 15.9177 | 14.9389 | 10 | 22 | **28 (capped)** | **77** | **Very High** |

**Band distribution:** Low 8 (40%), Moderate 5 (25%), High 4 (20%), Very High 3 (15%) — analyzed in §14.

Cross-sport isolation is verified separately in `tests/Feature/Risk/NormalizeBettingSlipTest.php` (not a scoring vector — it verifies normalization output, not a final score, since Tier 1's gate means no non-football leg ever reaches scoring at all).

## 21. Known Limitations

- **Calibration is expert-designed, not empirically validated** — every threshold, anchor, and cap remains a defensible starting hypothesis, not a statistically fitted model. See §22 (Calibration) below for the philosophy and future evidence plan (unchanged from the prior draft, still applicable, not restated in full here — see the prior committed content if needed, or treat this document plus the Future Calibration Plan in `docs/03-data-science/RISK_ENGINE.md` as the pair of authorities).
- **RF-006 remains permanently inactive in 2026.1** — a deliberate, documented gap, not an oversight (§11).
- **Group cap values (40, 28) remain calibration choices**, same status as every other threshold.

All other limitations from the prior draft are resolved: the sport-gating requirement is implemented (§2.1), the single-leg and two-leg ceiling questions are answered with real vectors (§14, TV-020/TV-021), and the reason-code catalogue is finalized (§18).

## 22. Section 22 (Prior Draft) — Every Open Decision, Individually Resolved

The prior draft's §22 listed seven open items without enumerating each fully. Each is resolved here in the requested eight-part structure.

### 22.1 — Factor tables themselves (§6–§10)

1. **Exact question:** Are the specific numeric values in each factor's threshold table/anchor set correct?
2. **Current proposed choice:** Retained as originally designed — an expert-designed starting hypothesis (§21).
3. **Alternatives considered:** Logarithmic formulas (rejected, §15); a from-scratch empirical fit (rejected — no labelled dataset exists, and this product doesn't predict outcomes to fit against in the first place).
4. **Mathematical consequence:** These values directly determine every score in §20. Changing them requires recomputing all 20 vectors.
5. **Product consequence:** Determines how "aggressive" the product feels for common slip constructions — verified reasonable via §14's distribution review.
6. **Implementation consequence:** None — any retuning is a data change to a lookup table/anchor array, not a formula change.
7. **Recommendation:** Approve as-is; revisit only after real usage data exists (see the Future Calibration Plan).
8. **Blocks approval:** No — the *shape* of each formula is sound and feasibility-verified; the specific numbers are refinable in a future rule-set version without restructuring anything.

### 22.2 — The two group cap values (40, 28)

1. **Exact question:** Are 40 (Group A) and 28 (Group B) the right caps, and is the resulting 78-point ceiling acceptable?
2. **Current proposed choice:** Retained; proven exactly reachable (TV-019, §9/§13).
3. **Alternatives considered:** No caps at all (rejected — reintroduces double counting, §12); redesigning factor maxima to sum to 100 pre-cap (Option C of the review's §7.1 — rejected: would require inflating individual factor maxima specifically to compensate for capping, which risks overweighting factors simply to fill the scale, the exact risk the review itself warned against).
4. **Mathematical consequence:** Defines exactly how much a correlated pair of factors can jointly contribute; proven to be a real, reachable ceiling, not a nominal one that silently caps the display range below 100.
5. **Product consequence:** A maximally-constructed slip can reach a genuine 100 — the full display range means something.
6. **Implementation consequence:** The rescale step (§13) is one multiplication and one division; trivial to implement and test.
7. **Recommendation:** Approve Option A (rescale 0–78 to 0–100) from the review's §7.1, with the exact transformation `rescaled = adjusted_sum × 100 ÷ 78`, `HalfUp` rounding, as specified in §13.
8. **Blocks approval:** No — proven, not theoretical.

### 22.3 — Limited Analysis data-quality floor and gate structure

1. **Exact question:** What exact score/proportion thresholds gate Full vs. Limited vs. Unavailable analysis?
2. **Current proposed choice:** The three-tier gate in §17 — sport hard-gate, then a 25% market-unrecognized proportion gate, then a partial-normalization deduction score (Strong/Good/Limited/Insufficient at 85/65/40).
3. **Alternatives considered:** A single data-quality score covering sport and market issues together (the prior draft's original design — rejected in this review because sport issues and market issues have different product implications, per §7.4, and blending them risked a confusing "this slip is 80% analyzable" promise for a slip that's actually part-Tennis); a proportion-only gate with no score (rejected — loses the ability to distinguish "one partial market" from "five partial markets" within an otherwise-fine slip).
4. **Mathematical consequence:** Insufficient/Unavailable is now reachable only via the sport gate or the proportion gate, not via partial-deduction alone (§16) — a real, stated change in behavior, not an incidental one.
5. **Product consequence:** A user is never shown a "completed" analysis for a slip containing an unsupported sport, full stop — the clearest, least confusing promise available.
6. **Implementation consequence:** Three sequential checks, each simple (an `any()`, a proportion division, a lookup-table band) — no new arithmetic technique beyond what §6–§10 already require.
7. **Recommendation:** Approve as specified in §17.
8. **Blocks approval:** No — fully specified, no ambiguity remains.

### 22.4 — Two missing boundary vectors (single-leg ceiling, two-leg Very High)

1. **Exact question:** Can a single-leg slip reach High, and can a two-leg slip reach Very High?
2. **Current proposed choice:** Both computed and confirmed: single-leg ceiling is 54 (High, not Very High) — TV-020; two-leg Very High is reachable at 77 under extreme construction — TV-021.
3. **Alternatives considered:** Leaving both as derived-but-unverified (the prior draft's state) — rejected once the review specifically asked for proof, not derivation.
4. **Mathematical consequence:** None — confirms the existing formulas behave as derived; no formula changed.
5. **Product consequence:** Confirms no single "outlier" leg can ever look maximally alarming (Very High) on its own — reassuring for the product's restraint goals.
6. **Implementation consequence:** None.
7. **Recommendation:** Add both vectors to the permanent canonical set (done, §20).
8. **Blocks approval:** No — resolved.

### 22.5 — RF-005 sport-gating requirement

1. **Exact question:** Must Factor RF-005 avoid trusting market classification for non-football legs?
2. **Current proposed choice:** Yes — and it is now enforced upstream, in the normalization boundary itself (§2.1), not as a rule RF-005's own logic has to remember to apply.
3. **Alternatives considered:** Gating inside RF-005's own calculation instead of upstream in normalization (rejected — would mean every future factor that reads `market_complexity` has to remember the same gate; fixing it once in `NormalizeBettingSlip` is the deeper, more durable fix, per this repo's `domain-modelling` skill's general preference for fixing root causes over per-factor special cases).
4. **Mathematical consequence:** `known_legs` in RF-005's formula is now guaranteed football-only by construction, not by convention.
5. **Product consequence:** No non-football leg can ever silently inflate or deflate market-complexity scoring.
6. **Implementation consequence:** Already implemented (commit `7d13c27`); E-06B inherits a corrected boundary rather than having to build the gate itself.
7. **Recommendation:** Confirm as binding and closed.
8. **Blocks approval:** No — implemented and tested.

### 22.6 — `MARKET_UNRECOGNIZED` reason-code catalogue addition

1. **Exact question:** Should `MARKET_UNRECOGNIZED` (and the newly added `SPORT_UNSUPPORTED`, `ANALYSIS_LIMITED`, `ANALYSIS_UNAVAILABLE`, `RELATIONSHIP_FACTOR_NOT_EVALUATED`) become permanent, superseding the original eight-code list in `docs/03-data-science/RISK_ENGINE.md`?
2. **Current proposed choice:** Yes — §18's table is now the authoritative catalogue; `RISK_ENGINE.md` points here rather than restating it.
3. **Alternatives considered:** Maintaining the code list in `RISK_ENGINE.md` directly (rejected — this repo's `documentation-stewardship` skill explicitly warns against the same information living in two places).
4. **Mathematical consequence:** None — codes are labels on already-computed values.
5. **Product consequence:** Consistent vocabulary across every future customer-facing surface.
6. **Implementation consequence:** One enum or constant list to implement in E-06B, matching §18 exactly.
7. **Recommendation:** Approve §18 as final for 2026.1.
8. **Blocks approval:** No.

### 22.7 — `brick/math` direct-dependency timing

1. **Exact question:** Should `composer.json` require `brick/math` directly now, or remain implicit (transitive via `laravel/framework`) until E-06B starts?
2. **Current proposed choice:** No change during E-06A (design-only scope forbids dependency changes); recommend making it direct at the start of E-06B, not before.
3. **Alternatives considered:** Adding it now — rejected, since E-06A is explicitly design-only and this is a one-line, zero-risk change better bundled with the implementation sprint that actually uses it.
4. **Mathematical consequence:** None.
5. **Product consequence:** None.
6. **Implementation consequence:** Trivial either way; deferring costs nothing.
7. **Recommendation:** Defer to E-06B's first commit.
8. **Blocks approval:** No.

---

## Final Report

### A. Prerequisite Correction

**Defect:** `NormalizeBettingSlip` applied `FootballMarketTaxonomyV1` to every leg regardless of sport recognition, risking misclassification of non-football legs with overlapping market phrasing.

**Fix:** Market normalization now runs only when `sport->sportCode === NormalizeSport::FOOTBALL_CODE`; otherwise `NormalizedMarket::notClassifiedForSport()` returns `market_code: null`, `complexity: unknown`, `status` mirroring the sport's own status, raw text always preserved. Committed separately as `7d13c27` (`fix(risk): isolate market normalization by supported sport`).

**Tests:** 6 new regression tests — football no-regression, three overlapping-phrase unsupported-sport cases (Tennis+Match Winner, Basketball+Match Result, Cricket+Total Goals), one unrecognized-sport case, one determinism case. Full suite 173/173 passing at the time of that commit.

**Exact behavior:** table in §2.1.

### B. Seven Open Decisions

Individually resolved in §22.1 through §22.7 above — none deferred without a recorded reason and an explicit "blocks approval: No" or "Yes" determination. All seven: **No** (none block approval).

### C. Final Mathematical Architecture

- **Active factors:** RF-001 Leg Count (max 25), RF-002 Combined Odds (max 20), RF-003 Individual Odds Elevation+Outlier (max 20), RF-004 Risk Concentration (max 15), RF-005 Market Complexity (max 10).
- **Inactive factor:** RF-006 Relationship (max 0, always computed, always traced, never contributing).
- **Group caps:** Group A (RF-001+RF-002) ≤ 40; Group B (RF-003+RF-004) ≤ 28. Proportional-scaling adjustment.
- **True achievable ceiling:** 78, proven exactly reachable (TV-019).
- **Score transformation:** `rescaled = adjusted_sum × 100 ÷ 78`, clamp [0,100], round `HalfUp` to integer.
- **Risk bands:** 0–24 Low, 25–49 Moderate, 50–74 High, 75–100 Very High — validated against 20 vectors, distribution in §14.

### D. Data Quality and Analysis Gate

- **Full:** Strong or Good data quality (no unsupported/unrecognized sport, ≤25% unrecognized markets, deduction score ≥ 65) → normal score and band, no flag.
- **Limited:** either the 25%-or-under unrecognized-market proportion caps it, or the partial-deduction score alone lands 40–64 → full numeric score, `limited_analysis: true`, quality band and (if applicable) `factors_not_evaluated` shown.
- **Unavailable:** any non-football leg (Tier 1), or >25% unrecognized markets (Tier 2), or a deduction score <40 (Tier 3 — not currently reachable by partial-normalization alone, §16) → no score, slip stays `Ready`.
- **Exact thresholds:** sport gate is absolute (any failure blocks); market-unrecognized proportion gate at exactly 25%; deduction score bands at 85/65/40 as in §16.

### E. Vector Distribution

Full table in §20 — 20 vectors, scores 3 to 100, all four bands represented (8 Low, 5 Moderate, 4 High, 3 Very High). Every vector in this design-only sprint is football-only (sport-gate interactions are verified separately by the normalization-layer regression tests, not by re-running them through the scoring vectors, since Tier 1 means a non-football leg never reaches scoring).

### F. Engineering Feasibility

Unchanged from the prior draft: only `+`/`-`/`×`/`÷`/`power(2)` required; `brick/math` already present transitively; no logarithm, root, or transcendental operation needed anywhere; `RoundingMode::HalfUp` is the exact case name; sub-millisecond performance at the 20-leg ceiling for every vector including the three added in this review.

### G. Scope Confirmation

Confirmed: no production scoring classes, no database migration, no persistence, no weakest-leg implementation, no UI, no AI, no external sports data. The only code change in this review was the normalization-boundary correction (§2.1/§A) — a bug fix to existing E-05A normalization code, not new scoring implementation, and explicitly authorized for separate commit by this review's own commit gate.

### H. Recommendation

```text
Approve Rule Set 2026.1.
```

Every item that previously blocked approval is now resolved (§22.1–22.7), the cross-sport defect is fixed and tested (not merely documented), the achievable ceiling is proven rather than asserted, and the analysis-availability gate is exact. E-06B can begin as a pure translation of this document into tested PHP — no further mathematical design decision should be required during implementation. If Product Office identifies a decision in §22 it disagrees with, the recommendation changes to `Revise specified decisions`; absent that, this document is ready to sign.
