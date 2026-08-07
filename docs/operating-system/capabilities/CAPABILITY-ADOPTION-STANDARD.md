# Tsotsia Capability Adoption Standard

## Purpose

This standard governs the evaluation, installation, use, review and retirement
of external AI skills, plugins, MCP servers, agent frameworks, libraries and
other reusable development capabilities.

An external capability is not approved merely because it is popular,
impressive, installable or recommended by an AI model.

## Scope

This standard applies to:

- Claude Code skills and plugins;
- Codex skills and instructions;
- MCP servers;
- development agents;
- design tools;
- testing and review tools;
- automation frameworks;
- APIs and SDKs;
- reusable internal capabilities;
- future agentic-development tooling.

## Custody

**Added (Constitutional Review, Finding 6 — No Multi-Repository Custody
Model):** this document is framed as a Tsotsia-wide standard, reusable
across projects, but it physically exists only at this location inside the
SlipGuard repository. No other repository's copy has been verified to exist
from here, and this document cannot itself guarantee synchronization with
copies it did not create.

This copy, at
`docs/operating-system/capabilities/CAPABILITY-ADOPTION-STANDARD.md` in the
SlipGuard repository, is SlipGuard's local instance of this standard. A
second Tsotsia project reusing it must either reference this exact file
directly or maintain its own copy; if it maintains its own copy, that
project is responsible for reconciling it against this one — nothing here
does that automatically. Product Office is the named owner for reconciling
this copy if a separate canonical Tsotsia-wide copy is later established
elsewhere.

## Adoption Gates

Every capability must be evaluated against the following gates.

### 1. Factuality

Determine:

- whether the capability actually exists;
- whether its repository or publisher is identifiable;
- whether its stated features are present;
- whether installation instructions are current;
- whether public claims are supported by documentation or code.

### 2. Maturity

Determine:

- maintenance activity;
- documentation quality;
- release history;
- issue handling;
- dependency stability;
- practical evidence beyond demonstrations;
- whether the capability is production-ready or experimental.

### 3. Scalability

Determine:

- whether it can be reused across projects;
- whether it requires excessive project-specific modification;
- whether it causes context leakage;
- whether it supports the relevant technology stacks;
- whether its output is repeatable.

### 4. Governance

Determine:

- what authority the capability receives;
- whether its responsibility overlaps another capability;
- whether activation can be controlled;
- whether its use can be evidenced;
- whether it respects repository and office authority;
- whether its recommendations can be reviewed before implementation.

### 5. Return on Complexity

Determine:

- measurable quality improvement;
- token and context cost;
- installation overhead;
- maintenance cost;
- dependency risk;
- learning cost;
- replacement difficulty;
- whether a simpler mechanism would achieve the same result.

## Classification

**Renamed (CEP-003, 2026-08-06 — Operational Capability Portfolio v1.0):**
"Pilot only" is renamed **Active Pilot**, matching the term the Capability
Registry's own section heading ("Active Pilots") already used, closing a
naming mismatch between that heading and its Status column. "Experimental"
is replaced by **Deferred**: no capability in this repository's history has
ever actually used Experimental's "research and isolated testing permitted"
meaning, while CEP-001 produced a real, unmet need for a status meaning
"evaluated, and consciously parked rather than piloted" (Design Taste
Frontend's CEP-001 recommendation was literally "Defer," with nowhere
accurate to record that outcome until now). Meaning is otherwise unchanged
from the two definitions below; this is a rename plus one semantic
correction, not a new gate or a loosened bar.

Every reviewed capability must receive one classification:

- Candidate
- Approved
- Approved with restrictions
- Active Pilot
- Deferred
- Rejected
- Retired

### Classification Meaning

- Candidate: Identified for evaluation but not yet authorized for installation or operational use.
- Approved: Evaluated and permitted for its defined responsibility.
- Approved with restrictions: Evaluated and permitted only within documented boundaries.
- Active Pilot: Evaluated sufficiently for controlled testing in the named pilot repository, but not approved for wider rollout.
- Deferred: Evaluated, and consciously not proceeding to pilot at this time — parked pending a named future trigger, not rejected on the merits.
- Rejected: Evaluated and not permitted for use.
- Retired: Previously permitted but no longer approved for use.

## Authority

External capabilities are advisory.

**Corrected (Constitutional Review, Finding 1 — Multiple Authority
Hierarchies):** this list previously placed direct user instructions last,
contradicting the ranked order used elsewhere in this system. It is now
ordered consistently with the canonical authority ordering in
`docs/operating-system/capabilities/DESIGN-CAPABILITY-MATRIX.md`'s Conflict
Resolution section — that section is the single canonical ordering; this list
restates only the items relevant to a Tsotsia-wide, cross-project standard
(it omits project-specific tiers such as Project Design Context, which do not
apply at this document's scope).

They may not override:

1. Direct user instructions
2. Repository Constitution
3. Approved Product Office decisions
4. Security, compliance and privacy requirements
5. Architecture decisions
6. Approved project requirements
7. Acceptance criteria
8. Approved brand and UX documentation

## Security

Third-party skills must be treated as untrusted until reviewed.

Before installation:

- inspect the repository;
- inspect installation scripts where practical;
- identify files that will be created or changed;
- identify executable scripts and dependencies;
- confirm whether installation is project-local or global;
- record the installed source and version;
- review unexpected changes before use.

## Installation Rule

No capability may be rolled out across all projects until:

1. it has been evaluated;
2. it has been registered;
3. it has been piloted in one repository;
4. its behaviour has been tested on a real task;
5. its benefit has been demonstrated;
6. conflicts and maintenance implications have been documented.

## Evidence Rule

For significant work, the executing agent must report:

- capability requested;
- capability actually invoked;
- evidence of invocation;
- files or decisions influenced;
- limitations encountered;
- whether recommendations were accepted or rejected.

Claiming that a skill was used is not sufficient evidence.

## Review and Retirement

**Corrected (Constitutional Review, Finding 4 — No Review Enforcement):**
"reviewed periodically" previously named no owner, no real date, and no
consequence for a missed review — every Review date in the Capability
Registry was a condition ("after pilot," "after website test") rather than a
date, with nothing that would ever force it to become one. The rule below
now names an owner, distinguishes when a condition is acceptable from when a
real date is required, and states what happens if review is missed, without
inventing a review that has not happened.

Product Office owns capability review. A capability at Approved, Approved
with restrictions, Active Pilot, or Deferred status must carry a real
calendar review date in the Capability Registry, not a condition. A
condition (for example, "after evaluation") is acceptable only while a
capability remains at Candidate status, since no decision has yet been made
for a date to meaningfully follow — once a condition is met, Product Office
is responsible for replacing it with an actual date, not leaving it
open-ended.

If a Review date passes without a recorded review, the capability is treated
as Candidate for authorization purposes — not authorized for installation or
operational use — until review is completed and a new status and date are
recorded. Silence does not extend an approval.

A capability may be retired when:

- it is abandoned;
- it becomes duplicative;
- its function is absorbed internally;
- it introduces unacceptable risk;
- a better replacement is approved;
- its benefit no longer justifies its complexity.
