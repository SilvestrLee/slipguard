# Office Operating System — Index

**Established:** 2026-07-25, Governance Programme G-02, Product Office authority. Documentation-only sprint — no application code, Risk Engine, UI, persistence, database, migration, or test was touched to produce this framework.

## What This Directory Is

The permanent governance framework every SlipGuard office constitution (`docs/offices/`) inherits, replacing the prior pattern of offices receiving their mandate through individual sprint directives. See `OFFICE_OPERATING_SYSTEM.md`'s Purpose for the full rationale.

## Reading Order

1. **`OFFICE_OPERATING_SYSTEM.md`** — start here. The master document: philosophy, office lifecycle, how work enters/leaves an office, ownership principles.
2. **`AUTHORITY_MODEL.md`** — who decides what, the cross-office Responsibility Matrix, conflict resolution.
3. **`DECISION_ESCALATION_MODEL.md`** — when an office decides alone vs. stops and escalates.
4. **`HANDOVER_STANDARD.md`** — the one format every office-to-office handover follows.
5. **`OFFICE_TEMPLATE.md`**, **`PLAYBOOK_TEMPLATE.md`**, **`WORKFLOW_TEMPLATE.md`** — the three templates every office constitution under `docs/offices/` is built from (identity/authority, how work gets done, the per-assignment lifecycle, respectively).

## Provenance — This Codifies Existing Practice, Not New Bureaucracy

Every mechanism in this framework was already operating in this repository before G-02 formalised it. This table is the non-duplication/provenance check `OFFICE_OPERATING_SYSTEM.md` references:

| OOS concept | Prior instance in this repository |
|---|---|
| Stop / document / escalate | RF-003A (`docs/00-governance/DECISION_LOG.md`, 2026-07-24); the E-06C Validation sprint's Category D discipline; U-02's design-token and Progress-metric escalations |
| One Accountable owner per decision | Rule Set 2026.1's "Accepted only on explicit Product Office / Data Science sign-off"; `docs/00-governance/ENGINEERING_CONSTITUTION.md`'s pre-existing Authority Matrix |
| Handover-shaped directives | Every sprint directive to date (E-06C Validation, Production Foundation Certificate, G-01, U-02) already carried Purpose/Scope/Deliverables/Acceptance-Criteria-equivalent sections under different headings |
| Office-specific escalation | ADR-007's "if a feature appears to require violating the boundary, stop and raise the issue" — the Engineering-specific instance `DECISION_ESCALATION_MODEL.md` now generalises |
| Approval closes work, not self-assessment | Every milestone in `TASKS.md` marked "delivered, pending Product Office review" before being marked complete |
| Gap-driven escalation over invention | The UX Constitution's own Gap Rule (`CLAUDE.md`'s Frontend Work Rule); U-02's colour-token and Journal-preview decisions |

## What This Framework Does Not Change

- `CLAUDE.md`'s Source-of-Truth Order — unchanged, still supreme.
- Any Locked decision in `docs/00-governance/DECISION_LOG.md` — none reopened.
- `docs/00-governance/WORKING_PRINCIPLES.md` — still Engineering's own day-to-day habits, a `PLAYBOOK_TEMPLATE.md`-level concern.
- `docs/00-governance/ENGINEERING_CONSTITUTION.md` — retained; its Engineering-specific Authority Matrix and Behaviour Change Policy now explicitly reference `AUTHORITY_MODEL.md` and `DECISION_ESCALATION_MODEL.md` as the general models they instantiate, rather than existing as a second, parallel definition.
- Any ADR under `docs/adr/` — architecture decisions continue to be recorded there, per `AUTHORITY_MODEL.md`'s Responsibility Matrix (Architecture Office, Accountable).

## Office Constitutions

Live under `docs/offices/`, one file per office: `PRODUCT_OFFICE.md`, `ARCHITECTURE_OFFICE.md`, `ENGINEERING_OFFICE.md`, `UX_STUDIO.md`, `DATA_SCIENCE_LAB.md`, `COMPLIANCE_OFFICE.md`. Each inherits `OFFICE_TEMPLATE.md`'s structure and references, rather than repeats, the five documents above.
