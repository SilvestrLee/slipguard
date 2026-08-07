# MVP Definition

**Correction (`PO-U23-002`, 2026-08-07):** two lines below no longer describe current state, corrected in place per this document's small size (see `FEATURE_MATRIX.md`'s own correction note for the fuller record). "Weakest-leg explanation" is corrected to "main contributing factor explanation" — the base Risk Report this document's Primary Journey describes shows the Main Contributing Factor (a risk-factor concept, `resources/views/livewire/betting-slips/report.blade.php`), not a per-leg weakest-leg ranking. A genuinely separate weakest-leg ranking engine (`App\Domain\Risk\Engine\RankLegsByStructuralWeakness`, the Marginal Structural Contribution model, `U-06.2A`/`E-06D.1`) does exist and is wired into the Planner and Capability B — real, not absent — just not part of the base analysis path this document scopes. "Automated parsing" and "live sports data" are removed from Explicitly Not Required — deterministic text/PDF slip parsing shipped in MVP, and Capability B ("Build an Accumulator") ships in MVP gated behind `MARKET_WIDE_PLANNER_ENABLED`, drawing on live fixture/market data. Per-bookmaker automated parsing (as opposed to the deterministic taxonomy-based parser that did ship) remains not built.

## Hypothesis
Bettors will value a product that clearly identifies structural weaknesses in a betting slip without pretending to predict outcomes.

## Must Work
Registration, login, manual slip entry, multi-leg validation, deterministic analysis, main contributing factor explanation, saved report, history, journal decision, and operations inspection.

## Explicitly Not Required
Per-bookmaker automated parsing, payments, native apps, social features, or outcome prediction.

## Exit Condition
The product is ready for controlled beta when the primary journey is reliable, understandable, secure, and measurable.
