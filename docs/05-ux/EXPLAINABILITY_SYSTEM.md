# Explainability System

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [UX Rules](UX_RULES.md), [Design Language](DESIGN_LANGUAGE.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Trust Signals](TRUST_SIGNALS.md) |

---

The constitutional guide for how every analysis screen communicates its results. This document defines **how results are explained** — not how the risk engine calculates them (`docs/03-data-science/RISK_RULE_SET_2026_1.md` is the mathematical authority; this document never restates or paraphrases a formula). If an analysis screen's copy or layout conflicts with this document, this document wins.

## Philosophy

Explainability is SlipGuard's primary competitive advantage over a plain odds calculator or a tipster app. A user should never need to ask "why did I receive this score?" — the answer should already be on the screen, in plain language, before they think to ask.

Every result must be understandable without exposing unnecessary mathematical complexity. Showing the *reasoning* is not the same as showing the *mathematics* — most users need the former, and only some will ever want the latter (see Progressive Disclosure).

## Principles

- **Explain first, score second.** The number is never presented before the reason it exists.
- **Trust before precision.** A user trusting the result matters more than the user seeing every decimal place — precision is available on request (Progressive Disclosure), not forced on arrival.
- **Never overwhelm.** One primary finding at a time; supporting detail is opt-in.
- **Progressive disclosure**, always — see below.
- **No academic wording.** If a term needs a statistics degree to parse, it doesn't belong in the default view (`UX_RULES.md`'s Language rules).
- **No gambling hype.** No slang, no excitement, no "hot" or "cold" framing.
- **No predictive language.** SlipGuard explains structural risk, never a result (`docs/00-governance/VISION_AND_PRINCIPLES.md`: "SlipGuard Is Not... a prediction engine").
- **No probability language.** "Confidence," "likely," and "chance" all imply outcome probability unless explicitly scoped to data quality — see Language Rules.
- **No "AI thinks..."** framing anywhere. Every displayed result traces back to a deterministic rule, never a model's opinion (`docs/03-data-science/RISK_RULE_SET_2026_1.md`; ADR-003's AI boundary).

## Explanation Hierarchy

```
Overall Structural Risk
        ↓
Primary Finding
        ↓
Top Contributing Factors
        ↓
Supporting Details
        ↓
Data Quality
        ↓
Rule Set Information
        ↓
Analysis Metadata
```

This is a **refinement** of `UX_RULES.md`'s existing Risk Report Hierarchy (headline → band → weakest leg → main reasons → per-leg detail → methodology/disclaimer → next action), not a replacement — the two describe the same report at different levels of detail:

| UX_RULES.md stage | Explanation Hierarchy stage |
|---|---|
| Plain-language headline | Overall Structural Risk |
| Overall risk band | Overall Structural Risk |
| Weakest leg | Primary Finding *(today: the top contributing factor; true per-leg weakest-leg ranking is E-06D — see Future Compatibility)* |
| Main reasons | Top Contributing Factors |
| Per-leg detail | Supporting Details |
| Methodology and disclaimer | Rule Set Information + Analysis Metadata |
| Save, revise, or journal action | *(follows the hierarchy, unchanged — see UX_RULES.md)* |

Nothing interrupts this flow — no promotional content, no upsell, no unrelated navigation, breaks the sequence between Overall Structural Risk and the user's next action.

## Progressive Disclosure

Visible by default:
- Structural Risk (the headline number and its band).
- Risk Band label (Low / Moderate / High / Very High).
- Primary Finding (one plain-language sentence — today, the largest single contributing factor).
- Two or three major contributors, each in one plain sentence.

Hidden until expanded:
- Full factor-by-factor calculations and contribution values.
- Mathematical traces (raw inputs, normalized values, interpolation detail — `FactorTrace` in the engine).
- Raw normalization detail (how a leg's market/sport text was classified).
- Rule-set references (which rule set version, which formula).
- Version metadata (engine, rule-set, taxonomy, input-schema versions).

The collapsed/expanded pattern follows `COMPONENT_PRINCIPLES.md`'s Reports component and `MOTION_SYSTEM.md`'s expand/collapse motion — never a separate page, always an in-place reveal.

## Language Rules

| Always | Never |
|---|---|
| "Structural Risk" | "Chance of Losing" |
| "Analysis" | "Prediction" |
| "This slip contains characteristics commonly associated with higher structural risk." | "This slip is likely to lose." |
| "Data Quality" (completeness/reliability of the analysis itself) | "Confidence" used to mean outcome likelihood (`docs/00-governance/PRODUCT_GLOSSARY.md`'s "Confidence" entry) |
| "Contributing factor" | "Risk of losing" |
| "Structurally elevated" | "Dangerous" / "Risky bet" (implies a betting-advice judgment, not a structural observation) |

Every sentence describing a result should be checkable against one question: *does this describe the slip's structure, or does it describe the future?* Only the former is ever permitted.

## Customer Trust

- Always explain limitations plainly — a Limited analysis states *why* it's limited, not just that it is.
- Always explain an Unavailable analysis — which gate stopped it (unsupported sport, too many unrecognized markets) and what the user could change, in plain language.
- Never hide missing or partial data behind a generic "N/A" — say what's missing and why it doesn't block the parts of the analysis that don't depend on it (`docs/03-data-science/RISK_RULE_SET_2026_1.md` §16's data-quality independence).
- Never fake certainty. If the rule set doesn't have a confident answer for something, the interface says so instead of rounding it away.

## Future Compatibility

This system is written to accommodate future capability without needing a rewrite when they land — none of the following are implemented today, and none are documented here beyond this acknowledgment:

- **Weakest-leg explanations** (E-06D) — will slot into the "Primary Finding" stage of the hierarchy above as a named, ranked leg rather than an aggregate top factor.
- **Rule-set versioning** — the hierarchy's "Rule Set Information" stage already reserves space for a version label; future rule-set changes don't require a new hierarchy stage, only a version value change.
- **Historical comparisons** ("this slip is riskier than your average") — would attach as an additional, clearly-labelled supporting detail, never blended into the Primary Finding.
- **Future relationship factor** (RF-006, currently inactive) — when active, its explanation follows the same Top Contributing Factors treatment as every other factor; no special-cased UI is pre-built for it.
- **Multiple sports** — the hierarchy and language rules are sport-agnostic already; only the taxonomy and normalization layers (out of this document's scope) would need to change.
