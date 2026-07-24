# Risk Engine

**Status:** Contract approved; exact production formula pending Data Science approval.

## Purpose
Measure explainable structural decision risk in a betting slip. The engine does not predict outcomes.

## Inputs
Per leg: decimal odds, sport, event, market, selection, optional competition and event time, and completeness indicators.

Per slip: leg count, combined decimal odds, optional bookmaker, and submission timestamp.

## Outputs
Overall risk score, risk band, weakest leg, per-leg contribution, accumulator compounding contribution, data-quality indicator, stable reason codes, and structured explanation facts.

## Candidate Factors
Exact weights and thresholds are not approved:
- Leg count.
- Combined odds.
- Individual high-odds legs.
- Risk concentration.
- Market complexity.
- Missing or ambiguous data.
- Detectable correlation.
- Accumulator compounding.

## Reason Codes
`LEG_COUNT_HIGH`, `COMBINED_ODDS_EXTREME`, `LEG_ODDS_OUTLIER`, `RISK_CONCENTRATED`, `MARKET_COMPLEXITY_HIGH`, `INPUT_DATA_INCOMPLETE`, `POTENTIAL_CORRELATION`, `ACCUMULATOR_COMPOUNDING`.

## Weakest Leg
The leg contributing the greatest explainable structural risk under the active engine version. It is not a prediction that the leg will lose.

## Versioning
Store engine version, rule-set version, reproducible normalized input, rule outputs, final score, and band. Historical analyses must not be silently recalculated.

## AI Boundary
AI may explain approved findings. It may not calculate, change bands, select the weakest leg, invent facts, predict outcomes, or add unsupported advice.

## Blocker
Before risk implementation, Data Science must approve formulas, weights, thresholds, normalization, market taxonomy, worked examples, edge cases, and test vectors.

## Normalization Status
Football sport and market normalization (taxonomy version 1.0) is implemented — see `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md` and `App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1`. This is engine-preparation only: it classifies free-text input into stable codes and does not calculate risk. Formulas, weights, and thresholds remain unapproved and blocked as above.
