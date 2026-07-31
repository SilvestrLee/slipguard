# SD-001 Strategic Decision Report — Evolution to Betting Decision Intelligence Platform

| Field | Value |
|---|---|
| Decision ID | SD-001 |
| Date | 2026-07-26 |
| Authority | Product Office (founder) |
| Status | Decision recorded and reconciled into repository governance. **No architecture, design, or implementation has begun.** |
| Related | `docs/00-governance/DECISION_LOG.md` (SD-001 entry), `docs/08-operations/DELIVERY_ROADMAP.md` (Programme U-06), `TASKS.md` |

---

## 1. What Was Decided

SlipGuard's product identity evolves from "betting risk intelligence platform" to **"betting decision intelligence platform."** A new capability — **Intelligent Accumulator Planning** (Programme U-06) — is now approved in principle: a tool that assists customers in constructing accumulators by generating candidate combinations and explaining their structural suitability.

This supersedes two prior restrictions:
- `PROJECT.md`'s MVP non-goal "safe accumulator generation."
- `DECISION_LOG.md`'s 2026-07-25 note reserving "Recommendations" for a future, separately-approved milestone.

**What remains permanently unchanged**, reaffirmed explicitly as part of this same decision:
- No outcome prediction, no "safe bet" or guaranteed-win claims, no certainty claims, no fabricated evidence.
- Every planner recommendation must be explainable, deterministic, evidence-based, and fully customer-editable (remove/replace/lock/regenerate) — the customer remains the final decision-maker.
- AI may explain reasoning in plain language only; AI never determines suitability, ranking, confidence, or planner outputs — those stay deterministic.

## 2. Why It Was Decided

Given directly, with reasoning, not just asserted by document formatting:
- **Personal:** the product's original motivation was the founder's own experience that more football research and predictions didn't improve betting decisions — the missing thing was a disciplined, transparent, explainable *decision process*, not more predictions.
- **Commercial:** post-slip analysis alone is judged unlikely to sustain recurring subscription engagement; assistance *before* a bet is placed is expected to provide more sustained customer value than analysis *after* the fact.

## 3. How This Was Reached — Full Exchange, Not Just the Outcome

This decision didn't arrive as a single request; it went through real pushback, recorded here for the audit trail rather than left implicit:

1. An Architecture Office discovery directive (`AR-U06.1-001`) was received, asking for read-only architectural inspection to support a "Planner" capability.
2. Before doing that work, a direct conflict was raised: "Intelligent Accumulator Planner" appeared to contradict `PROJECT.md`'s explicit MVP non-goal "safe accumulator generation" and `CLAUDE.md`'s locked "no outcome prediction"/"no safe-bet claims" decisions, with no prior trace anywhere in the repository's history. Discovery work was paused pending clarification.
3. A "Constitutional Amendment" (`CA-001`) followed, reframing the planner using careful language distinguishing "opaque recommendation" (prohibited) from "transparent deterministic planning" (permitted). This was assessed as still functionally a recommendation/generation engine dressed in different vocabulary, and was pushed back on a second time — directly, in plain language, asking whether this was genuinely wanted.
4. `SD-001` followed, this time openly using the word "recommendation" rather than avoiding it, and supplying real first-person reasoning (§2 above). This was accepted as a genuine, considered decision — not because the document was more emphatically formatted, but because it directly answered the question asked and gave verifiable reasoning rather than asserting authority alone.

## 4. Repository Changes Made

All uncommitted, pending your review, same as everything else this session:

| File | Change |
|---|---|
| `CLAUDE.md` | Product Identity updated to "betting decision intelligence platform"; "provide tips" replaced with SD-001's actual distinction (unexplained suggestion vs. deterministic/explainable/editable planning); a new Locked Decision added constraining accumulator planning to those same terms. "No outcome prediction"/"no safe-bet claims" left untouched. |
| `PROJECT.md` | "Safe accumulator generation" removed from MVP Non-Goals, with a note on what's newly permitted vs. still excluded. |
| `docs/00-governance/DECISION_LOG.md` | New SD-001 entry — the decision, the reasoning, the permanent prohibitions, and both concerns from §5 below. Explicitly marked as superseding the 2026-07-25 "Recommendations reserved" entry, which was left in place, not deleted. |
| `docs/08-operations/DELIVERY_ROADMAP.md` | New "U-06 — Intelligent Accumulator Planning" entry, status stated as decision-only, with the data-source prerequisite (§6) noted inline. |
| `TASKS.md` | New Programme U-06 tracking note under Active Milestone, same status and prerequisite. |

## 5. Concerns Raised — Not Blockers, but on the Record

1. **Tension with "Accumulator Tax."** SlipGuard already has an approved concept whose entire premise is that more legs compound structural risk. A tool whose purpose is helping customers *build* accumulators is in real tension with that message and deserves care in how it's positioned, not just a documentation reconciliation.
2. **Regulatory exposure.** Gambling-adjacent planning/advisory tools are the kind of thing specifically regulated in a number of jurisdictions (e.g. UK Gambling Commission rules touching tipster-adjacent services). Genuine external legal/compliance review was recommended before this ships — an internal "Compliance Office" governance document is not a substitute for that.

## 6. Open Prerequisite — Not Yet Resolved

**"Generate accumulator candidates" requires a source of available fixtures, markets, and odds to choose from.** This repository has none today — no fixture database, no odds feed, no bookmaker integration; the app only processes slips a customer enters manually. `PROJECT.md`'s "bookmaker parsing" non-goal is untouched by SD-001 — only "safe accumulator generation" was removed. This is a load-bearing dependency with no decision behind it yet, not an assumption to build on.

## 7. Current Status

- **Decision:** recorded, reconciled into governing documents.
- **Architecture discovery (`AR-U06.1-001`):** received, paused, **not executed.**
- **Design, implementation:** not started.
- **Next step, unresolved:** how U-06 gets its underlying market/odds data — needs its own decision before Architecture discovery would produce a solid answer.

## 8. Confirmation

No application code, tests, migrations, routes, or Risk Engine behaviour were touched in producing this report or the SD-001 documentation changes it describes. Full Pest suite re-run after the documentation changes: 329/329 passing.
