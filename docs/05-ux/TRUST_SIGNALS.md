# Trust Signals

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Explainability System](EXPLAINABILITY_SYSTEM.md), [Design Language](DESIGN_LANGUAGE.md), [Homepage Storyboard](HOMEPAGE_STORYBOARD.md) |

---

Every visual and textual mechanism SlipGuard uses to earn trust — and, just as important, the language permanently forbidden from appearing anywhere in the product because it would destroy that trust. Where `EXPLAINABILITY_SYSTEM.md` defines how a *result* is explained, this document defines how the *product as a whole* signals that it can be believed.

## Trust Mechanisms

Each of the following is a legitimate, reserved trust signal — used consistently, never decoratively, and never invented ad hoc per screen:

- **Versioned rule sets.** Every analysis states which rule-set version produced it (`docs/03-data-science/RISK_RULE_SET_2026_1.md`'s `ruleSetVersion`) — proof the math is a fixed, auditable thing, not a black box that changes silently.
- **Deterministic analysis.** Stated plainly wherever methodology is shown: the same slip always produces the same result under the same rule-set version (`ADR-002`).
- **No prediction.** Restated at the point of highest doubt — directly beside the risk band itself, not buried in a footer.
- **No betting tips.** SlipGuard never recommends a bet, a leg, or an adjustment as "better" in outcome terms — only in structural-risk terms (`EXPLAINABILITY_SYSTEM.md`'s Language Rules).
- **Transparent limitations.** A Limited or Unavailable analysis always states why (`EMPTY_STATES.md`'s Unavailable Analysis / Unsupported Sport states).
- **Data quality indicators.** Shown independently from the risk score, never blended into it (`docs/03-data-science/RISK_RULE_SET_2026_1.md` §16's independence rule).
- **Analysis timestamps.** Every report states when it was produced — lets a user judge whether it's still current.
- **Rule-set version, analysis version.** Displayed in the Rule Set Information / Analysis Metadata stages of the Explanation Hierarchy (`EXPLAINABILITY_SYSTEM.md`), never hidden or omitted to keep a screen "clean."
- **Normalization status.** When a leg's market or sport couldn't be fully classified, that is shown, not silently absorbed into a generic result.
- **Supported sports / unsupported sports.** Stated plainly wherever relevant (builder, unavailable-analysis states, methodology) — SlipGuard is specific and upfront about scope rather than vague about coverage.
- **Limited analysis messaging / Unavailable analysis messaging.** Always specific to the actual gate that fired (`EMPTY_STATES.md`), never a single generic "reduced accuracy" message covering multiple different causes.
- **Operator independence** (added `PO-RC1-007`, 2026-08-07 — Product Office directive commissioned homepage trust messaging naming this mechanism; it was real and already Locked but had never been added to this list or stated on the homepage). Stated plainly wherever the Trust section appears: no affiliate agreement, bookmaker partnership, or commercial incentive ever biases a calculation, classification, or recommendation (`ADR-012`, `CLAUDE.md`'s Locked Decisions). This is a standing product boundary, not a claim invented for marketing — it is checkable the same way every other mechanism in this list is: nothing in the codebase reads a bookmaker identity, promotional flag, or affiliate relationship anywhere in the Risk Engine, Planner, or Builder.

## Forbidden Language

Never appears anywhere in the product — marketing, in-app copy, error states, or otherwise:

`Guaranteed` · `Winning strategy` · `Safe bet` · `Lock` · `Banker` · `Sure odds` · `AI knows` · `Likely winner` · `Expected winner` · `Most probable outcome`

Each of these either promises a result SlipGuard cannot deterministically guarantee, or implies outcome prediction, both of which are locked prohibitions (`docs/09-compliance/PRODUCT_GUARDRAILS.md`'s Prohibited Claims; `docs/00-governance/VISION_AND_PRINCIPLES.md`).

## Required Language

The vocabulary trust is built from — used consistently rather than varied for style:

`Structural Risk` · `Rule Set` · `Deterministic` · `Analysis` · `Data Quality` · `Explanation` · `Supported` · `Unavailable` · `Limited` · `Transparent`

These terms already have fixed meanings in `docs/00-governance/PRODUCT_GLOSSARY.md` and `EXPLAINABILITY_SYSTEM.md`'s Language Rules — using a synonym instead of the approved term (e.g. "confidence" instead of "data quality") reintroduces exactly the ambiguity these documents exist to remove.

## Where Trust Signals Appear

- **Homepage** — the Trust section of `HOMEPAGE_STORYBOARD.md` states these mechanisms directly, as a value proposition, not legal boilerplate.
- **Every analysis screen** — Rule Set Information and Analysis Metadata stages of the Explanation Hierarchy (`EXPLAINABILITY_SYSTEM.md`).
- **Every Limited/Unavailable state** — `EMPTY_STATES.md`'s corresponding entries.
- **Footer / methodology** — a permanent, always-accessible statement of what SlipGuard is and isn't, matching `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s Transparency requirement.

Trust signals are never used decoratively (e.g. a version number shown only because it "looks technical and credible") — each one is included because it gives the user real, checkable information.
