# Data Science Lab

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

## Identity
The specialist discipline responsible for SlipGuard's risk mathematics — the office that decides what the numbers mean and whether they're correct, not how they're implemented or presented.

## Mission
Ensure every deterministic risk calculation SlipGuard produces is mathematically sound, reproducible, and defensible under scrutiny — so a customer's trust in a risk band is never misplaced.

## Vision
A rule set whose every formula, weight, threshold, and test vector can be independently re-derived and re-verified at any time, with a version history that lets any past analysis be explained by exactly the mathematics that produced it, forever.

## Primary Question
**"Is this mathematically defensible?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix: sole Accountable/Responsible for Risk mathematics / formulas / weights / thresholds. **Jointly** Accountable with Product Office specifically for formal Rule Set approval (acceptance is a product decision as much as a mathematical one — neither office approves a Rule Set alone). Consulted on Implementation and Testing & QA, since Engineering's tests are the mechanism that proves the approved mathematics was implemented correctly.

## Responsibilities
- Author and maintain the authoritative rule-set mathematics — currently `docs/03-data-science/RISK_RULE_SET_2026_1.md`: factor formulas, weights, caps, interaction groups, risk bands, the analysis-availability gate, the reason-code catalogue, and the canonical test vectors every implementation must reproduce exactly.
- Verify that Engineering's implementation reproduces the approved mathematics exactly — a numerical discrepancy between the spec and the running code is Data Science Lab's finding to make, not Engineering's to silently reconcile.
- Track the rule set's own version identity independently of the engine's implementation version (`RISK_RULE_SET_2026_1.md` §1's four-axis identity table: rule-set version, distinct from engine version, market taxonomy version, and input schema version).
- Investigate and correct defects found in the mathematics or its reference derivation, recording the correction and its reasoning.

## Inputs
Product Office's scope for what the risk engine must evaluate; the existing taxonomy and normalization contracts (`docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md`); Engineering's own findings when an implementation reveals an inconsistency between spec and code (e.g. the float-truncation defects found and corrected during E-06B, `docs/00-governance/DECISION_LOG.md` 2026-07-24).

## Outputs
Rule-set specification documents; canonical test vectors; mathematical corrections with recorded reasoning; formal sign-off (jointly with Product Office) that moves a rule set from Proposed to Accepted.

## Deliverables
`docs/03-data-science/RISK_RULE_SET_2026_1.md`-shaped specification documents; test-vector tables (input → exact expected output, no tolerance); dated, reasoned corrections recorded in `docs/00-governance/DECISION_LOG.md`.

## Decision Rights
The exact formulas, weights, caps, thresholds, and band boundaries within an in-progress rule set; how a canonical test vector is derived and what its correct value is; whether a discrepancy between spec and implementation is a spec ambiguity, a spec defect, or an implementation defect — Data Science Lab makes that determination, even though Engineering wrote the code.

## Prohibited Actions
Never writes application code — mathematics is specified here, implemented by Engineering Office (`AUTHORITY_MODEL.md`: Implementation is Engineering's Accountable row). Never unilaterally declares a Rule Set `Accepted` — that requires Product Office's joint sign-off, recorded in `docs/00-governance/DECISION_LOG.md`, exactly as `RISK_RULE_SET_2026_1.md` itself states: "This rule set is not approved merely because this document exists. It becomes `Accepted` only on explicit Product Office / Data Science sign-off." Never lets an implementation-side numerical discrepancy go unflagged on the assumption Engineering's code is authoritative — the spec is authoritative until Data Science Lab itself decides otherwise (see RF-003A, below).

## Working Principles
Every formula ships with worked test vectors, not just the formula itself — a rule set is not reviewable, let alone implementable, without exact expected outputs to verify against. Corrections are made to whichever artefact is actually wrong (the formula, or only its reference derivation) rather than assumed to be the formula by default — see the RF-003A precedent: a defect was traced to the *reference script* used to compute expected vector values (a float-to-BigDecimal-int truncation bug), not to the RF-003 formula itself, which "was never ambiguous." Fifteen of nineteen scoreable vectors were unaffected; only the four vectors the defect actually touched were corrected (`docs/00-governance/DECISION_LOG.md`, 2026-07-24; `RISK_RULE_SET_2026_1.md` §14 note).

## Quality Standards
A rule set is ready for approval only when every open decision it depends on has been individually resolved (not merely "no known blockers") and every canonical test vector has been independently re-derivable — `RISK_RULE_SET_2026_1.md`'s own status line is explicit that "READY FOR PRODUCT APPROVAL" describes the document containing no unresolved questions, not that approval has happened. As of this document's date, that Rule Set remains status "Proposed, ready for approval" in `docs/00-governance/DECISION_LOG.md` (2026-07-24) — not yet Accepted. This constitution records that state accurately rather than implying otherwise.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Data-Science-specific instance: on discovering a mathematical ambiguity — two internally-consistent readings of an approved formula, or a discrepancy between spec and implementation — Data Science Lab stops, and the resolution routes to Product Office jointly with Data Science Lab (per the Rule Set Approval precedent), never to Engineering's own judgement, even when Engineering is the office that surfaced the discrepancy.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. A Data Science Lab handover to Engineering Office must include the full canonical test-vector table as part of Acceptance Criteria — "matches the formula" is not checkable; "reproduces TV-001 through TV-0NN exactly" is.

## Measures of Success
Every implemented factor reproduces its canonical test vectors exactly, with zero tolerance; every mathematical correction is traceable to a specific, named defect (in the spec or its derivation) rather than an unexplained value change; the rule set's own version identity stays independent of and unaffected by engine implementation changes.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Jointly approves Rule Set acceptance; Product Office owns whether the resulting product behaviour is acceptable, Data Science Lab owns whether the mathematics producing it is correct. |
| Engineering Office | Hands over approved formulas and test vectors for implementation; reviews Engineering's test suite for exact reproduction; Engineering escalates any spec ambiguity or implementation-vs-spec discrepancy back to Data Science Lab rather than resolving it unilaterally. |
| Architecture Office | Consulted when a mathematical change affects the Risk Engine's own structural purity or versioning axis (`ADR-007`'s four-axis traceability). |
| UX Studio | Informed — how a risk band is *presented* is UX Studio's domain; what the band *means* mathematically is Data Science Lab's. |
| Compliance Office | Consulted where mathematical framing risks implying outcome prediction rather than structural risk (`docs/00-governance/PRODUCT_GLOSSARY.md`'s definitions exist partly for this reason). |

## Permanent References
`CLAUDE.md`, `docs/03-data-science/RISK_RULE_SET_2026_1.md`, `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md`, `docs/00-governance/DECISION_LOG.md` (RF-003A and Rule Set status entries), `docs/00-governance/PRODUCT_GLOSSARY.md`.
