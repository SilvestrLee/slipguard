# SlipGuard Design Workflow

## Purpose

This document defines the standard lifecycle for significant user-facing
design work within SlipGuard.

The workflow separates governance, experience design, implementation,
verification and acceptance.

No capability may bypass this workflow.

## Workflow

Product Office
↓

Experience Architecture
↓

UX & Visual Direction
↓

Engineering Implementation
↓

Design QA
↓

UX Verification
↓

Product Office Acceptance

## Gate 1 — Product Office

Purpose

Confirm:

- business objective;
- user objective;
- scope;
- exclusions;
- acceptance criteria;
- office ownership.

Output

Approved Product Direction.

No implementation begins here.

## Gate 2 — Experience Architecture

Purpose

Translate approved product direction into:

- journeys;
- workflows;
- navigation;
- hierarchy;
- interaction states;
- screen inventory.

Output

Experience Specification.

## Gate 3 — UX & Visual Direction

Purpose

Define:

- hierarchy;
- layout;
- visual treatment;
- typography behaviour;
- information density;
- interaction style.

Project Design Context must be reconciled before recommendations are accepted.

Output

Approved UX Direction.

## Gate 4 — Engineering Implementation

Purpose

Implement only the approved scope.

Requirements

- inspect existing architecture;
- identify affected files;
- reuse existing components where appropriate;
- preserve repository conventions;
- avoid unrelated redesigns.

Output

Working implementation.

## Gate 5 — Design QA

Purpose

Verify:

- requirements;
- state completeness;
- accessibility;
- responsiveness;
- visual quality;
- interaction quality;
- brand consistency.

Output

QA Report.

## Gate 6 — UX Verification

Purpose

Determine whether the implementation still reflects the approved experience.

Output

UX Verification Report.

## Gate 7 — Product Office Acceptance

Purpose

Determine whether the work is:

- Accepted;
- Conditionally Accepted;
- Rejected.

Only this gate authorizes the work as accepted.

## Capability Participation

Capabilities support the workflow.

They do not replace it.

No capability may:

- approve itself;
- approve another capability;
- approve product scope;
- approve release.

## Evidence

Every completed workflow should record:

- offices involved;
- capabilities requested;
- capabilities invoked;
- repository evidence reviewed;
- implementation files;
- QA findings;
- Product Office decision.

## Exceptions

Emergency fixes may shorten the workflow only with explicit Product Office
authorization.

The shortened workflow and reason must be documented.
