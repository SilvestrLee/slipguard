# ADR-006 — Betting Slip Content Versioning

**Status:** Accepted

## Context

Sprint E-03B (Slip Domain Hardening) required an explicit answer to whether `BettingSlip` needs its own content-versioning scheme (a version column, a history table, or event sourcing) before the future Risk Engine can safely consume it.

## Decision

No. `BettingSlip` does not get a separate version column, history table, or event log. Immutability is enforced through the slip lifecycle instead (`BettingSlipStatus`: Draft → Ready → Analysed → Archived): only a Draft slip is editable. Once a slip leaves Draft, its legs cannot change (`SaveBettingSlip` refuses to touch a non-editable slip; see `App\Exceptions\BettingSlipNotEditableException`). A locked slip is therefore already a single, stable version of itself — there is nothing left to version against.

## Alternatives Considered

- **A `version` integer column, bumped on every edit.** Rejected: with edits blocked entirely once a slip is Ready, there is no in-place mutation for a version counter to distinguish between.
- **A full edit-history table.** Rejected as speculative: nothing in the current MVP scope (E-04's deterministic analysis) needs to reconstruct prior edits to a slip, and building it now would be exactly the kind of audit trail / event sourcing this sprint was explicitly told not to build.

## Consequences

- The future `SlipAnalysis` record (E-04, deterministic risk analysis) still carries its own `engine_version` / `rule_set_version` per `docs/03-data-science/RISK_ENGINE.md` — that versions the *analysis*, not the slip. This ADR does not change that.
- If a user needs to change locked data, they must explicitly return the slip to Draft first (`BettingSlip::returnToDraft()`); there is no path that mutates Ready/Analysed/Archived data in place, so no version drift can occur.
- Revisit only if a real product requirement emerges for retaining multiple historical drafts of the same conceptual slip — not currently needed.

## Addendum (Sprint E-05A) — Analysed slips may not be deleted

The same immutability reasoning above extends to deletion, not just editing. Once a `SlipAnalysis` record exists for a slip, deleting the slip would either cascade-delete that historical result or leave it orphaned — both unacceptable for an auditable engine (see the Stage 1 Engine Contract review, `next-step.md`, §"Two Concrete Conflicts").

`BettingSlipPolicy::delete()` now permits deletion only for Draft and Ready slips. Analysed and Archived slips can no longer be deleted through the normal workflow — archiving is the only "remove from active view" action available once a slip has been analysed. This does not require a new ADR; it is the same content-immutability principle this ADR already established, applied to one more operation.
