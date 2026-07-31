# Weakest-Leg Attribution Model — Marginal Structural Contribution (MSC)

**Status:** `ACCEPTED` (Product Office, `PO-U06.2A-AC-001`, 2026-07-26 — recorded in `docs/00-governance/DECISION_LOG.md`). This is the authoritative engineering reference for weakest-leg attribution; the mathematics below is unchanged by acceptance — only this status line and the Final Report's return-of-ownership note (§13.D) were updated. Not yet implemented — Engineering has not been separately commissioned; a prior implementation directive (`PO-E06D-002`) was declined because it was issued before this acceptance existed, per `docs/00-governance/DECISION_LOG.md`.

**Commissioned by:** Product Office handover `PO-U06.2A-001` (Programme U-06 — Intelligent Accumulator Planning & Decision Intelligence, Work Package U-06.2A — Deterministic Weakest-Leg Mathematical Model), under strategic authority SD-001.

**Supersedes no other document.** `docs/03-data-science/RISK_RULE_SET_2026_1.md` remains the sole authority for whole-slip structural risk mathematics (RF-001–RF-006, group caps, the analysis-availability gate, the reason-code catalogue). This document adds a new, separate layer on top of that unchanged mathematics — it does not alter a single formula, weight, threshold, cap, or band in Rule Set 2026.1. `docs/03-data-science/RISK_ENGINE.md`'s "Weakest Leg" line (§Weakest Leg: "the leg contributing the greatest explainable structural risk... not a prediction that the leg will lose") is the product-level definition this document finally makes computable; nothing here changes that definition, only how it is calculated.

**Prerequisite reading:** `RISK_RULE_SET_2026_1.md` (the whole-slip mathematics this model reuses unmodified), `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` (Programme U-06's Capability A scope), `docs/00-governance/DECISION_LOG.md` entries for SD-001, `PO-U06.1-AC-001`, and the declined `PO-E06D-001` directive (the reason this had to be Data Science Lab's own formula, not Engineering's).

---

## 1. Mission Recap

Per the handover: *"How should SlipGuard mathematically attribute structural risk to individual selections so that every accumulator can be deterministically ranked from strongest to weakest?"*

This document answers that question with one model — **Marginal Structural Contribution (MSC)** — and states, with worked proof, exactly where it is unambiguous, where it produces genuinely subtle (including negative) results, and where it cannot produce a number at all.

No machine learning, probability, or outcome forecasting appears anywhere below. Every quantity is computed by re-invoking the already-approved, already-tested `CalculateStructuralRisk` engine — never a new scoring formula.

## 2. Mathematical Assumptions & Preconditions

1. **Rule Set 2026.1 is authoritative and unmodified.** RF-001–RF-005's tables, RF-006's inactivity, both group caps, the rescale transform, the risk bands, and the three-tier analysis-availability gate are used exactly as specified — see `RISK_RULE_SET_2026_1.md` §6–§17. This document invents no new factor, weight, or threshold in that mathematics.
2. **Per-leg normalization is independent of sibling legs.** Verified directly against `App\Domain\Risk\Normalization\NormalizeBettingSlip` and `NormalizeFootballMarket`: each leg's sport/market classification depends only on that leg's own free text, never on any other leg in the slip. This is what makes it valid to remove a leg from an already-normalized collection without re-running normalization on the remainder (§11).
3. **Ranking requires a slip with a numeric score.** MSC is defined only for a slip `S` whose own `RiskAnalysisResult.availability` is `Full` or `Limited` (i.e. `structuralScore` is not null). A slip that is itself `Unavailable` has no baseline score to take a marginal difference against, so it is not offered leg-level ranking at all — consistent with the existing product rule that an `Unavailable` slip shows no numeric output of any kind (`RISK_RULE_SET_2026_1.md` §17).
4. **Ranking is only meaningful for `leg_count ≥ 2`.** A single-leg "accumulator" is not actually an accumulator; there is nothing to rank it against. See §3.4's explicit single-leg rule.
5. **The model consumes only facts the engine already computes.** No new per-leg input is required beyond what `NormalizedBettingSlipLeg` and `CalculateStructuralRisk` already produce today.

## 3. The Model: Marginal Structural Contribution (MSC)

### 3.1 Definition

For an accumulator `S` with legs `{l₁, …, lₙ}`, `n ≥ 2`, and a numeric structural score `score(S)` (the existing `RiskAnalysisResult.structuralScore`, already an integer 0–100 per Rule Set 2026.1 §13):

```text
S₋ᵢ  =  the accumulator formed by removing leg i from S, all other legs and their order unchanged.

MSC_i  =  score(S) − score(S₋ᵢ)
```

`MSC_i` is computed by literally re-running the unmodified `CalculateStructuralRisk` engine on the (n−1)-leg remainder — not by any new formula. It answers, exactly and only: *"If this selection were removed or replaced, how many structural-risk points would the accumulator lose?"*

**The Weakest Leg** is the leg with the greatest `MSC_i` (rank position 1 under §3.4). **Ranking from strongest to weakest** is the full ordering of all legs by descending `MSC_i`, tie-broken per §3.5.

### 3.2 Why Leave-One-Out — Alternatives Considered and Rejected

**Alternative A — additive per-factor decomposition.** Distribute each factor's already-computed slip-level value across legs by a hand-authored split (e.g. RF-004's own `share_i²` term almost gives a per-leg number already; RF-005's per-leg complexity point is already per-leg).

*Rejected.* RF-001 (leg count) and RF-002 (combined odds) have **no principled per-leg split at all** — RF-001 depends only on *how many* legs exist, not which one; every leg is equally "responsible" for the slip having `n` legs, so any split (equal shares, odds-weighted shares, anything else) is an invented number with no basis in the approved mathematics. Worse, the **group caps operate on the sum of two factors**, not on individual legs — decomposing a *capped, jointly-scaled* value per leg has no defensible per-leg meaning whatsoever. Any formula that tried to do this would be new, unapproved mathematics layered on top of Rule Set 2026.1, not a restatement of it.

**Alternative B — a new bespoke per-leg risk score** (e.g. a weighted composite of the leg's own odds elevation, market complexity, and concentration share, using a freshly authored weight table independent of removal).

*Rejected.* This requires inventing new weights with no worked justification beyond what RF-003/RF-004/RF-005 already establish for the *whole slip* — duplicating the same signal under a second, differently-tuned formula risks disagreeing with the actual slip-level score (a leg could rank "weakest" under the new formula while removing it barely moves the real score at all). That is precisely the kind of unexplained, non-reproducible suggestion SD-001 forbids ("no opaque or unexplained suggestion is permitted"). It also structurally ignores RF-001 and RF-002 (leg count, combined odds) entirely, since neither has a natural "per-leg value" to feed such a formula — missing two of the five active factors that can dominate a slip's real risk.

**Alternative C — leave-one-out marginal contribution against the exact, already-approved engine (chosen).**

No new weight, threshold, or formula component of any kind. Automatically includes every active factor (RF-001–RF-005) and both group-cap/rescale nonlinearities exactly as approved, because it is not a new computation — it is the same approved computation, run twice, subtracted. It is trivially explainable in plain language ("your risk score would drop from 64 to 27 without this selection") and requires zero changes to Rule Set 2026.1. It also correctly captures RF-002's multiplicative structure without needing a logarithm (which `BigDecimal` cannot compute, `RISK_RULE_SET_2026_1.md` §15): because `combined_odds` is the product of every leg's own odds, removing the leg with the highest odds shrinks the remaining product by the largest factor, and the piecewise-linear RF-002 table then attributes a correspondingly larger score drop to it — the multiplicative relationship falls out of the calculation for free, without any decomposition formula.

### 3.3 Formal Equations

```text
Given: normalized legs L = [l₁, …, lₙ], n ≥ 2
       S = CalculateStructuralRisk(L)                 -- baseline, must have S.availability ∈ {Full, Limited}

For each i in 1..n:
    L₋ᵢ = L with lᵢ removed, order of remaining legs preserved
    Sᵢ  = CalculateStructuralRisk(L₋ᵢ)

    if Sᵢ.availability == Unavailable:
        MSC_i is UNDEFINED (leg i is "gate-dependent" — see §4, reported separately, not ranked numerically)
    else:
        MSC_i        = S.structuralScore − Sᵢ.structuralScore              (integer, the customer-facing quantity)
        MSC_i_precise = S.rescaled_precise − Sᵢ.rescaled_precise           (unrounded, internal precision — see §3.5 and §11)
```

`rescaled_precise` is the value `CalculateStructuralRisk` already computes internally (`adjusted_sum × 100 ÷ 78`, to 10-digit `BigDecimal` scale) **before** the final `HalfUp` round to an integer (`app/Domain/Risk/Engine/CalculateStructuralRisk.php`, the `$rescaled` local variable) — it exists today but is not currently exposed on `RiskAnalysisResult`. See §11's implementation note.

### 3.4 Ranking

Legs are ordered by descending `MSC_i` (ties broken per §3.5). Position 1 = the Weakest Leg. Position n = the leg whose removal would reduce the score least (or increase it least, if negative — §7.3) — informally, the "safest" or most structurally load-bearing-in-a-good-sense leg. This is a **full total order**, not just a top-1 pick, per the handover's mission statement ("ranked from strongest to weakest").

**Single-leg accumulators (n = 1):** not ranked. The one leg is, trivially and by definition, the entirety of the slip's risk; there is no comparison to make and no removal to model against (removing it leaves nothing, which is not a valid accumulator state). Report this as "not applicable — an accumulator has more than one selection," not as a degenerate one-element ranking.

### 3.5 Tie-Break Specification

`MSC_i` can tie exactly across legs — proven in §7.2 with a fully symmetric three-leg example, not merely asserted as a theoretical possibility. A ranking must be a strict total order (the success criterion "identical inputs always produce identical rankings" demands it), so ties are broken, in this exact sequence, until strictly resolved:

1. **`MSC_i` (integer, rounded)** — descending. Primary key; the customer-facing quantity.
2. **`MSC_i_precise` (unrounded internal rescale)** — descending. Resolves the common case where two legs round to the same integer drop but are not actually identical in effect (§7.1's worked example: two legs both show an integer drop of 4, but the unrounded values are 4.1883 and 4.1354 — genuinely different). **This step requires the small additive engineering change in §11**; if Engineering elects not to expose that value, skip directly to step 3 — correctness is unaffected, only tie-break granularity.
3. **Leg's own `decimal_odds`** — descending. Consistent with RF-003's own established principle that higher individual odds indicate greater individual elevation.
4. **Leg's own `market_complexity`** — descending (`complex` > `moderate` > `simple` > `unknown`), consistent with RF-005's own ordinal scale.
5. **Original slip-entry sequence position** — ascending (the leg the customer entered first wins the tie). This step exists purely to guarantee termination when two legs are, by every risk-relevant measure, genuinely identical (§7.2). **It carries no risk meaning whatsoever** and must never be presented to a customer as if it did — see §10's limitation on this point.

Steps 1–4 use only facts the approved mathematics already treats as risk-relevant. Step 5 is the sole non-mathematical tie-break, included only because a strict order is mathematically required and every risk-relevant signal has been genuinely exhausted at that point.

## 4. Interaction With the Analysis-Availability Gate — Gate-Dependent Legs

This is a real, provable edge case, not a hypothetical — found by direct analysis of the already-approved three-tier gate (`RISK_RULE_SET_2026_1.md` §17), not invented here.

**Claim:** removing a leg from `S` can cause `S₋ᵢ` to become `Unavailable` even though `S` itself was `Full` or `Limited`.

**Proof, using the Rule Set's own worked example table (§17):** a 4-leg slip with exactly one unrecognized-market leg is `Limited` — the proportion 1/4 = 25% is at, not over, the Tier 2 threshold ("4 football legs, 1 unrecognized market (25%) → Limited", the Rule Set's own row). Remove any *other* (recognized) leg from that slip: 3 legs remain, the same one unrecognized leg is now 1/3 ≈ 33.3% — over the 25% threshold — so `S₋ᵢ` is `Unavailable`. `MSC_i` is undefined for that leg by ordinary subtraction, because `Sᵢ.structuralScore` does not exist.

**Which legs this can affect (derived, not assumed):**
- **Tier 1 (sport gate) never causes this.** Removing a leg can only remove a bad-sport leg or leave the rest unchanged; it can never introduce a bad-sport leg among the remainder. Tier 1 status can only stay the same or improve on removal.
- **Tier 3 (deduction score) never causes this either.** The deduction is a flat `−8` per `partial` leg (capped at `−40`), not a proportion — it depends only on the *count* of partial legs remaining, which removing a *non-partial* leg never changes, and removing a *partial* leg only ever improves (fewer deductions, or no change once already capped).
- **Only Tier 2 (the unrecognized-market proportion) can cause this**, and only when `S` already has at least one unrecognized-market leg (i.e. `S` is already `Limited` via Tier 2, never when `S` is `Full`) — because only then is the proportion's numerator nonzero while its denominator can be pushed over the 25% line by shrinking `n` through removing a *different*, recognized leg. Removing the unrecognized leg itself only ever keeps or improves the proportion (numerator and denominator shrink together).

**Recommendation (Data Science Lab's decision right over ranking methodology, per this handover's §5):** a gate-dependent leg (one whose removal alone would make the remainder `Unavailable`) is **excluded from the numeric `MSC` ranking** — there is no valid subtraction to rank it by — and reported **separately**, with its own explicit flag and a proposed new reason code (§12), rather than assigned an interpolated or default rank. Mixing "this leg's removal blocks analysis" (a data-quality/eligibility fact) into the same ordinal position as "this leg carries the most structural risk" (a risk-magnitude fact) would blend two different kinds of signal — the exact mistake `RISK_RULE_SET_2026_1.md` §16 already rejected once for sport-level vs. market-level data-quality issues, applied here by the same principle to a different pair of concepts. How a leg that is *both* high-MSC *and* gate-dependent (a legitimate, if rare, double case) should be jointly presented is a UX Studio / Product Office presentation decision, out of scope here.

## 5. Explainability Requirements & Available Facts

Every ranked leg carries, at zero additional calculation cost (all values are already produced by the two `CalculateStructuralRisk` invocations that computed it):

- `score(S)` and `band(S)` — the accumulator's current score and band.
- `score(S₋ᵢ)` and `band(S₋ᵢ)` — what the score and band would be without this leg.
- `MSC_i` — the point difference, signed (§7.3 — never clamped to zero).
- A band-shift flag (e.g. `S` is High, `S₋ᵢ` is Moderate) — the single most customer-legible fact available, since a band change is a plain-language "this selection is what's keeping your slip in a higher risk category."
- **Reason-code delta** — the set difference between `S.reasonCodes` and `S₋ᵢ.reasonCodes` (which reason codes disappear, or newly appear, without this leg) — free, since both result sets already exist; no new reason-code logic required, only a set comparison over the existing, approved catalogue (`RISK_RULE_SET_2026_1.md` §18).

This is a descriptive breakdown for explanation purposes — it does not change what determines the ranking, which is `MSC_i` alone (§3.4). No customer-facing copy is finalized here; wording is UX Studio's domain per the Frontend Work Rule.

## 6. Confidence Interpretation

Not applicable, and not required. `MSC_i` is a deterministic difference between two deterministic, already-approved calculations — there is no sampling, estimation, or model uncertainty anywhere in it, so no confidence interval or probability statement is meaningful. (An optional future *interpretive banding* of `MSC` magnitude — e.g. "Negligible / Low / Moderate / High" contribution — is deliberately **not** proposed here: it would be a new threshold table requiring its own separate approval, and nothing in this work package's mission requires it; ordering alone answers "ranked from strongest to weakest.")

## 7. Worked Numerical Examples

All figures below were computed against Rule Set 2026.1's own formulas (§6–§13) exactly, following an initial hand derivation that was then independently re-verified with a Python/`Decimal` reference script reproducing every factor formula, both group caps, and the rescale transform to 50 significant digits of intermediate precision — this re-verification caught and corrected one hand-arithmetic error in an earlier draft (§7.1's L1 removal), which is exactly the kind of defect-in-the-illustration, not-the-formula distinction this office's own RF-003A precedent describes.

**Post-acceptance correction (Engineering implementation, 2026-07-26):** the same principle applied a second time, one level deeper. `App\Domain\Risk\Engine\RankLegsByStructuralWeakness`'s `BigDecimal` implementation reproduced every figure in this section exactly except §7.1's L4 `mscPrecise` — the production engine gives `37.4726`, not the `37.4727` this section previously stated. The defect traced to the Python re-verification script referenced above, not to the model or to Engineering's implementation: that script carried every factor's arithmetic to 50 significant digits throughout, never rounding each factor's own contribution to 4 decimal places before summing, as Rule Set 2026.1 §3 requires (`RiskConcentrationFactor`, `MarketComplexityFactor`, etc. each call `->toScale(4, RoundingMode::HalfUp)` on their own contribution — confirmed directly in the factor source). That omission is invisible for most vectors (rounding to 4 d.p. earlier vs. later rarely changes the final rounded score) but produced a genuine last-digit discrepancy for this one, larger computation chain. Corrected below; no other figure in this document was affected — every other value in §7 and §8 reproduced exactly. **The working principle stands unchanged: if Engineering's production implementation ever produces a different last-digit result than this document's figures, investigate this document's arithmetic first — the formula itself, not the illustration, is what Engineering must reproduce exactly.**

### 7.1 WLA-EX-01 — Ordinary Mixed Four-Leg Accumulator (the general case)

| Leg | Odds | Market Complexity |
|---|---:|---|
| L1 | 1.30 | simple |
| L2 | 1.50 | simple |
| L3 | 2.20 | moderate |
| L4 | 6.00 | complex |

**Baseline `S`:** combined odds 25.7400. RF-001 = 8 (n=4). RF-002 = 17.5740. RF-003 = 14.9099 (absolute 8.6667 + relative 6.2432, median 1.85, ratio 3.2432). RF-004 = 5.9306 (HHI 0.546531 over shares of {0.30, 0.50, 1.20, 5.00}/7.00). RF-005 = 3.75 (average complexity 0.75). Group A = 25.5740 (uncapped). Group B = 20.8405 (uncapped). Adjusted sum = 50.1645. Rescaled = 64.3135 (precise). **`structuralScore = 64`, High.**

| Removed leg | Remaining combined odds | Score | Band | `MSC` (int) | `MSC_precise` |
|---|---:|---:|---|---:|---:|
| L1 (1.30, simple) | 19.8000 | 60 | High | 4 | 4.1354 |
| L2 (1.50, simple) | 17.1600 | 60 | High | 4 | 4.1883 |
| L3 (2.20, moderate) | 11.7000 | 61 | High | 3 | 2.8281 |
| L4 (6.00, complex) | 4.2900 | 27 | Moderate | 37 | 37.4726 |

**Ranking (weakest → strongest): L4, L2, L1, L3.**

L4 is overwhelmingly the weakest leg — removing it drops the slip a full risk band, from High to Moderate. L1 and L2 tie at an integer `MSC` of 4 and are resolved by tie-break step 2 (§3.5): L2's removal has the larger true effect (4.1883 > 4.1354) because removing L2 (the larger of the two small "filler" weights, `odds − 1 = 0.50` vs. L1's `0.30`) leaves the *remaining* three legs' risk more concentrated (a smaller denominator sum in RF-004's share calculation) than removing L1 does — a genuine, subtle, correctly-captured effect that a naive "lower odds = weaker" heuristic would miss entirely (it would have ranked L1 and L2 as indistinguishable, or wrongly ordered by raw odds alone).

### 7.2 WLA-EX-02 — Exact Symmetric Tie (proves tie-break step 5 is necessary, not decorative)

Three legs, all odds 2.00, all `moderate` markets. **Baseline `S`:** combined odds 8.0000. RF-001 = 5 (n=3). RF-002 = 11.3333. RF-003 = 2 (absolute 2, relative 0 — ratio exactly 1.0). RF-004 = 0 (normalized concentration exactly 0 — every leg has an identical share). RF-005 = 5.0 (all moderate). Adjusted sum = 23.3333. Rescaled = 29.9145. **`structuralScore = 30`, Moderate.**

Removing *any one* of the three legs leaves two legs, both odds 2.00, both moderate — a fully deterministic result, identical regardless of which of the three was removed: combined odds 4.0000, RF-001 = 2, RF-002 = 7 (exact anchor), RF-003 = 2, RF-004 = 0, RF-005 = 5.0, adjusted sum = 16, rescaled = 20.5128. **`structuralScore = 21`, Low.**

`MSC_1 = MSC_2 = MSC_3` exactly, at every precision level (integer: 9; unrounded: 9.4017 for all three) — a genuine tie, because the three legs are, by every input the mathematics reads, identical. Tie-break steps 2–4 (§3.5) all tie as well (identical precise score, identical odds, identical complexity). **Only step 5 — original entry position — resolves it**, ranking L1 (entered first) as nominal "weakest," purely as a stable convention with no risk content (§10).

### 7.3 WLA-EX-03 — A Genuinely Negative `MSC` (a leg whose removal very slightly *raises* the score)

This is a rigorous, closed-form construction, not a coincidence found by search — proof follows the worked figures.

Thirteen legs: twelve "filler" legs at odds 1.05 (`simple`), one dominant leg at odds 50.00 (`complex`).

**Baseline `S` (n = 13):** RF-001 = 21. Combined odds ≈ 89.79 → RF-002 capped at 20. Raw Group A = 21 + 20 = 41 > 40 → **capped, Group A = 40 (exactly)**. RF-003 = 20 (absolute capped 12 + relative capped 8, since the dominant leg's odds/median ratio is far past both caps). RF-004 ≈ 14.6094 (concentration dominated by the one 50.00 leg). Raw Group B = 20 + 14.6094 ≈ 34.6094 > 28 → **capped, Group B = 28 (exactly)**. RF-005 = (2/13)/2 × 10 = 0.7692 (average complexity, 12 zero-point legs + 1 two-point leg). Adjusted sum = 40 + 28 + 0.7692 = 68.7692. Rescaled = **88.1657** (precise; rounds to 88).

**Remove one filler leg (n = 12, eleven fillers + the same dominant leg):** RF-001 = 21 (the table's own 12/13 plateau — **unchanged**). Combined odds ≈ 85.52, still ≥ 50 → RF-002 still capped at 20 (**unchanged**). Raw Group A = 21 + 20 = 41 > 40 → **still capped at 40 (unchanged)**. RF-003 = 20 (**unchanged** — the dominant leg is still the max, the median is still inside the filler block). Raw Group B ≈ 34.64 > 28 → **still capped at 28 (unchanged)**. RF-005 = (2/12)/2 × 10 = 0.8333 (**up** from 0.7692 — removing a zero-point filler raises the average complexity of the remaining, smaller set). Adjusted sum = 40 + 28 + 0.8333 = 68.8333. Rescaled = **88.2479** (precise; also rounds to 88).

**`MSC_precise` for the removed filler = 88.1657 − 88.2479 = −0.0822.** Negative.

**Why this is a provable, not accidental, result:** whenever Group A's raw sum exceeds 40 in *both* the before-state and the after-state, its adjusted contribution is pinned at exactly 40 regardless of which particular leg was removed — the cap absorbs the entire effect (proof: Group A's adjusted value is `min(raw, 40)` under proportional scaling, and once `raw > 40` in both states, both states evaluate to exactly the cap, independent of raw's exact value). The identical argument holds for Group B at 28. With both groups pinned, the **only** term that can move at all is RF-005, which has no group cap — and removing any leg whose own complexity is below the slip's current average complexity mechanically raises that average (a strictly smaller denominator, same or smaller numerator-of-points removed relative to what remained). The net effect is therefore governed entirely by RF-005's uncapped increase, which the rescale transform turns into a small negative `MSC`. This is a **structural consequence of RF-005 having no ceiling-sharing cap while RF-001/RF-002 and RF-003/RF-004 do**, not a numerical coincidence — the same construction (many saturated-cap legs plus one dominant leg, remove any "cheap-complexity" filler) reproduces it for other leg counts and odds, and a more extreme complexity spread or a larger filler count can widen the gap enough to flip the *rounded* integer score negative too, not merely the unrounded value shown here.

**Consequence for the model (see §10):** a genuinely low-odds, simple-market leg — the kind of selection a customer would intuitively call "the safe one" — can legitimately show a zero or slightly negative `MSC`. The model must **report this honestly**, never clamp it to zero, and never present such a leg as "contributing risk" it does not contribute.

## 8. Validation Test Vectors

These formalize §7's three worked examples as the model's canonical reference set, in the same "input → exact expected output, no tolerance" shape `RISK_RULE_SET_2026_1.md`'s own vectors use (Data Science Lab's own Quality Standard, `docs/offices/DATA_SCIENCE_LAB.md`).

| Vector | Legs (odds / complexity) | `score(S)` | Weakest leg | `MSC` (int) of weakest | Full ranking (weakest→strongest) | Notable property |
|---|---|---:|---|---:|---|---|
| WLA-TV-01 | 1.30/simple, 1.50/simple, 2.20/moderate, 6.00/complex | 64 (High) | L4 | 37 | L4, L2, L1, L3 | General case; tie-break step 2 required to order L1/L2 |
| WLA-TV-02 | 2.00/moderate ×3 | 30 (Moderate) | L1 (by tie-break only) | 9 | L1, L2, L3 (tie-break step 5 only) | Exact tie at every precision level except entry position |
| WLA-TV-03 | 1.05/simple ×12, 50.00/complex | 88 (Very High) | the 50.00/complex leg | — (dominant; MSC for that leg is large and positive, not shown above — §7.3 isolates the *filler* leg's behaviour) | dominant leg ranks weakest by a wide margin; every filler leg shows a small negative `MSC_precise` | Proves negative `MSC` is reachable; both cap groups pinned at their exact cap value before and after filler removal |
| WLA-TV-04 | 4 football legs, 1 unrecognized market (25%), 3 recognized | `S` = Limited, numeric score present | — | UNDEFINED for any of the 3 recognized legs | recognized legs excluded from numeric ranking (gate-dependent, §4); unrecognized leg ranks normally | Proves the gate-instability edge case from §4 using the Rule Set's own §17 worked-example row |

WLA-TV-03's dominant leg's own `MSC` (not the filler's) is a genuinely separate, large-magnitude computation (removing the one 50.00/complex leg collapses Groups A and B off their caps entirely) — left as an implementation-time reproduction check rather than hand-computed here, since §7.3's point (the filler's negative value) is what this vector exists to prove.

## 9. Mathematical Invariants

1. **Determinism.** Every step is a pure function of already-normalized, already-deterministic inputs (the underlying engine, subtraction, and a fully specified sort) — no randomness, clock, or external state anywhere (proof by construction, §2 point 2 and §3.3).
2. **Reproducibility.** Identical input legs (same odds, same normalized market facts, same order) always produce an identical ranking — direct consequence of invariant 1.
3. **`MSC` is not bounded below by zero.** Proven, not merely permitted — §7.3.
4. **`MSC` is bounded above by `score(S)` itself** (removing a leg can reduce the score to at most 0, never below).
5. **Gate-dependent legs never receive a numeric `MSC`** — by definition (§4), not by omission.
6. **Order independence of the ranking with respect to input leg order**, except for the fully-specified, documented tie-break at step 5 (§3.5) — i.e. reordering two *non-tied* legs never changes their relative rank; reordering two genuinely *tied* legs changes which one wins the position-based tie-break, which is the documented, intended behaviour of step 5, not a violation of determinism.
7. **Single-leg neutrality is inherited, not re-derived** — because `S₋ᵢ` for an `n = 2` slip is always a valid `n = 1` slip, and Rule Set 2026.1's own invariant (`RF-004 = 0` exactly at `n = 1`) already governs that computation; this model adds no new single-leg behaviour.
8. **Tier 1 and Tier 3 gate status can only stay the same or improve on leg removal; only Tier 2 can worsen it** — proven in §4, not assumed.

## 10. Known Limitations

- **Inherits Rule Set 2026.1's calibration caveat** (§21 of that document): every RF-00n table, anchor, and cap is expert-designed, not empirically fitted. `MSC`'s exact magnitudes are only as meaningful as that underlying calibration — this model changes nothing about that status, and does not claim greater precision than its inputs support.
- **`MSC` can be zero or negative** (§7.3) — this must never be clamped, hidden, or silently reinterpreted as zero. A leg with a negative `MSC` is genuinely, mathematically not a risk driver for this slip, however counterintuitive that may look next to its own individual odds.
- **Gate-dependent legs cannot receive a numeric rank at all** (§4) — a real gap in coverage, not an oversight; recommended to surface as a distinct, separately-labelled fact rather than force a number where none exists.
- **The final tie-break (entry position) carries no risk meaning** (§3.5 step 5, proven necessary in §7.2) — must never be phrased to a customer as if position in the slip were itself a risk factor.
- **RF-001 and RF-002 are irreducibly whole-slip properties.** A leg sitting inside a long or high-combined-odds slip can show a non-trivial `MSC` even if that leg's own odds and market look unremarkable in isolation — this is the model working correctly (leg count and combined odds are real, whole-slip structural facts), not a defect, but it means `MSC` should never be read as "this leg, by itself, is risky" — only as "the slip, with this leg removed, would be less risky by this amount."
- **RF-006 (Relationship) is inactive**, per Rule Set 2026.1 §11 — `MSC` is therefore completely insensitive to any same-event or correlated-selection relationship between legs, by direct inheritance of that existing, explicit scope limitation. If a future rule-set version activates RF-006, this model must be re-verified against the new formula before continued use — not silently assumed to still hold.
- **Performance is O(n) engine re-invocations per ranking** (n + 1 total, including the baseline) — trivial at the already-benchmarked sub-millisecond pure-engine speed (`TASKS.md`'s E-06B note: 0.615 ms per invocation), giving roughly 13 ms worst case at the 20-leg ceiling if normalization is correctly reused rather than repeated (§11) — but Engineering/Architecture should confirm this fits the Planner's expected interaction latency budget for "regenerate," which is a UX/product concern this document does not resolve.

## 11. Engineering Implementation Notes

This model requires **no change to any approved factor, weight, threshold, cap, or reason code** in Rule Set 2026.1. It requires exactly two small, additive, non-breaking items, both flagged explicitly rather than assumed:

1. **Reuse the already-normalized leg snapshot; do not re-normalize on removal.** Because per-leg normalization has no cross-leg dependency (§2 point 2, verified against the actual normalization code), computing `S₋ᵢ` only requires removing one `NormalizedBettingSlipLeg` from the already-normalized collection and re-invoking `CalculateStructuralRisk` — never re-running `NormalizeBettingSlip`. This is the only thing that makes the O(n) re-invocation cost trivial; re-normalizing on every removal would be needlessly expensive and is not required by anything in this model.
2. **Optional but recommended: expose the pre-round rescaled value.** `app/Domain/Risk/Engine/CalculateStructuralRisk.php` already computes an unrounded `$rescaled` `BigDecimal` internally before rounding to `structuralScore` — it is simply discarded today (`RiskAnalysisResult` stores only the rounded integer). Retaining and exposing this value (e.g. a new `public ?string $rescaledScorePrecise` or equivalent on `RiskAnalysisResult`) is a small, backward-compatible addition — not a formula change — needed only for tie-break step 2 (§3.5). If Engineering or Architecture prefers not to make this change, the model remains fully correct and deterministic without it; only tie-break granularity is reduced (falls through to steps 3–5 sooner).

No new domain model, migration, or persistence decision is proposed here — where (or whether) `MSC` results are persisted, and how they interact with `ADR-007`'s immutability discipline or `ADR-008`'s proposed planner/engine boundary, is Architecture Office's decision, not this document's. This document specifies the mathematics only, per the handover's own scope (§7 "Out of Scope": no production code, no repository implementation).

## 12. New Reason Code — Proposed, Not Adopted

`RISK_RULE_SET_2026_1.md` §18 is the sole authoritative reason-code catalogue. This document identifies exactly one genuinely new signal (§4's gate-dependent leg) that has no existing code to express it, and proposes, rather than adds, the following for Product Office / Data Science Lab joint consideration alongside the rest of this specification:

| Proposed Code | Meaning | Severity | Customer-Facing |
|---|---|---|---|
| `LEG_REMOVAL_BLOCKS_ANALYSIS` | Removing this leg alone would push the remaining accumulator's unrecognized-market proportion (Tier 2, §17) over the 25% threshold, making the remainder `Unavailable` | Medium | Yes — explains why this selection has no numeric ranking value, distinct from it being risky |

This code is **not** added to the catalogue by this document — per §18's own governance ("this document is the single authoritative source"), any addition to Rule Set 2026.1's catalogue requires the same Product Office / Data Science Lab joint process as the rule set itself.

## 13. Final Report

### A. Deliverables Checklist (per handover §8)

- Mathematical specification — §3.
- Formal equations — §3.3.
- Explanatory narrative — §3.2, §5.
- Deterministic ranking methodology — §3.4.
- Tie-break specification — §3.5.
- Confidence interpretation — §6 (not applicable; reasoned, not skipped).
- Worked numerical examples — §7 (three, covering the general case, an exact tie, and a negative-`MSC` proof).
- Validation test vectors — §8.
- Mathematical assumptions — §2.
- Limitations of the model — §10.
- Implementation notes for Engineering — §11.
- Identified risks and assumptions — §4 (gate interaction), §10.

### B. Independent Scientific Judgement (per handover §12)

The existing Risk Engine philosophy (a pure, deterministic, versioned calculator with no persistence or randomness) is **sufficient** to support a mathematically sound weakest-leg model — no alternative philosophy or new dependency was needed. The one genuine gap found during this analysis (§4's gate-instability edge case) is a consequence of the *existing, already-approved* Tier 2 gate design interacting with leg removal, not a defect in that gate — it is reported here as a newly-connected fact (two existing rules, connected for the first time), in the same spirit as the Architecture Office's U-06.1 discovery connecting ADR-007's known gap to U-06's "Regenerate" requirement.

### C. Recommendation

```text
Approve the Marginal Structural Contribution (MSC) model as Programme U-06.2A's weakest-leg
mathematics, subject to Product Office / Data Science Lab joint sign-off recorded in
DECISION_LOG.md, exactly as Rule Set 2026.1's own acceptance discipline requires.
```

No unresolved mathematical question remains open in this document. If Product Office or a future Data Science Lab review disagrees with a specific choice above (the leave-one-out framing itself, a tie-break ordering, or the gate-dependent-leg exclusion policy), the recommendation changes to *revise the specified decision*; absent that, this document is ready to sign.

### D. Return of Ownership

Per the handover's §11: constitutional ownership of this work package returned to **Product Office** upon delivery of this document, per this office's own Prohibited Actions (`docs/offices/DATA_SCIENCE_LAB.md`: "Never writes application code... Never unilaterally declares a Rule Set Accepted") — Data Science Lab did not commission Engineering Office, write application code, or make any persistence, UI, or architecture decision.

**Product Office has since formally accepted this specification** (`PO-U06.2A-AC-001`, 2026-07-26, recorded in `docs/00-governance/DECISION_LOG.md`) as the authoritative engineering reference for weakest-leg attribution. Acceptance covers the model, the ranking and tie-break methodology, the worked examples and validation vectors, the engineering implementation notes, and the documented limitations, exactly as submitted — no mathematics was revised as part of accepting it. Engineering implementation still requires its own separate commissioning (per this office's Handover Rules and `ADR-008`'s still-Proposed planner/engine boundary); acceptance of the mathematics is not itself that commission.

**Returned By:** Data Science Lab
**Status:** Accepted by Product Office — implementation not yet separately commissioned.
