# SlipGuard Design Capability Routing

## Purpose

This document defines when candidate capabilities may be used during design and
frontend work.

Installation does not grant authority.

Candidate status does not grant authority.

Approval does not grant universal authority.

Capabilities must only be invoked within their documented responsibility.

**Added (Constitutional Review, Finding 5 — No Reconciliation Mechanism):**
the activation rules below presume the capability's status in
`docs/operating-system/capabilities/CAPABILITY-REGISTRY.md` already permits
invocation. This document does not itself authorize a capability — a
capability listed here with activation conditions is not invokable if the
Registry's Status says otherwise (Candidate, Rejected, Retired, or reverted
per that Registry's Review rule). Check the Registry first; these rules
shape *how* an already-authorized capability may be used, not *whether* it
may be.

## Routing Principle

Every capability has:

- a purpose;
- activation conditions;
- authority boundaries;
- required inputs;
- expected outputs.

Capabilities should be selected deliberately.

Do not activate every capability for every task.

## Authority Order

**Corrected (Constitutional Review, Finding 1 — Multiple Authority
Hierarchies):** this section previously restated its own ranked order,
diverging from the equivalent lists in `CAPABILITY-ADOPTION-STANDARD.md` and
`PROJECT-DESIGN-CONTEXT.md`. It now defers to a single canonical ordering
instead of forking one.

When recommendations conflict, follow the canonical authority ordering
defined in `docs/operating-system/capabilities/DESIGN-CAPABILITY-MATRIX.md`'s
Conflict Resolution section. That section is the single ranked order for this
system; it is not restated here so it cannot drift out of sync.

## Experience Architect

Activate when:

- defining workflows;
- redesigning journeys;
- creating screen inventories;
- defining interaction states;
- reducing cognitive load.

Do not activate for:

- backend-only work;
- styling adjustments;
- motion tuning;
- code review.

## Brand Governor

Activate when:

- designing user-facing interfaces;
- reviewing visual consistency;
- reviewing language;
- validating hierarchy;
- reviewing SlipGuard identity.

Do not activate for:

- infrastructure;
- database work;
- API design;
- testing;
- deployment.

## Design QA

Activate:

after implementation and before Product Office acceptance.

Its responsibility is verification, not redesign.

## Capability Auditor

Activate only when:

- evaluating a new external capability;
- replacing an existing capability;
- reviewing an installed capability;
- renewing capability approval.

Never activate it during normal feature implementation.

## Frontend Design

Activate during:

- frontend implementation;
- responsive implementation;
- component construction.

Not during:

- product planning;
- product approval;
- governance;
- capability evaluation.

## Impeccable

Activate:

after visual direction exists.

Purpose:

challenge weak hierarchy,
generic AI patterns,
poor coherence,
missing states.

Not for defining product scope.

## UI/UX Pro Max

Activate:

during structured UX research.

Treat every recommendation as advisory until reconciled against repository
evidence.

## Design Taste Frontend

Activate only for:

- landing pages;
- marketing pages;
- visual exploration suitable for its documented scope.

Not for:

- dashboards;
- accumulator builder;
- analysis workflows;
- operational interfaces.

## Emil Design Engineering

Activate only after:

- static hierarchy approved;
- states approved;
- functionality complete.

Motion must communicate:

- progress;
- hierarchy;
- continuity;
- causality;
- spatial relationship.

Never decorative animation.

## Invocation Evidence

Every significant task should report:

- capabilities requested;
- capabilities actually invoked;
- capabilities not invoked;
- evidence of invocation;
- material influence on the result;
- limitations.

## Prohibited Behaviour

Do not:

- invoke every capability;
- bypass Product Office;
- bypass UX approval;
- redefine product scope;
- redefine brand identity;
- silently replace repository decisions;
- claim capability invocation without evidence;
- auto-commit changes.
