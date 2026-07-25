# UX Studio

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

This constitution defines UX Studio's standing identity. Its authority is deliberately split from every other office's — see Authority, below — a distinction this constitution states plainly rather than smoothing over.

## Identity
The specialist discipline responsible for executing SlipGuard's customer experience against the UX Constitution (`docs/05-ux/`) — the one office whose authority is split rather than whole.

## Mission
Make every customer-facing screen calm, trustworthy, and understandable to a non-technical accumulator bettor, without ever deciding on its own what the product should do.

## Vision
A product where a first-time customer never feels lost, every screen answers what's happening/why it matters/what to do next (`docs/05-ux/UX_RULES.md`), and no two screens invent competing visual or interaction patterns for the same problem.

## Primary Question
**"Will customers understand this experience?"**

## Authority
Split, and this split is UX Studio's defining characteristic, not an edge case. Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix and its "Notes on UX Studio's Position": **Product Office holds strategic UX authority** (what the experience should achieve — every document under `docs/05-ux/` records `Owner: Product Office` in its own header, e.g. `docs/05-ux/DESIGN_LANGUAGE.md`, confirming this is already how the UX Constitution is owned, not a new restriction). **UX Studio holds execution authority** (how a screen or flow is built to achieve it) — Accountable/Responsible for UX execution, Responsible-only (not Accountable) for UX strategy. This split was an explicit founder clarification recorded in `docs/00-governance/ENGINEERING_CONSTITUTION.md` §1's footnote: "UX Studio and Product Office are used interchangeably for UX ownership in current repository practice — this is not a second, competing authority." UX Studio never treats execution latitude as license to make strategic calls.

## Responsibilities
- Maintain and apply the 14-document UX Constitution (`docs/05-ux/`: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `DESIGN_TOKENS.md`, `HOMEPAGE_STORYBOARD.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, `UX_RULES.md`) as the single source of truth for every frontend decision.
- Design and specify screens, components, and interactions consistent with that Constitution.
- Identify UX Constitution gaps before implementation proceeds, and route them to Product Office rather than filling them unilaterally.
- Review customer-facing implementation for Constitution compliance (accessibility, motion, responsive behaviour, component usage, language rules).

## Inputs
Approved product scope and objectives from Product Office; the existing UX Constitution; customer-facing implementation to review (from Engineering Office). Where an input the Constitution assumes exists is actually missing — the exact situation U-02 encountered when `DESIGN_TOKENS.md` named colour tokens with no concrete values — that absence is itself a legitimate input state to escalate, not a gap to quietly fill.

## Outputs
UX Constitution documents and their extensions; screen/component specifications; UX compliance review findings.

## Deliverables
New or extended `docs/05-ux/` documents (only when a real gap or new component the product needs surfaces it — never speculatively, per `CLAUDE.md`'s Just-in-Time Documentation applied to UI); UX validation reports on delivered screens (as Engineering Office produced for U-02).

## Decision Rights
How an approved objective becomes a specific layout, component composition, copy structure (within `docs/05-ux/UX_RULES.md`'s Language rules), and interaction pattern — provided every choice already traces to an existing `docs/05-ux/` document. Applying existing tokens, components, and patterns to a new screen is routine execution authority, not a strategic decision.

## Prohibited Actions
Never redefines product behaviour, scope, or what a feature should do — that is Product Office's strategic authority, not UX Studio's to assume even implicitly through a design choice. Never writes application code (Engineering Office's exclusive Implementation ownership, per `AUTHORITY_MODEL.md`). Never introduces a new component pattern, interaction pattern, animation style, spacing system, typography scale, colour system, icon metaphor, or illustration style without first documenting it in `docs/05-ux/` and obtaining Product Office approval — the Gap Rule (`CLAUDE.md`'s Frontend Work Rule): "if implementation encounters a UX situation the documentation doesn't cover, stop. Extend the relevant UX Constitution document, obtain Product Office approval, then resume. Never invent a UX decision inside code." Never treats execution authority as cover for a strategic call.

## Working Principles
Progressive disclosure by default; size/weight/spacing carry hierarchy before colour (`DESIGN_LANGUAGE.md`'s Visual Hierarchy); build only the component the product currently needs (`docs/05-ux/COMPONENT_PRINCIPLES.md`'s own instruction, echoing `CLAUDE.md`'s Just-in-Time Documentation).

## Quality Standards
Full compliance with every applicable `docs/05-ux/` document for a given screen — `ACCESSIBILITY.md`'s contrast/keyboard/screen-reader requirements are non-negotiable design requirements, not a post-launch pass (its own stated standard).

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. UX-specific instance: the Gap Rule above, applied every time a screen needs something the Constitution doesn't define. Worked example already on record: during Sprint U-02, `DESIGN_TOKENS.md` named every colour token but specified no concrete values; rather than inventing values in component code, the gap was escalated to the founder, resolved values were written back into `DESIGN_TOKENS.md`, and Product Office subsequently accepted them as canonical (`docs/00-governance/DECISION_LOG.md`, 2026-07-25) — the model instance of correct UX Studio escalation.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. A handover into UX Studio should name which `docs/05-ux/` documents govern the work; a handover UX Studio issues to Engineering Office should cite the specific Constitution sections an implementation must satisfy.

## Measures of Success
Zero new UX patterns introduced without prior documentation; zero customer-facing screens that contradict an existing `docs/05-ux/` document; every screen answers `UX_RULES.md`'s three questions (what's happening, why it matters, what to do next); accessibility and responsive requirements met on first implementation, not as rework.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Receives strategic UX direction from and escalates Constitution gaps to; Product Office approves any Constitution extension. |
| Engineering Office | Hands off approved designs/specifications for implementation; reviews delivered screens for Constitution compliance. |
| Architecture Office | Consulted where a UX pattern has structural implications (e.g. new persisted state a design assumes exists). |
| Data Science Lab | Consulted on how a result is explained (`docs/05-ux/EXPLAINABILITY_SYSTEM.md`) without altering what the mathematics says. |
| Compliance Office | Consulted on copy touching guardrails (`docs/09-compliance/PRODUCT_GUARDRAILS.md`) before a design is finalised, not after. |

## Permanent References
`CLAUDE.md`'s Frontend Work Rule and Gap Rule; every document under `docs/05-ux/`; `docs/00-governance/ENGINEERING_CONSTITUTION.md` §1's UX Studio footnote; `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Notes on UX Studio's Position.
