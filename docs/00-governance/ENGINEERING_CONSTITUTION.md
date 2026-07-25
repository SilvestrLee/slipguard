# SlipGuard Engineering Constitution

**Status:** Active
**Effective:** 2026-07-25
**Applies To:** All engineering work in this repository, from this point forward
**Ratified By:** Product Office + Architecture Office (Governance Consolidation Sprint G-01)
**Related Documents:** `CLAUDE.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`, `docs/00-governance/WORKING_PRINCIPLES.md`, `docs/00-governance/DECISION_LOG.md`, `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`, `docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md`, `docs/00-governance/GOVERNANCE_CONSOLIDATION_G01.md` (the audit trail behind this document)

## Purpose

`docs/00-governance/WORKING_PRINCIPLES.md` already governs *how* engineering executes day-to-day work (ship vertical slices, avoid speculative engineering, and so on). This document is different in kind: it governs *who decides what*, *when engineering must stop and escalate rather than decide*, and *how the platform is permitted to change* now that the Production Foundation is certified (`docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md`, 2026-07-25, certifying commit `18d1c4c`).

Nothing below is a new decision. Every principle here was already being followed, consistently, before this document existed — `docs/00-governance/GOVERNANCE_CONSOLIDATION_G01.md` records the prior instances each section codifies. This document exists so a future contributor can learn these rules by reading the repository, not by reconstructing prior conversations.

---

## 1. Authority Matrix

| Area | Authority |
|---|---|
| Product Vision | Product Office |
| Product Behaviour | Product Office |
| Mathematical Models | Data Science Lab |
| Risk Formula Calibration | Data Science Lab |
| Rule Set Approval | Product Office + Data Science Lab |
| Software Architecture | Architecture Office |
| Implementation | Engineering Office |
| UX Strategy | UX Studio* |
| Testing | Engineering Office |
| Release Approval | Product Office + Engineering Office |

\* Every document under `docs/05-ux/` records `Owner: Product Office` in its own header. "UX Studio" and "Product Office" are used interchangeably for UX ownership in current repository practice — this is not a second, competing authority over UX strategy, and this matrix does not create one.

**Engineering implements. Engineering does not reinterpret product intent. Engineering does not redefine mathematics. Engineering does not redesign architecture without approval.**

## 2. Behaviour Change Policy

Engineering shall never independently:

- invent mathematics
- recalibrate scores
- redefine confidence bands
- alter Rule Sets
- reinterpret product intent
- redesign UX behaviour
- introduce customer-facing behaviour changes
- redefine business terminology (see `docs/00-governance/PRODUCT_GLOSSARY.md`)

If Engineering discovers ambiguity: **Stop. Document. Escalate. Await Product Office decision.**

This is the same discipline already demonstrated by the RF-003A correction (`docs/00-governance/DECISION_LOG.md`, 2026-07-24): the defect found was in a *reference script*, not in the approved formula, and the correction was recorded as a dated, reasoned decision rather than silently patched. The E-06C Validation sprint applied the identical discipline to Category D findings — documented as observations for Product/Architecture Office, never acted on unilaterally.

## 3. Certified Architecture Preservation

> Preserve certified architecture before introducing new abstractions.

Engineering may improve architecture only when:

- measurable benefit exists,
- deterministic behaviour is preserved,
- regression tests pass,
- governance remains intact,
- behavioural intent is unchanged.

Avoid architectural churn. Prefer disciplined evolution over invention.

## 4. Repository Evolution Philosophy

SlipGuard evolves through **extension, composition, versioning, documentation, and deterministic validation** — not through continual redesign, unnecessary rewrites, speculative abstractions, trend-driven architecture, or framework experimentation. The platform should grow without losing historical integrity: a persisted `SlipAnalysis` must remain reproducible under the exact engine/rule-set/taxonomy/schema versions it was created with, forever (`docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`).

## 5. Documentation Authority Hierarchy

```
Product Constitution
        ↓
Vision & Principles
        ↓
Architectural Decision Records (ADR)
        ↓
Decision Log
        ↓
Approved Rule Sets
        ↓
Engineering Documentation
        ↓
Implementation
        ↓
Tests
        ↓
Generated Reports
```

When documents conflict, the higher authority governs. Implementation must always follow the highest applicable authority. This refines, rather than replaces, `CLAUDE.md`'s own Source-of-Truth Order, which additionally places the current founder instruction above every repository document. See `docs/00-governance/GOVERNANCE_CONSOLIDATION_G01.md`'s Cross-Reference Report for how each tier above maps to an actual file in this repository.

## 6. Observability Convention

*Documentation only — this section describes future architectural guidance. No logging code is implied or required by this section.*

Every future subsystem that logs or traces a request should preserve these identifiers where applicable: `request_id`, `session_id`, `user_id`, `slip_id`, `analysis_id`, `engine_version`, `rule_set_version`, `taxonomy_version`, `analysis_schema_version`, `normalization_version`, `created_at`, `analysed_at`.

## 7. Refactoring Policy

Refactoring is encouraged only when it:

- improves readability,
- improves maintainability,
- removes duplication,
- preserves behaviour,
- preserves deterministic outputs,
- preserves public contracts,
- passes full regression testing.

Engineering should never refactor simply because another design appears "cleaner."

## 8. Foundation Freeze

The following are baseline infrastructure as of the certifying commit `18d1c4c` (`docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md`):

- Risk Engine
- Rule Set integration
- Football Taxonomy
- Normalization pipeline
- ADR-007 layering
- Analysis Persistence boundary
- Analysis contracts (`SlipAnalysis`/`LegAnalysis`)
- Security/ownership boundaries
- Governance framework (SGOS)

These evolve only through: Product Office approval, an ADR, a Decision Log entry, regression validation, and a version increment where applicable.

**Note on Rule Set 2026.1 specifically:** this freeze means Engineering will not alter its mathematics unilaterally — it does not mean Product Office/Data Science's own approval decision has already been made. As of this document's date, `docs/00-governance/DECISION_LOG.md` still records Rule Set 2026.1 as "Proposed, ready for approval," not Accepted. Behavioural compatibility must be preserved unchanged until that decision lands.

## 9. Customer Experience Transition

The Foundation Phase is complete. Future engineering work shall prioritise customer experience, usability, accessibility, clarity, explainability, and trust while preserving the certified engineering foundation described above.
