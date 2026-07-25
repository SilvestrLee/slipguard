# Office Playbook Template

**Status:** Active · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

Where `OFFICE_TEMPLATE.md` defines what an office *is*, a playbook (referenced from that office's own constitution, and — where one already exists as a full standard, e.g. Engineering's `docs/06-engineering/ENGINEERING_STANDARDS.md` and `docs/07-quality/QUALITY_STRATEGY.md` — pointed to rather than duplicated) defines **how** it does its work day to day. An office is not required to write a separate, freestanding playbook document if its existing standards documents already cover these sections; in that case its constitution's Working Principles section should say so explicitly and cite them.

## Required Sections

### Thinking Process
How this office approaches an ambiguous or complex piece of work before producing anything — what it reads first, what questions it asks, how it decides whether it has enough information to proceed (and, per `DECISION_ESCALATION_MODEL.md`, when it doesn't).

### Review Process
How this office checks its own work before calling it done — self-review, not the Accountable office's separate approval. What specifically gets checked, and against what.

### Quality Review
The concrete, checkable standard applied during Review Process — distinct from a vague "make sure it's good." For Engineering, this is largely already `docs/07-quality/QUALITY_STRATEGY.md`; other offices should define their own equivalent.

### Internal Validation
How this office confirms its output is *correct*, not just complete — testing (Engineering), a second read against the mathematics (Data Science Lab), a check against every relevant UX Constitution document (UX Studio), a check against `docs/09-compliance/PRODUCT_GUARDRAILS.md` (Compliance Office), and so on.

### Submission Standards
What must be true of a deliverable before this office submits it as a handover response — the office's own pre-flight checklist, so the Accountable office's review finds nothing that should have been caught earlier.

### Documentation Expectations
What this office documents as a matter of course while doing the work (not after, as an afterthought) — e.g. Engineering records decisions inline in commit messages and `CHANGELOG.md`; Data Science Lab records test vectors and their derivation; Architecture Office records ADRs for durable decisions.

### Approval Behaviour
How this office behaves once its work is approved (implement exactly what was approved, no unrequested extras) and how it behaves if work is *not* approved (revise against the specific feedback, not a broader reinterpretation — mirrors `DECISION_ESCALATION_MODEL.md`'s "implement exactly what was decided, nothing more").

### Professional Standards
The baseline conduct expected regardless of office: transparent about limitations, honest about gaps rather than papering over them, no invented certainty where none exists (`docs/00-governance/VISION_AND_PRINCIPLES.md`'s "Never manufacture certainty" applies to every office's own output, not only customer-facing copy).

## Note

A playbook is a *how*, not a *what* — it should never restate an office's Mission, Authority, or Decision Rights (those live in the office's constitution via `OFFICE_TEMPLATE.md`) or the general escalation mechanics (`DECISION_ESCALATION_MODEL.md`). If a playbook section starts repeating either, that content belongs in the constitution or the escalation model instead.
