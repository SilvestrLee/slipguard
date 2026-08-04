# Product Office → Engineering Handover — Intelligence Builder Workspace Evolution

| Field | Value |
|---|---|
| Document ID | `PO-U18.4.1-001` |
| Office | Product Office |
| Status | **Approved for Architecture & Engineering** — adopted as approved design direction, `PO-MVP-004`, 2026-08-03 |
| Priority | HIGH (Capability B / MVP+) |
| Provenance note | Delivered as a direct founder instruction pasted into an Engineering conversation, not through a document formatted against `docs/00-governance/office-operating-system/HANDOVER_STANDARD.md`'s required sections (Purpose/Authority/Inputs/Deliverables/Out of Scope/Acceptance Criteria/Required Reading/Escalation/Expected Outputs/Completion Conditions). Saved here verbatim so it exists as a citable repository artefact rather than only in a chat transcript. Its relationship to the still-open MVP Scope Lock decision on Capability B, and the scope boundary for how Engineering is to act on it, was not stated in the document itself — both were resolved by direct founder clarification, recorded in full at `docs/00-governance/DECISION_LOG.md`, `PO-MVP-004`, 2026-08-03. Read that entry before treating anything below as an implementation license: in summary, this document is the **approved design direction for evolving the existing, already-built Capability B Builder** — not a request to build Capability B from scratch, not a deferral, and not a request to replace the current implementation immediately. Engineering's actual next step is a gap analysis (`TASKS.md`, `U-18.4`), not direct implementation of everything below. **Terminology amendment (`PO-REVIEW-001`, 2026-08-03):** the body text below still says "No Typing" (its "Relationship to 'No Typing'" section) because it is preserved verbatim as received. That phrase has since been formally retired in favour of the **Minimal Manual Input Principle** — see `docs/00-governance/DECISION_LOG.md`, `PO-REVIEW-001` — for all writing going forward. Do not treat the verbatim text below as current terminology guidance. |

---

# Executive Summary

Following a review of SportyBet's **Multi Maker** workflow, Product Office has concluded that the interaction model is valuable, but the implementation philosophy is fundamentally different from SlipGuard's vision.

SportyBet has successfully solved the problem of **editing an accumulator before it reaches the betslip**.

SlipGuard should adopt this workflow concept while replacing every betting-centric interaction with an intelligence-first experience.

This document establishes the **Intelligence Builder Workspace** as the future interface for Capability B.

This is NOT a betting slip editor.

It is a deterministic intelligence workspace.

---

# Design Principles

## DO NOT COPY

The goal is **not** to reproduce SportyBet's UI.

Instead, preserve only the interaction pattern.

Specifically:

✓ editable temporary workspace

✓ live recalculation

✓ incremental building

✓ filters

✓ remove selections

✓ add selections

Everything else should become uniquely SlipGuard.

---

# New Product Definition

Capability B shall no longer be referred to internally as simply

> Build Accumulator

It becomes

> Intelligence Builder Workspace

The Builder is where users:

- generate candidates
- inspect candidates
- improve candidates
- regenerate candidates
- compare candidates
- save candidates
- export candidates

The Builder is **not** where users gamble.

---

# Primary User Journey

Instead of

Generate
↓

Analyse
↓

Done

The workflow becomes

Generate Candidate
↓

Open Builder Workspace
↓

Inspect
↓

Modify
↓

Regenerate
↓

Improve
↓

Accept
↓

Save
↓

Export

The Builder becomes the heart of Capability B.

---

# Workspace Layout

The Builder should be treated as a true workspace rather than a page.

Recommended layout

------------------------------------------------

Header

------------------------------------------------

Sport Selector

Filters

Workspace Actions

------------------------------------------------

Candidate List

(Intelligence Objects)

------------------------------------------------

Persistent Intelligence Sidebar

------------------------------------------------

Footer Toolbar

------------------------------------------------

Every area should remain visible while editing.

Avoid modal-heavy interactions.

---

# Sport Selector

Like SportyBet's tabs, users should switch between sports instantly.

Example

Football

Basketball

Tennis

Baseball

Cricket

Rugby

MMA

Boxing

Esports

Motorsport

etc.

IMPORTANT

This selector must be powered by the platform's sport registry.

Never hardcode sports.

Adding a new sport should require no UI redesign.

---

# Sport-Agnostic Rule

The Builder must never assume football.

Football is merely the first implementation.

Internally everything should operate on

Sport

↓

Competition

↓

Event

↓

Market

↓

Selection

No logic should ever say

if football

Instead

ask the sport adapter.

---

# Candidate Header

Instead of showing only odds, the Builder should expose intelligence.

Example

Candidate #204

Sport

Football

Selections

18

Current Odds

52.48

Structural Rating

91

Structural Risk

Medium

Evidence Quality

High

Correlation

Low

Volatility

Stable

This updates continuously.

---

# Candidate List

Each row is an Intelligence Object.

Instead of

Team A

Odds 1.43

SlipGuard should display

Selection

Market

Fixture

Odds

Risk

Evidence

Structural Rating

Volatility

Correlation

Reasoning Badge

Every row should communicate

"What do we know?"

rather than

"What are the odds?"

---

# Builder Filters

SportyBet filters by

Time

League

Odds

SlipGuard should instead prioritise intelligence.

Required filters

Sport

Competition

Market Type

Kickoff Time

Bookmaker

Odds Range

Structural Rating

Evidence Quality

Volatility

Risk Band

Correlation

Availability

Users should still be able to filter traditionally if desired.

---

# Live Intelligence Sidebar

One of the Builder's defining features.

Always visible.

Example

Current Candidate

Selections

18

Structural Risk

Medium

Evidence

92%

Weak Markets

3

Correlated Picks

2

High Volatility

1

Recommended Actions

Replace Match 7

Remove Match 12

Lower Corner Market

Use Double Chance

Swap Bookmaker

This panel continuously updates.

---

# Regenerate

SportyBet provides a refresh button.

SlipGuard replaces this with deterministic regeneration.

Rename

Regenerate Candidate

The user should be able to regenerate using goals.

Examples

Reduce Risk

Maintain Odds

Increase Evidence

Lower Correlation

Elite Leagues Only

Corners Only

Goals Markets Only

Mixed Sports

Weekend Matches

Builder regeneration should never feel random.

Every replacement must be explainable.

---

# Candidate Library Integration

The Builder becomes the editor for Candidate Library.

Users may begin from

Today's Safe Picks

Weekend Builder

Elite Leagues

High Confidence

Low Risk

Mixed Sports

Basketball Tonight

Corner Specialists

BTTS Collection

Player Props

etc.

Opening a candidate immediately loads it into the Builder Workspace.

---

# Mixed Sport Accumulators

The architecture must support mixed-sport accumulators.

Example

Football

↓

Basketball

↓

Tennis

↓

MMA

↓

Esports

All inside one candidate.

No assumptions anywhere in Builder logic should prevent this.

---

# Intelligence-First Editing

Every modification should trigger

Re-score

↓

Re-calculate

↓

Re-evaluate

↓

Re-explain

NOT merely

Update Odds

Odds are only one metric.

---

# Footer Toolbar

The footer becomes action-oriented.

Possible actions

Save Candidate

Duplicate

Regenerate

Compare

Analyse

Export

Share

Archive

No "Place Bet."

SlipGuard is not a bookmaker.

---

# Explainability

Every automated change should answer

Why?

Examples

Selection removed because

High structural volatility.

Selection replaced because

Equivalent market with stronger evidence exists.

Odds reduced because

Risk reduction requested.

Evidence improved because

Provider confidence increased.

Nothing should ever appear arbitrary.

---

# Relationship to Capability B

Capability B should now be viewed as

Candidate Generation Engine

+

Intelligence Builder Workspace

+

Candidate Library

+

Deterministic Optimisation Engine

Generation is only step one.

The Builder is where the real value is created.

---

# Relationship to "No Typing"

This enhancement strengthens the previously approved No Typing initiative.

Users should rarely need to search manually.

Instead they

Browse

Generate

Refine

Accept

The Builder becomes the primary interaction model.

---

# Architecture Requirements

Engineering should ensure

• Builder never assumes football

• All intelligence supplied through sport adapters

• Candidate model is reusable

• Deterministic calculations remain shared

• Regeneration engine remains explainable

• UI components reusable across sports

• Filters dynamically generated from sport metadata

---

# UX Requirements

The Builder must feel

Calm

Professional

Analytical

Human-designed

Never like a sportsbook.

Avoid flashing odds.

Avoid gambling aesthetics.

Prioritise clarity over excitement.

---

# Success Criteria

The implementation is considered successful when

✓ Users can begin with a generated or library candidate.

✓ Candidates are editable without leaving the Builder.

✓ Every change updates intelligence in real time.

✓ Regeneration is deterministic and explainable.

✓ The same Builder works for football, basketball, tennis, cricket, MMA, esports and future sports.

✓ Mixed-sport accumulators work naturally.

✓ The Builder reinforces SlipGuard's identity as a sports intelligence platform rather than a betting platform.

---

# Product Office Decision

This proposal is **APPROVED**.

The SportyBet Multi Maker interaction has validated the need for a dedicated editing workspace, but SlipGuard will evolve the concept into an **Intelligence Builder Workspace**.

This workspace becomes the central operating environment for Capability B and the Candidate Library, and it must be implemented in a sport-agnostic manner so that every current and future sport can use the same deterministic intelligence pipeline with no football-specific assumptions embedded in the architecture.
