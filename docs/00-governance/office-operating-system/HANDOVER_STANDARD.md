# Handover Standard

**Status:** Active · **Effective:** 2026-07-25 · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

Every piece of work assigned to a SlipGuard office — by Product Office or by a peer office (`OFFICE_OPERATING_SYSTEM.md`'s Relationship Between Offices) — follows this single format. A handover that omits a required section is incomplete; the receiving office's correct first action is to ask for the missing section, not to infer it.

This standard exists so that, per G-02's own success criterion, future handovers can be short — they assign an objective inside an already-understood shape, rather than re-explaining what a handover is each time.

## Required Sections

### Purpose
One or two sentences: what this handover achieves and why it matters now. Not a restatement of the receiving office's whole mission — just this piece of work's reason for existing.

### Authority
Which office issued this handover, and under what authority (usually self-evident — Product Office issuing to any office, or a peer office issuing within its own Decision Rights per `AUTHORITY_MODEL.md`). Stated explicitly so the receiving office can verify the handover is legitimate before starting.

### Inputs
Everything the receiving office needs to begin: prior decisions, existing artefacts, data, constraints. If an input doesn't exist yet (e.g. "the UX Constitution doesn't specify a value for X"), say so — that's a legitimate input state, not an omission to paper over.

### Deliverables
The concrete things the receiving office must produce. Specific enough that "did we deliver this" is a factual question, not a judgement call.

### Out of Scope
Explicitly excluded work — especially work adjacent enough to the Deliverables that a reasonable office might otherwise assume it's included. This section is not optional padding: most of the scope disputes this repository has avoided so far were avoided because scope boundaries were stated, not implied (see, e.g., every Engineering sprint's own "Explicitly Forbidden" / "Not Done" sections).

### Acceptance Criteria
The specific, checkable conditions under which the Accountable office (`AUTHORITY_MODEL.md`) will approve the deliverables. Written so the receiving office can self-check before submitting, not discovered only at review time.

### Required Reading
Every existing document the receiving office must read before starting — named specifically, not "read the relevant docs." Mirrors the discipline `CLAUDE.md`'s own Required Reading section already applies at the whole-project level, scoped down to this one handover.

### Escalation
What this specific handover expects the receiving office to do if it hits an ambiguity `DECISION_ESCALATION_MODEL.md`'s general rule would apply to — usually just a pointer to that document, but a handover may name a specific contact/office if the ambiguity is foreseeable (e.g. "if a UX pattern is missing, escalate to Product Office, not UX Studio's own judgement, since UX Studio's mandate is execution, not strategy").

### Expected Outputs
The concrete artefacts (files, reports, code, documents) the handover expects to exist afterward, and where they should live. Distinct from Deliverables (the *what*) — this is the *where and in what form*.

### Completion Conditions
The specific, objective conditions under which this handover is considered closed — normally: Acceptance Criteria met, Expected Outputs exist in the stated location, and the Accountable office has recorded approval (`OFFICE_OPERATING_SYSTEM.md`'s Definition of Completion). A handover may add conditions specific to its own deliverables (e.g. "and the full test suite still passes").

## What This Standard Deliberately Does Not Cover

- **How** the receiving office does the work — that's `PLAYBOOK_TEMPLATE.md`, owned by the receiving office itself.
- **The office's standing mission or boundaries** — that's the office's own constitution (`OFFICE_TEMPLATE.md`), not re-derived per handover.
- **Escalation mechanics in general** — fully defined once in `DECISION_ESCALATION_MODEL.md`; a handover only needs to reference it, per the Escalation section above.

## Retroactive Note

Every sprint directive this repository has received to date (E-06C Validation, the Production Foundation Certificate, G-01, U-02) already contained the substance of most of these sections, just under different headings (e.g. "Scope" for Out of Scope, "Definition of Done" for Completion Conditions, "Deliverables" verbatim). This standard formalises a pattern that was already working, per `OFFICE_OPERATING_SYSTEM.md`'s Governance Philosophy — it does not retroactively invalidate any prior handover's shape.
