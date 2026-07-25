# Office Workflow Template

**Status:** Active · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

The consistent lifecycle every office follows for a single piece of assigned work — distinct from `OFFICE_OPERATING_SYSTEM.md`'s Office Lifecycle, which governs the office's own standing existence, not one assignment.

```
Receive Assignment
        ↓
Review Constitution
        ↓
Review Inputs
        ↓
Execute Responsibilities
        ↓
Validate Internally
        ↓
Prepare Deliverables
        ↓
Submit to Owning Office
        ↓
Await Approval
        ↓
Archive Work
```

## Stage Definitions

**Receive Assignment** — a handover arrives in `HANDOVER_STANDARD.md`'s format. If any required section is missing, the office requests it before proceeding (`OFFICE_OPERATING_SYSTEM.md`: "ask, not guess").

**Review Constitution** — the office re-confirms the assignment falls inside its own Decision Rights and doesn't require a Prohibited Action. This is a genuine check, not a formality — most escalations are caught here, before any work begins.

**Review Inputs** — the office reads everything the handover's Required Reading names, plus its own Permanent References (`OFFICE_TEMPLATE.md`).

**Execute Responsibilities** — the actual work, governed by the office's own playbook (`PLAYBOOK_TEMPLATE.md`'s Thinking Process). Any ambiguity discovered here triggers `DECISION_ESCALATION_MODEL.md` immediately, not at submission time.

**Validate Internally** — the office's own Review Process and Quality Review (`PLAYBOOK_TEMPLATE.md`), performed before anything is submitted.

**Prepare Deliverables** — the work is packaged to match the handover's Expected Outputs exactly (location, format, naming) — not "equivalent" outputs the receiving side has to reinterpret.

**Submit to Owning Office** — delivered to whichever office is Accountable for this decision type (`AUTHORITY_MODEL.md`), per `PLAYBOOK_TEMPLATE.md`'s Submission Standards.

**Await Approval** — the work is not complete until the owning office has responded. The producing office does not begin dependent follow-on work during this stage on the assumption of approval, unless the handover explicitly authorised parallel work.

**Archive Work** — once approved, durable decisions are recorded (`docs/00-governance/DECISION_LOG.md` where applicable) and the work becomes part of the permanent record — `TASKS.md`/`CHANGELOG.md` for engineering deliverables, the equivalent standing record for other offices' deliverables.

## Rejection Path

If the owning office does not approve: the producing office receives specific feedback (not a bare rejection), returns to **Execute Responsibilities** with that feedback as a new input, and the cycle repeats from there — not from the beginning, and not by silently reinterpreting the original assignment more broadly (`PLAYBOOK_TEMPLATE.md`'s Approval Behaviour).
