# Domain Model

## User
Owns slips, analyses, and journal entries.

## BettingSlip
Suggested fields: user_id, bookmaker_id nullable, name nullable, status, total_decimal_odds, leg_count, submitted_at, analyzed_at.

## BettingSlipLeg
Suggested fields: betting_slip_id, sport, competition nullable, event_name, event_start_time nullable, market_name, selection_name, decimal_odds, display_order.

## SlipAnalysis
Immutable result with betting_slip_id, engine_version, rule_set_version, overall_risk_score, risk_band, weakest_leg_id nullable, data_quality_score nullable, summary_payload, and analyzed_at.

## LegAnalysis
Stores per-leg contribution, risk band, rule results, and explanation payload.

## JournalEntry
Stores user, slip, decision, optional result, note, and reflection.

## Bookmaker
Minimal catalogue entity until integrations require more.

## FeatureFlag
Simple auditable feature control.
