# The Office Operating System (OOS)

**Status:** Active · **Effective:** 2026-07-25 · **Authority:** Product Office · **Applies To:** every SlipGuard office, present and future

This is the master governance document for SlipGuard's organisational model — the shared operating philosophy every office constitution under `docs/offices/` inherits rather than restates. Where an office constitution and this document appear to conflict, this document governs (`AUTHORITY_MODEL.md`'s Decision Precedence explains why).

This document does not restate what already exists elsewhere. It orchestrates:

- `CLAUDE.md` — the highest-authority document in the repository; the OOS operates *underneath* it, never above it.
- `docs/00-governance/VISION_AND_PRINCIPLES.md` / `PROJECT_CHARTER.md` — why SlipGuard exists at all.
- `docs/00-governance/WORKING_PRINCIPLES.md` — how day-to-day engineering execution behaves (unchanged, still Engineering's own operating habits, not an authority model).
- `docs/00-governance/ENGINEERING_CONSTITUTION.md` — Engineering's existing behaviour-change discipline, now the specific instance of this document's general escalation model (see `DECISION_ESCALATION_MODEL.md`).

## Purpose

Until this milestone, offices received responsibility through individual sprint directives — correct for a Foundation phase where architecture, mathematics, and UX were each defined once, sequentially. As multiple offices now operate concurrently against a certified foundation, that approach stops scaling: without a shared model, every handover has to re-establish who decides what, which is exactly the kind of repeated instruction the Product Office's G-02 directive exists to eliminate.

The OOS makes every office's mission, authority, boundaries, and escalation behaviour a permanent, readable repository asset. A future handover assigns an objective; it no longer needs to define a department.

## Governance Philosophy

Three commitments, consistent with every governance document that precedes this one:

1. **Codify what already works, don't invent new process.** Every mechanism in this framework (stop/document/escalate, one accountable decision-owner, explicit prohibited actions) is already how SlipGuard's offices have operated since the Foundation phase — see `docs/00-governance/office-operating-system/README.md`'s provenance notes and `ENGINEERING_CONSTITUTION.md`'s own audit trail. The OOS generalises a working pattern; it does not introduce untested bureaucracy.
2. **One accountable owner per decision, always.** No decision is jointly ownerless. Where two offices both have a stake, `AUTHORITY_MODEL.md`'s Responsibility Matrix names exactly one Accountable office, with others Responsible, Consulted, or Informed.
3. **Autonomy within boundaries, escalation at the edges.** An office decides freely inside its own Decision Rights (defined in its own constitution). The instant a decision would require exceeding those rights, the office stops and escalates (`DECISION_ESCALATION_MODEL.md`) rather than inferring an answer — this is the single most important behaviour in the entire model, and the one every prior sprint in this repository has already demonstrated (RF-003A, the E-06C Validation sprint's Category D discipline, U-02's design-token and Progress-metric gaps).

## Office Lifecycle

An office (not a single sprint's work — the standing department itself) moves through:

1. **Establishment** — Product Office defines the office's constitution using `OFFICE_TEMPLATE.md`, published under `docs/offices/`.
2. **Operation** — the office receives handovers (`HANDOVER_STANDARD.md`), executes its `WORKFLOW_TEMPLATE.md` lifecycle per assignment, and operates inside its Decision Rights.
3. **Evolution** — an office's constitution may be amended only by the authority that established it (Product Office), following the same escalation discipline any other governance change would (`DECISION_ESCALATION_MODEL.md`; recorded in `docs/00-governance/DECISION_LOG.md`).
4. **Retirement** — if an office's responsibilities are absorbed elsewhere, its constitution is archived (per `SGOS_VERSION.md`'s "superseded material is archived, not deleted" philosophy), not silently removed.

This document governs stage 1, 3, and 4 (the office's existence). `WORKFLOW_TEMPLATE.md` governs stage 2 at the level of a single assignment.

## How Work Enters an Office

Every piece of work an office receives arrives as a **Handover** in the single format defined by `HANDOVER_STANDARD.md` — Purpose, Authority, Inputs, Deliverables, Out of Scope, Acceptance Criteria, Required Reading, Escalation, Expected Outputs, Completion Conditions. An office never infers scope from an informal request; if a handover is ambiguous or incomplete against that standard, the receiving office's first action is to ask, not to guess (this mirrors, at the organisational level, the same "stop and ask" behaviour Engineering has already applied to UX and product ambiguity throughout this project).

## How Work Leaves an Office

An office's output is complete only when:

1. It satisfies the handover's stated Acceptance Criteria and Completion Conditions.
2. It has been internally validated per the office's own `PLAYBOOK_TEMPLATE.md`-derived quality process.
3. It has been submitted to the owning office (usually, but not always, Product Office — see `AUTHORITY_MODEL.md`) and formally approved, with the approval recorded (`docs/00-governance/DECISION_LOG.md` for durable decisions, or the relevant office's own record for routine work).

Work is never considered "done" by the producing office's own assessment alone — approval by the Accountable office (`AUTHORITY_MODEL.md`) is what closes a handover, exactly as every sprint in this repository's history has already required Product Office review before a milestone is marked delivered.

## Relationship With Product Office

Product Office sits at the top of the office hierarchy for scope, behaviour, and release decisions, but not above `CLAUDE.md` or the founder — `CLAUDE.md`'s Source-of-Truth Order ("Current founder instruction" ranks above `CLAUDE.md` itself, which ranks above every other document) is unchanged and unaffected by this framework. Product Office:

- Defines and amends every office's constitution.
- Is the default Accountable party for cross-office and product-behaviour decisions (`AUTHORITY_MODEL.md`).
- Is the terminal escalation point for any ambiguity no single office can resolve alone (`DECISION_ESCALATION_MODEL.md`).
- Approves release (jointly with Engineering, per the Responsibility Matrix).

Product Office does not thereby gain the authority to reinterpret another office's specialist judgement (e.g. it cannot itself recalibrate Data Science Lab's mathematics, or redesign Architecture Office's layer boundaries) — it can only request that the owning office reconsider, per that office's own Decision Rights.

## Relationship Between Offices

Offices collaborate directly for routine, in-boundary work (e.g. Engineering asking Architecture Office to confirm a boundary reading) without needing to route every exchange through Product Office. Two rules bound this:

- **No office may expand its own authority by agreement with another office.** Two offices cannot jointly decide something that individually neither owns — that still escalates to whichever office (usually Product Office) is Accountable per `AUTHORITY_MODEL.md`.
- **Peer-to-peer handovers still use `HANDOVER_STANDARD.md`.** The format is not reserved for Product-Office-issued work; any office issuing work to another follows the same standard, so a future handover never needs to explain its own shape.

## Ownership Principles

- **Authority is delegated, never assumed.** Every office's authority exists because Product Office's founding constitution for that office grants it (`OFFICE_TEMPLATE.md`'s Authority section) — no office may act outside its granted authority on the theory that "someone has to."
- **Specialist domains are inviolable by non-specialists.** Only Data Science Lab may decide mathematical correctness; only Architecture Office may decide structural soundness; only UX Studio may decide experiential execution (within Product Office's strategic ownership — see `docs/offices/UX_STUDIO.md`); only Compliance Office may decide regulatory defensibility. This is not a courtesy; it is how SlipGuard has avoided inventing product behaviour, mathematics, or architecture inside implementation throughout the Foundation phase, and the OOS makes it structural rather than incidental.

## Authority Inheritance

`CLAUDE.md` → Product Office → each office's constitution → that office's day-to-day decisions. No office constitution may grant itself authority `CLAUDE.md` or Product Office has not delegated to it. Where an office constitution is silent on a question, the answer is: escalate (`DECISION_ESCALATION_MODEL.md`), never infer.

## Definition of Autonomy

An office acts autonomously — without asking first — only for decisions falling entirely inside its own constitution's Decision Rights **and** not touching another office's Prohibited Actions. The moment either condition fails, autonomy ends and escalation begins.

## Definition of Escalation

Fully defined in `DECISION_ESCALATION_MODEL.md` — not restated here. In one sentence: stop, document the ambiguity, route it to the Accountable office (`AUTHORITY_MODEL.md`), resume only once that office has decided.

## Definition of Completion

A handover is complete when, and only when: its Completion Conditions (`HANDOVER_STANDARD.md`) are met, its Acceptance Criteria are satisfied, and the owning office has recorded approval. Partial completion, or completion the producing office declares unilaterally, does not count — this mirrors every approval cycle already established in this repository (e.g. Production Foundation certification, U-02's Product Office review).

## Relationship to Existing Governance

This framework sits alongside, not above, the pre-existing governance stack:

| Existing document | Relationship to the OOS |
|---|---|
| `CLAUDE.md` | Unchanged, highest authority. The OOS operates inside its Source-of-Truth Order. |
| `docs/00-governance/DECISION_LOG.md` | Unchanged — still the durable record of product/scope decisions, including future office-constitution amendments. |
| `docs/adr/` | Unchanged — still the durable record of architecture decisions. |
| `docs/00-governance/ENGINEERING_CONSTITUTION.md` | Retained. Its Authority Matrix and Behaviour Change Policy are now the Engineering-specific application of `AUTHORITY_MODEL.md` and `DECISION_ESCALATION_MODEL.md` respectively — see that document's own updated cross-references. |
| `docs/00-governance/WORKING_PRINCIPLES.md` | Unchanged — Engineering's day-to-day execution habits, a `PLAYBOOK_TEMPLATE.md`-level concern, not an authority model. |

No governance already recorded elsewhere is duplicated inside the OOS — see `docs/00-governance/office-operating-system/README.md` for the full non-duplication check.
