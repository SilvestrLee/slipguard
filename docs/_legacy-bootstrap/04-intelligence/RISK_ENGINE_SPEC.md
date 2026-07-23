# SlipGuard Risk Engine Specification

**Version:** 0.1  
**Status:** Draft contract — formulas require Data Science approval  
**Owner:** Data Science Lab

## Purpose

Define the deterministic contract that Engineering can implement without turning SlipGuard into a prediction product.

## MVP Inputs

For each leg:

- Decimal odds.
- Sport.
- Event description.
- Market description.
- Selection description.
- Optional event time.
- Optional competition.
- Input completeness indicators.

For the slip:

- Number of legs.
- Combined decimal odds.
- Optional bookmaker.
- Submission timestamp.

## MVP Outputs

- Overall risk score on a documented scale.
- Risk band.
- Weakest leg.
- Per-leg risk contribution.
- Accumulator compounding contribution.
- Data-quality or analysis-confidence indicator.
- Structured reason codes.
- Human-readable explanation generated from reason codes.

## Important Distinction

The engine measures the structure and decision risk of the submitted slip.

It does not calculate the true probability that a team or selection will win unless a later approved data model and reliable data source explicitly support that calculation.

## Initial Explainable Factors

Data Science must approve exact weights and thresholds before production use.

Potential MVP factors:

1. Number of legs.
2. Combined odds.
3. Individual high-odds legs.
4. Concentration of risk in one leg.
5. Repeated or correlated event exposure when detectable.
6. Market complexity classification.
7. Missing or ambiguous input data.
8. Time or event mismatch when available.
9. Accumulator compounding.

## Reason Codes

Every rule should emit a stable code, for example:

- `LEG_COUNT_HIGH`
- `COMBINED_ODDS_EXTREME`
- `LEG_ODDS_OUTLIER`
- `RISK_CONCENTRATED`
- `MARKET_COMPLEXITY_HIGH`
- `INPUT_DATA_INCOMPLETE`
- `POTENTIAL_CORRELATION`
- `ACCUMULATOR_COMPOUNDING`

Do not hard-code final customer prose into mathematical rules.

## Risk Bands

Final thresholds require approval.

Suggested semantic bands:

- Low Structural Risk
- Moderate Structural Risk
- High Structural Risk
- Very High Structural Risk

Avoid “safe”, “guaranteed”, or “certain”.

## Weakest-Leg Contract

Weakest leg means:

> The leg contributing the greatest explainable structural risk under the active engine version.

It does not mean the leg is predicted to lose.

Tie-breaking rules must be deterministic.

## Versioning

Every completed analysis stores:

- Engine version.
- Rule set version.
- Input snapshot or reproducible normalized payload.
- Rule outputs.
- Final score and band.

A later engine update must not silently rewrite historical analyses.

## AI Boundary

An LLM may receive:

- Approved score.
- Risk band.
- Weakest-leg label.
- Reason codes.
- Approved explanation facts.
- Tone and reading-level instruction.

An LLM may not:

- Change scores.
- Change risk bands.
- Select the weakest leg.
- Invent match facts.
- Claim a result will occur.
- Introduce betting recommendations unsupported by the rules.

## Required Next Decision

Before E-03 begins, Data Science must supply:

- Exact formula.
- Weights.
- Thresholds.
- Normalization rules.
- Market taxonomy used in MVP.
- Worked examples.
- Edge cases.
- Test vectors.