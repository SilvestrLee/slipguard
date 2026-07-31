> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# R-01.6 — Platform Design Principles: Duplication Analysis

```text
Identifier:            R-01.6
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic
Status:                Exploratory — not recommended for adoption as new governance (see below)
Owner:                 Architecture Office (role, not a standing individual)
Product Office Decision: None
Repository Status:     None
```

## Finding

Nine of the ten principles proposed in this package ("U-19.1 — Cross-Platform Design Principles") are near-verbatim restatements of governance that's already real, already Approved, and already locked: `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` (Evidence Before Theatre, Deliberate Omission, Product Before Decoration, Zero New Components, Human Continuity), `ADR-012` (customer agency, human judgement), `ADR-003`/`UX_RULES.md` (explain before conclude), `DESIGN_LANGUAGE.md` (cognitive load, white space), and `WORKING_PRINCIPLES.md` (reuse before reinvention). Full mapping given in conversation; not reproduced here to avoid a third copy of the same content.

**Recommendation: do not adopt as new governance.** This would create near-total duplication of principles this repository's own working discipline explicitly warns against layering twice. If a single cross-referencing index (pointing at, not restating, the real sources) would be useful, that's a legitimate small artifact — distinct from re-declaring the content itself as new.

## The one real defect

Principle 7 ("Design for Continuity") adds language beyond what `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md`'s real "Human Continuity" principle says: *"encourage continuous engagement through clear progression."* "Engagement" as a goal directly conflicts with this repository's own real precedent — `PO-U02-DASH-002` deliberately dropped an engagement-shaped dashboard metric because it "answered no decision question," and `ADR-012` Principle 8 ("risk-awareness over excitement") plus the existing anti-gamified-streak rule in `COMPONENT_PRINCIPLES.md` both point away from optimizing for return frequency. This isn't a duplication issue — it's a real, substantive risk that would need correcting before any version of this principle could be adopted, even setting the duplication finding aside entirely.
