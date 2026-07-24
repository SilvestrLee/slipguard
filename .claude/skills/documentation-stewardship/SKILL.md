---
name: documentation-stewardship
description: This skill should be used when updating TASKS.md, CHANGELOG.md, an ADR, the incident log, or any file under docs/. Trigger phrases include "update the changelog", "record this decision", "write an ADR", "update the docs". Applies whenever a task's documentation-update step is being performed, not only when explicitly asked to write documentation.
user-invocable: false
---

# Documentation Stewardship (SlipGuard / SGOS)

SGOS v1.0 is frozen (`CLAUDE.md`'s Documentation Freeze section). Do not expand governance, product, architecture, or engineering-standards documents unless: the founder explicitly requests it, implementation is blocked without it, a permanent architectural decision changes, or production experience reveals a real gap. Otherwise, build software and only touch the allowed set below.

## Always Allowed to Update

- `TASKS.md` — active milestone, done/in-progress/blocked/later. Keep it truthful to actual code state; a task marked done that the code doesn't support is worse than an honest gap.
- `CHANGELOG.md` — one section per sprint/milestone, Added/Changed/Fixed/Not Done structure (see existing entries for the exact shape). Include the real test count, not an approximation.
- `docs/08-operations/INCIDENT_AND_LEARNING_LOG.md` — genuine incidents only (a real bug, a tooling surprise like an installer overwriting files), never trivial typos.

## Allowed With a Real Reason

- A new ADR (`docs/adr/ADR-NNN-*.md` + a row in `docs/adr/ADR-INDEX.md`) — only for a durable decision affecting architecture, security, cost, or maintainability. Prefer amending an existing ADR with an addendum over creating a near-duplicate one (see ADR-006's deletion-policy addendum).
- A narrowly-scoped new doc under `docs/03-data-science/` or similar — only when it's the single authoritative record for something genuinely new (e.g. `FOOTBALL_MARKET_TAXONOMY_V1.md`), and only one such document per topic — check for an existing doc to extend before creating a new one.

## Never

- Duplicate an existing doc's content into a new file "to be safe" — this repo has already accumulated and had to archive one duplicate documentation tree (`docs/_legacy-bootstrap/`) from exactly this mistake.
- State a decision as "still open" if the current implementation has already made the call — check what the code actually does before writing a "remaining risk" or "next task" section; a report that implements X while calling X "unresolved" is internally inconsistent and erodes trust in the report.
