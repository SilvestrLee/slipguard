> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. Requires real UX Studio process (Frontend Work Rule, Mandatory Specialist Design Capability Invocation) and Product Office commissioning before any implementation may begin. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# R-01.5 — Customer Decision Journey & Experience Flow

```text
Identifier:            R-01.5
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (proposes reorganizing navigation/UX platform-wide,
                       not a bounded technical question)
Status:                Exploratory — see "Citation note" below
Owner:                 UX Studio (role, not a standing individual)
Date Created:          this conversation's date
Last Reviewed:         this conversation's date
Dependencies:          R-01.4-SGIP (partially cited as authority — see below)
Related Architecture:  ADR-003, ADR-012 (content checked against both, no conflict found);
                       docs/05-ux/ (not yet reviewed against this proposal — required before
                       any implementation, per CLAUDE.md's Frontend Work Rule)
Product Office Decision: None
Repository Status:     None
```

## Citation note — disclosed, not corrected silently

This document's "Strategic Authority" cited "existing Product Office direction for... Intent-Driven Builder" among its basis. That concept exists only in `R-01.4-SGIP` — classified `Exploratory`, `Scope: Strategic`, explicitly not adopted. Citing unadopted incubation material as "existing direction" doesn't make it existing direction; it's noted here rather than passed through silently. The rest of the proposal doesn't depend on this citation being true — the six-stage journey model stands or falls on its own content, checked below.

## Content assessment

The substance holds up well against real, locked constraints — checked directly, not assumed:
- Stage 1 (Observe) and Stage 4 (Validate) explicitly rule out predictions/tips/recommendations, matching `ADR-003` exactly.
- Stage 5 (Decide) states "SlipGuard never makes the decision," matching `ADR-012` Principle 2 exactly.
- Stage 1's actual content (Dashboard, "Continuation," recent analyses/reports) closely mirrors what's *already real* in this repository — `PO-U02-DASH-002`'s delivered "Continue Working" and "Needs Your Attention" dashboard sections (per `TASKS.md`) are functionally the same idea under a different name, which is a good sign: this proposal is extending something already built and accepted, not inventing from nothing.
- "Watchlist (future)" is honestly marked as not-yet-existing rather than presented as real.

No content conflict with any Locked Decision was found. The gap isn't in what this proposes — it's procedural: none of it has gone through the Frontend Work Rule's required `docs/05-ux/` review, and none of it has been checked against UI UX Pro Max / 21st.dev per the Mandatory Specialist Design Capability Invocation, both required *before finalizing* a design decision this broad, not after.

## What would actually need to happen before this graduates

Per `LIFECYCLE.md` §4, for a `Strategic`-scope package: a real Product Office decision to pursue a navigation reorganization at all (not inferred from this document); the Frontend Work Rule's own required sequence (review existing `docs/05-ux/` documents, invoke UI UX Pro Max/21st.dev for reference, compare against Locked Decisions, adopt/modify/reject each recommendation explicitly, document the outcome); and, per the Gap Rule, any genuinely new UX pattern this implies gets documented in `docs/05-ux/` *before* implementation, not invented inside a screen. None of that has happened yet — this stays `Exploratory`.

## Follow-up: UX exploration completed

`02-UX-EXPLORATION-REPORT.md` — the Frontend Work Rule review and Mandatory Specialist Design Capability Invocation (both tools, genuinely invoked) were actually carried out, in response to a follow-up commission (`PO-U18.3.1-001`). Headline finding: most of the six proposed exploration areas already have a real, locked answer in `docs/05-ux/` — only the Builder ("No Typing") and Intelligence Presentation areas were genuinely open, and a Learning/behavioural-summaries area was found to be a compliance question, not a design question, and was deliberately not designed further.
