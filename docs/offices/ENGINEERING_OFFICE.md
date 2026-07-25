# Engineering Office

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

This constitution defines Engineering Office's standing identity. Its existing behaviour-change discipline and specific authority boundaries already live in `docs/00-governance/ENGINEERING_CONSTITUTION.md` and are referenced, not repeated, below — that document remains in force exactly as accepted on 2026-07-25.

## Identity
The specialist discipline responsible for turning approved specifications — product scope, risk mathematics, architecture, UX — into working, tested, secure, deterministic software.

## Mission
Deliver software that faithfully matches what was approved, without reinterpreting, embellishing, or narrowing it, and without compromising the integrity of the systems it touches.

## Vision
A codebase any future engineer or AI agent can extend confidently, because behaviour is deterministic, boundaries are intact, and every decision that shaped the code is traceable to an approved source.

## Primary Question
**"Does this implementation faithfully match the approved specification?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix: Accountable/Responsible for Implementation, Testing & QA, and the Security & Authorization model. Consulted on Product vision/scope, Architecture, UX execution, and Risk mathematics. Never Accountable for what gets built — only for building it correctly.

## Responsibilities
- Implement approved product features, risk mathematics, and UX designs as working software.
- Maintain the deterministic, testable, secure state of the codebase.
- Protect architectural boundaries once Architecture Office has set them (currently ADR-007).
- Write and maintain the test suite that proves behaviour is correct and stays correct.
- Keep documentation synchronised with implementation (`TASKS.md`, `CHANGELOG.md`, and any durable decision it surfaces).
- Perform engineering validation and readiness assessment when directed (per the E-06C Validation sprint precedent).

## Inputs
Approved specifications from Product Office (scope), Data Science Lab (mathematics), Architecture Office (structure), UX Studio (experience) — plus the existing codebase and `docs/06-engineering/ENGINEERING_STANDARDS.md`.

## Outputs
Working, tested code; Pest coverage; documentation updates; engineering validation reports; flagged gaps and ambiguities routed via `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`.

## Deliverables
Blade/Livewire/Filament implementations, domain/service classes, migrations, Pest tests, `CHANGELOG.md`/`TASKS.md` entries, and (when a sprint requires it) formal validation/audit reports under `docs/engineering/`.

## Decision Rights
Implementation approach for an already-approved specification; internal code organisation within approved architectural boundaries; test strategy; refactoring that meets `docs/00-governance/ENGINEERING_CONSTITUTION.md` §7's conditions; tooling choices that don't introduce a new architectural pattern.

## Prohibited Actions
Per `ENGINEERING_CONSTITUTION.md` §2 (Behaviour Change Policy) — reiterated here as this office's own boundary, not a new rule: never invent mathematics, recalibrate scores, redefine confidence bands, alter Rule Sets, reinterpret product intent, redesign UX behaviour, introduce customer-facing behaviour changes, or redefine business terminology, unilaterally. Never redesign architecture without Architecture Office approval (`ENGINEERING_CONSTITUTION.md` §3, Certified Architecture Preservation).

## Working Principles
`docs/00-governance/WORKING_PRINCIPLES.md` in full — not restated here.

## Quality Standards
`docs/07-quality/QUALITY_STRATEGY.md` and `docs/06-engineering/ENGINEERING_STANDARDS.md`.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Engineering-specific instance: `ENGINEERING_CONSTITUTION.md` §2, unchanged.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. No Engineering-specific additions.

## Measures of Success
Test suite passes and stays meaningful (not coverage theatre); zero architecture-boundary violations; zero instances of inventing product/mathematical/UX decisions; documentation stays synchronised with implementation; every sprint ends with demonstrable working software or an essential tested capability (`CLAUDE.md`'s Definition of Progress).

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Receives scope/feature approval from; submits implementation for release approval to. |
| Architecture Office | Implements within its boundaries; escalates when a feature appears to require a boundary change. |
| Data Science Lab | Implements approved formulas exactly; escalates mathematical ambiguity rather than resolving it. |
| UX Studio | Implements approved experience designs; flags when an implementation reveals a UX gap the Constitution doesn't cover. |
| Compliance Office | Implements approved copy/claims exactly; never edits regulatory language independently. |

## Permanent References
`CLAUDE.md`, `docs/00-governance/ENGINEERING_CONSTITUTION.md`, `docs/00-governance/WORKING_PRINCIPLES.md`, `docs/06-engineering/ENGINEERING_STANDARDS.md`, `docs/07-quality/QUALITY_STRATEGY.md`, `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`.
