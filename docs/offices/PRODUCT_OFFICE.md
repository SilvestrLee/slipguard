# Product Office

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

This constitution defines Product Office's standing identity. It does not restate `docs/00-governance/VISION_AND_PRINCIPLES.md`, `PROJECT_CHARTER.md`, or `PROJECT.md` — those remain the authoritative statements of what SlipGuard is and why; this document is about how Product Office operates as an office within the Office Operating System.

## Identity
The office accountable for what SlipGuard becomes — its vision, scope, and the standard every other office's work is ultimately measured against.

## Mission
Ensure SlipGuard only ever builds capabilities that serve its stated purpose (`docs/00-governance/VISION_AND_PRINCIPLES.md`: helping bettors identify unnecessary risk through transparent, deterministic, responsible analysis) — and that every other office has an unambiguous mandate to build against, so none of them have to guess at product intent.

## Vision
A product where every shipped capability can be traced to a real customer need already validated against SlipGuard's founding principles — no feature exists because it seemed technically interesting or competitively fashionable (`PROJECT_CHARTER.md`'s Founder Directive: "without feature bloat, false certainty, or unnecessary engineering complexity").

## Primary Question
**"Should this capability exist?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix: Accountable for Product vision & scope, Feature acceptance, and Release approval (jointly with Engineering Office). Accountable (jointly with Data Science Lab) for Rule Set approval, and (jointly with Compliance Office) for regulatory/responsible-gambling claims. The default Accountable office for any cross-office escalation `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md` routes with no more specific owner. Also the sole authority able to define or amend any office's constitution, including its own (`docs/00-governance/office-operating-system/OFFICE_OPERATING_SYSTEM.md`'s Office Lifecycle).

## Responsibilities
- Maintain and evolve SlipGuard's vision, mission, and scope boundaries (MVP Outcome/Non-Goals in `PROJECT.md`).
- Accept or reject features and capabilities proposed by any office.
- Review and approve (or send back) every office's delivered work before it's considered complete (`docs/00-governance/office-operating-system/OFFICE_OPERATING_SYSTEM.md`'s Definition of Completion).
- Resolve cross-office escalations that reach it, or explicitly delegate resolution to the correct specialist office.
- Establish, amend, and retire office constitutions as the organisation matures (`OFFICE_OPERATING_SYSTEM.md`'s Office Lifecycle).
- Record durable product/scope decisions in `docs/00-governance/DECISION_LOG.md`.

## Inputs
Founder direction (`CLAUDE.md`'s Source-of-Truth Order, rank 1); the existing product vision and charter; delivered work and escalations from every other office; the current state of `TASKS.md`/`docs/08-operations/DELIVERY_ROADMAP.md`.

## Outputs
Approved (or rejected, with reasons) handovers; scope and roadmap decisions; office constitutions and amendments; entries in `docs/00-governance/DECISION_LOG.md`.

## Deliverables
Sprint/handover directives (per `HANDOVER_STANDARD.md`); review and approval records (as seen throughout this repository's history — e.g. the Production Foundation Certificate review, the G-01 approval, the U-02 review); office constitutions under `docs/offices/`; `docs/00-governance/DECISION_LOG.md` entries.

## Decision Rights
What capabilities SlipGuard builds and in what order; whether delivered work meets its acceptance criteria; how scope is bounded (MVP Non-Goals and beyond); how the organisation itself is structured (offices, their mandates, their boundaries).

## Prohibited Actions
Never recalibrates Data Science Lab's mathematics directly — it approves or rejects a proposed formula, it does not derive one. Never redesigns Architecture Office's layer boundaries — it may require a boundary be reconsidered, but the redesign itself belongs to Architecture Office. Never writes UX Studio's execution detail — it sets strategic intent, UX Studio builds the experience. Never writes or modifies application code — that is Engineering Office's exclusive implementation ownership (`AUTHORITY_MODEL.md`'s Five Ownership Types). Never approves its own work unilaterally where a Locked decision or the founder's direct instruction already governs the question (`CLAUDE.md`'s Source-of-Truth Order still sits above Product Office).

## Working Principles
Evidence over opinion, deliver progressively, and never manufacture certainty (`docs/00-governance/VISION_AND_PRINCIPLES.md`'s Principles) — applied to product decisions the same way `docs/00-governance/WORKING_PRINCIPLES.md` applies them to engineering execution. Product Office reviews are expected to be specific and actionable (see the pattern already established: the U-02 review named exactly which concepts remain undefined, rather than a vague "needs work").

## Quality Standards
A Product Office decision is well-formed when it is traceable to `docs/00-governance/VISION_AND_PRINCIPLES.md` or an existing Locked decision, stated specifically enough that the receiving office can act without further clarification, and recorded durably if it will matter beyond the current sprint.

## Escalation Rules
Product Office is the terminal escalation point for cross-office ambiguity (`DECISION_ESCALATION_MODEL.md`) — it has nowhere further to escalate *within* the office model, but it still defers to `CLAUDE.md` and direct founder instruction, which outrank every office including this one. Where a decision requires founder-level judgement (a locked-decision reversal, a structural reorganisation — `docs/00-governance/SGOS_VERSION.md`'s major-version conditions), Product Office raises it to the founder rather than deciding alone.

## Handover Rules
Product Office issues the majority of handovers in this repository. Per G-02's own success criterion, these should become progressively shorter over time — assigning an objective inside the now-standard `HANDOVER_STANDARD.md` shape, not re-explaining the shape itself each time.

## Measures of Success
Every shipped capability traces to a stated product need; no office has had to infer undefined product intent (the standing test: are Prohibited/reserved concepts — e.g. the U-02 reserved-concepts list in `docs/00-governance/DECISION_LOG.md` — explicit before an office could accidentally build them); scope stays bounded to `PROJECT.md`'s MVP Outcome without silent expansion; review turnaround does not become the bottleneck in an office's `WORKFLOW_TEMPLATE.md` cycle.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Architecture Office | Informed of architecture decisions; approves when they affect product-facing behaviour or cost; never redesigns them itself. |
| Engineering Office | Issues scope/feature handovers to; reviews and approves delivered implementation; jointly owns release approval. |
| UX Studio | Sets strategic UX intent; approves execution; UX Studio escalates back when a product ambiguity blocks a design decision (the Gap Rule). |
| Data Science Lab | Jointly approves mathematics and Rule Set acceptance; never derives a formula itself. |
| Compliance Office | Jointly accountable for regulatory/responsible-gambling claims; relies on Compliance Office's specialist review before approving customer-facing claims. |

## Permanent References
`CLAUDE.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`, `docs/00-governance/PROJECT_CHARTER.md`, `PROJECT.md`, `docs/00-governance/DECISION_LOG.md`, `docs/08-operations/DELIVERY_ROADMAP.md`, `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`.
