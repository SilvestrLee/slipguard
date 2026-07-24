---
name: sprint-verification
description: This skill should be used before declaring a sprint, task, or multi-file change complete — as the final gate, not a substitute for pest-verification or laravel-engineering during the work itself. Trigger phrases include "is this done", "finish this sprint", "ready to commit", "final verification". Use automatically at the end of substantial work, not only when explicitly asked to verify.
user-invocable: false
---

# Sprint Verification Gate (SlipGuard)

Before reporting any sprint or task as complete, work through this in order:

1. **Targeted tests** for the specific files changed, then the **full suite**: `php artisan test`. Report the exact numbers.
2. **Style**: `./vendor/bin/pint --test` (or run it for real and fix, then confirm clean).
3. **Scope check**: re-read the task's explicit out-of-scope list (if one was given) and confirm nothing on it appears in the diff. State this confirmation explicitly rather than assuming it's obvious.
4. **Consistency check**: if the task's own report claims something is "still open" or "not yet decided," verify that against what the diff actually implements — a report that implements a decision while calling it undecided is a real defect in the report itself, not a stylistic nitpick.
5. **Git status**: branch, modified/untracked files, whether anything unexpected (a stray `.env`, an unrelated file) is staged. Do not stage or commit unless the user has explicitly asked for a commit in this turn — a prior commit approval does not carry forward to new, unrelated changes.
6. **Naming/milestone check**: if a sprint is directed under a name that collides with an existing milestone in `docs/08-operations/DELIVERY_ROADMAP.md` or `TASKS.md`, flag the collision rather than silently overwriting the existing milestone's meaning.

## What "Done" Looks Like

A completion report names the actual files changed, the actual test counts, and any findings from a code-review pass individually (not just a count) — see `CHANGELOG.md`'s Sprint E-05A entries for the expected level of specificity.
