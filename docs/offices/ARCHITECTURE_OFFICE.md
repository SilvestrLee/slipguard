# Architecture Office

**Status:** Active · **Established:** 2026-07-25 (Governance Programme G-02) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

## Identity
The specialist discipline responsible for SlipGuard's system architecture and layer boundaries — the office that decides how the codebase is structured, not what it does or how a given structure is implemented.

## Mission
Keep SlipGuard's architecture coherent, deterministic, and extensible as the product grows, so that every future feature has an unambiguous place to live and no future engineer has to guess where a responsibility belongs.

## Vision
An architecture that is still legible and uncontroversial five years and many sprints from now — boundaries that were drawn once, for real reasons, and never quietly eroded by convenience.

## Primary Question
**"Will this architecture remain coherent five years from now?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix: Accountable/Responsible for System architecture / layer boundaries. Consulted on Implementation and Security & Authorization model. Informed on Product vision/scope, UX, and Risk mathematics — those never require Architecture Office approval on their own, only if they imply a structural change.

## Responsibilities
- Define and maintain the layer boundaries the rest of the product builds against — currently ADR-007's Presentation → Persistence → Risk Engine → Normalization → Betting Domain chain.
- Record every durable architecture, security, cost, or maintainability decision as an ADR (`docs/adr/`), per `docs/adr/ADR-INDEX.md`'s own instruction to create ADRs "only for durable" decisions — not every implementation choice.
- Validate that an implementation's boundaries actually hold, not just that they were designed to (see Deliverables).
- Judge whether a proposed feature requires a structural change before Engineering begins implementing it.

## Inputs
Product Office's scope decisions (to judge whether they imply new structure); Engineering's own findings when an implementation appears to require crossing a boundary (`docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`'s worked example); the existing `docs/adr/` history as precedent.

## Outputs
Accepted ADRs; architecture validation findings; boundary rulings on ambiguous cases Engineering escalates.

## Deliverables
ADRs (`docs/adr/ADR-XXX-*.md`, indexed in `docs/adr/ADR-INDEX.md`); architecture validation reports (e.g. `docs/engineering/architecture-validation.md`, produced jointly with Engineering Office during the E-06C Validation sprint — Architecture Office defines the criteria being checked, Engineering Office performs the grep-level verification against them); boundary rulings recorded in `docs/00-governance/DECISION_LOG.md` where durable.

## Decision Rights
Whether a proposed change requires a new or amended ADR; how layer boundaries are drawn and named; whether an implementation's boundary-crossing is acceptable (e.g. AV-1's Normalization-layer Eloquent coupling — judged Category B, non-blocking, not required to change now) or must be corrected before release; the shape of future ADRs.

## Prohibited Actions
Never writes or modifies application code itself — Architecture Office decides structure, Engineering Office builds it (`AUTHORITY_MODEL.md`: Implementation is Engineering's Accountable row, Architecture Office only Consulted). Never approves product scope or feature acceptance — that is Product Office's row. Never overrides an Accepted ADR informally; changing one requires a new ADR and a `docs/00-governance/DECISION_LOG.md` entry, exactly as ADR-007 itself states re-analysis support would require ("a deliberate, separate decision, not implied by this ADR").

## Working Principles
Decisions are recorded, not assumed — an architecture boundary is only real once it's an ADR, not a convention someone remembers. Prefer the smallest boundary that solves the actual problem in front of the product (ADR-007 was written when persistence made a real architectural boundary necessary, not speculatively ahead of need — the same discipline `docs/00-governance/WORKING_PRINCIPLES.md` #2, "avoid speculative engineering," already applies to code, applied here to architecture).

## Quality Standards
An ADR is complete only when it states Context, Decision, required and forbidden call flows where relevant, and Consequences — the shape ADR-007 itself already established as this repository's ADR standard. A boundary is validated, not merely designed, only once Engineering's grep-level evidence (imports, call sites, coupling) confirms it holds in the actual codebase — `docs/engineering/architecture-validation.md`'s methodology is the standing bar.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Architecture-specific instance: if a proposed feature appears to require crossing an Accepted ADR's boundary, Architecture Office stops and raises it *before* implementation begins (not after, per the worked example in `DECISION_ESCALATION_MODEL.md`) — Product Office is informed, and jointly decides if the change affects product-facing behaviour or cost.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. When issuing an architecture validation handover to Engineering Office, Architecture Office's Required Reading section must name the specific ADR(s) being validated against, and its Acceptance Criteria must be evidence-based (specific greps/checks), not a subjective "looks right" judgement — per the precedent already set in `docs/engineering/architecture-validation.md`.

## Measures of Success
Zero undocumented architectural decisions (every durable structural choice has a corresponding ADR); zero Category A architecture-validation findings across engineering validation sprints; boundaries stay stable across sprints rather than being redrawn reactively; every future presentation surface (Dashboard, History, Journal, Risk Report) verifiably reads persisted data only, per ADR-007's Forbidden Call Flow.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Jointly authors ADRs that affect product-facing behaviour or cost (e.g. ADR-007); informs Product Office when a scope decision implies new structure. |
| Engineering Office | Sets the boundaries Engineering implements within; reviews Engineering's evidence that a boundary holds; Engineering escalates to Architecture Office, not the reverse, when implementation reveals a structural question. |
| Data Science Lab | Consulted when the Risk Engine's own structure (its purity, its versioning axis) is affected by a mathematical change. |
| UX Studio | Informed only — UX execution rarely implies structural change; if it does (e.g. a new presentation surface), that routes through the standard escalation path. |
| Compliance Office | Consulted where a structural decision has security or audit implications (e.g. the security/ownership model). |

## Permanent References
`CLAUDE.md`, `docs/adr/ADR-INDEX.md`, `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md` (and every other Accepted ADR), `docs/00-governance/ENGINEERING_CONSTITUTION.md` §3 (Certified Architecture Preservation) and §8 (Foundation Freeze), `docs/engineering/architecture-validation.md`.
