> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review — not merely further discussion — before any implementation may begin. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# R-01.4 — SlipGuard Intelligence Platform Foundation (SGIP) — Concept

```text
Identifier:            R-01.4
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (proposes changing SlipGuard's product identity itself,
                       not a bounded technical question — see LIFECYCLE.md §2)
Status:                Exploratory — see "Known defect" below before reading further
Owner:                 Architecture Office (role, not a standing individual)
Date Created:          this conversation's date
Last Reviewed:         this conversation's date
Dependencies:          none formally; substantively overlaps U-06/U-07 (Capability A planning)
                       and U-17 (Capability B, Deterministic Market Intelligence) — not reconciled
                       against either here
Related Architecture:  ADR-002, ADR-003, ADR-012 (referenced; a "Sports Decision Intelligence
                       Platform" framing would need to be checked against ADR-012's regulatory
                       positioning specifically, not assumed compatible)
Product Office Decision: None
Repository Status:     None
```

## Known defect — disclosed, not corrected silently

This package originated as a document calling itself "SGIP-001," self-classified `Constitutional`, `CRITICAL` priority, `APPROVED FOR EXECUTION`, citing `SD-001` as its authority. Direct verification against `docs/00-governance/SD-001-STRATEGIC-DECISION-REPORT.md` found that citation false: the real `SD-001` authorizes evolution from "betting risk intelligence platform" to "betting decision intelligence platform" and Programme U-06 (Intelligent Accumulator Planning) — nothing about a "Sports Decision Intelligence Platform," Match Intelligence, a Knowledge Layer, or an Intelligence Workspace. The original document also declared itself binding on all future feature work without reconciling against real, currently-active programmes (`U-06`/`U-07`, `U-17` Phase 2/3, the dashboard evolution work). None of that is corrected here by omission — it's the reason this stays at `Exploratory`, `Scope: Strategic`, rather than being taken at its own word.

## What's preserved below

The underlying ideas, stripped of the false authority claim, presented as what they actually are: a possible future direction, not an adopted one.

### Core proposition

SlipGuard's decision-support role could extend from *structural* risk analysis (leg count, market families, odds-derived volatility — what the engine does today) to a broader layered model:

```text
Evidence → Evidence Intelligence → Match Intelligence → Decision Intelligence
  → Accumulator Intelligence → Slip Intelligence → Portfolio Intelligence (future)
```

Note the real, substantial overlap with work already named elsewhere: "Match Intelligence" as a concept sits close to `R-01.1-MECI` (context evidence attached to a match) and to `U-17`'s "Deterministic Market Intelligence Platform" (already-authorized Capability B architecture, a different but adjacent concept). Any real pursuit of this would need to reconcile all three rather than treat them as independent, which the original document didn't attempt.

### Named principles (evaluated, not adopted)

Most of these aren't new — they restate things already true of SlipGuard today, under new names:
- "Evidence Before Opinion," "Explain Before Recommend," "Reveal Uncertainty" — already how the deterministic engine and `ADR-003` work; restating them isn't harmful, but isn't new either.
- "Assistance Before Automation" — already `ADR-012`'s "SlipGuard never becomes the decision maker."
- "Truth Over Engagement" — already `ADR-012`'s "risk-awareness-over-excitement."
- **"No Typing Philosophy" / Intent-Driven Builder** — this one is *not* a restatement. Automatic/assisted candidate construction from external evidence is specifically Capability B (`U-17.*`), already gated behind Parser Office source discovery and Compliance Office licensing review in this repository's real history. Declaring it "U-00.4, owned by Engineering Office" would skip that gate entirely. Any real version of this idea needs to go through the `U-17` sequence, not around it.
- **"Knowledge Layer" / "Platform Learning"** — the vaguest and highest-risk item here. Before this is anything more than a phrase, it needs a real answer to: does "Platform Learning" mean anything that touches how a risk score, band, or weakest-leg finding is computed? If yes, it collides with `ADR-002`'s deterministic-only mandate and needs Data Science Lab / Compliance involvement from the start, not after a Knowledge Layer already exists.

### What would have to be true before this graduates past `Exploratory`

Per `LIFECYCLE.md` §4, applied to a `Strategic`-scope package specifically: a real, dated founder decision to even consider a product-identity change this size (not inferable from this document); reconciliation against `U-06`/`U-07`/`U-17` rather than independent authorization; and — because dropping "betting" from the identity has real regulatory-positioning implications under `ADR-012` — the same order-of-magnitude compliance seriousness `03-COMPLIANCE-REVIEW.md` (in `R-01.1-MECI/`) applied to a much narrower question. None of that has happened. This stays exactly where it is: preserved, not lost, not adopted.
