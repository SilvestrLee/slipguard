# Office Constitution Template

**Status:** Active · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

Every constitution under `docs/offices/` inherits this structure. Fill in every section — an office constitution with an empty section is incomplete, not "not applicable" (if a section genuinely doesn't apply, state that explicitly and say why, rather than omitting it). Reference `OFFICE_OPERATING_SYSTEM.md`, `AUTHORITY_MODEL.md`, and `DECISION_ESCALATION_MODEL.md` rather than restating them — an office constitution's job is to say what's *specific* to that office.

---

## Identity
What this office is, in one or two sentences. Not a mission statement yet — just what kind of function it is (e.g. "the specialist discipline responsible for X").

## Mission
Why this office exists — the outcome it's accountable for producing across the life of the product, not a single sprint.

## Vision
What "this office is working well" looks like once SlipGuard is mature — the standard it holds itself to beyond the current milestone.

## Primary Question
The single question this office asks of every piece of work before approving it. One sentence. This is the fastest way for anyone (including another office) to understand what this office actually optimises for.

## Authority
What this office may decide *without* another office's approval, and where that authority comes from (`AUTHORITY_MODEL.md`'s Responsibility Matrix — cite the relevant rows, don't re-derive them).

## Responsibilities
What this office is expected to do as standing, ongoing work — not a list of past sprints, a list of durable functions.

## Inputs
What this office needs before it can act: from Product Office, from other offices, from existing documentation. If an input is currently missing (a real, named gap), say so.

## Outputs
What this office produces as a matter of course — documents, code, decisions, approvals.

## Deliverables
Concrete artefact types this office is expected to produce (e.g. "ADRs," "risk-engine test vectors," "Blade/Livewire implementations") — more specific than Outputs, closer to "what would appear in a handover's Expected Outputs section for this office."

## Decision Rights
The specific decisions this office may make autonomously, per `DECISION_ESCALATION_MODEL.md`'s "may decide independently" test. Be concrete — "implementation approach for an approved specification" is a decision right; "whether the specification is correct" is not, unless this *is* the office that owns specification correctness.

## Prohibited Actions
What this office must never do unilaterally, even under time pressure or when the answer seems obvious. This is the mirror image of Decision Rights and is equally important — most governance failures come from an office quietly doing something adjacent to its mandate, not from outright rule-breaking.

## Working Principles
How this office approaches its own work — its version of `docs/00-governance/WORKING_PRINCIPLES.md`, scoped to this office's discipline. May reference existing standards (e.g. Engineering Office referencing `docs/06-engineering/ENGINEERING_STANDARDS.md`) instead of restating them.

## Quality Standards
What "good work" looks like for this office, specifically — the standard its own internal review (`PLAYBOOK_TEMPLATE.md`) checks against before submission.

## Escalation Rules
This office's specific instances of `DECISION_ESCALATION_MODEL.md` — named scenarios where this office, specifically, is expected to stop (may simply reference the general model plus one or two office-specific examples, not reinvent it).

## Handover Rules
Anything specific to how this office issues or receives handovers beyond `HANDOVER_STANDARD.md`'s general format (most offices will have little or nothing to add here — that's expected, not a gap).

## Measures of Success
How this office's own effectiveness is judged — concrete enough to be evaluated, not aspirational language.

## Relationship With Other Offices
For each office this one regularly interacts with: what that relationship looks like in practice (who hands off to whom, who reviews whom) — a short table is usually clearest.

## Permanent References
Every existing durable document this office's work depends on or must stay consistent with — named specifically.
