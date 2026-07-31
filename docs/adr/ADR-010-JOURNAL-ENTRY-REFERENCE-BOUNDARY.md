# ADR-010 — Journal Entry Reference Boundary

**Status:** Accepted (Product Office + Architecture Office, `PO-U08.3-001`, 2026-07-27) — formalises architecture already accepted (`PO-U08.1-AC-001`) and already implemented (`PO-U08.2-AC-001`); no amendment to either acceptance.

## Context

`U-08.1`'s architecture document proposed a `JournalEntry` domain model to resolve `U-04.1`'s Open Issue OI-01 (the Journal capability's persistence schema, left unspecified by UX Studio for Architecture Office to decide). Product Office accepted that proposal in principle (`PO-U08.1-AC-001`), and Engineering implemented it (`PO-U08.2-AC-001`) — but the proposal itself was never drafted as its own standalone, numbered ADR, only described inline within `U-08.1`'s document. This left the repository citing "ADR-010" as though it already existed as a constitutional artefact when it did not (identified during review of `PO-U08.2-AC-001`, corrected in `docs/00-governance/DECISION_LOG.md`). This ADR closes that gap — it documents the decision already made and already built, not a new one.

## Decision

A `JournalEntry` is the customer's own reflection on exactly one completed analysis. Its architectural boundary:

**Purpose:** record what a customer decided and what they later learned about a specific, already-persisted `SlipAnalysis` — never a system-generated insight, never a re-statement of the analysis itself (`COMPONENT_PRINCIPLES.md`'s Journal Cards anti-pattern).

**Relationship to `SlipAnalysis`:** a `JournalEntry` belongs to exactly one `SlipAnalysis` (`slip_analysis_id`, not `BettingSlip` — this was `ADR-007`'s own forward-looking note: "journal entries will reference `SlipAnalysis`, not `BettingSlip`, so a journal entry always reflects the exact analysis a customer saw"). One `SlipAnalysis` may have zero or many journal entries — a customer may reflect on the same result more than once.

**Immutable analysis linkage:** `slip_analysis_id` is fixed at creation and never appears in any update action's attributes (`App\Actions\Journal\UpdateJournalEntry` accepts only `reflection`). This prevents an entry from silently detaching from the context it was written about — the customer's note about "this specific result" can never later become a note about a different one.

**Editable reflection:** `reflection` (free text) is the one mutable field, per `U-04.1` §6.2's explicit "Edit an existing entry inline" requirement. Editability of the customer's own words is not in tension with immutability of the *reference* — the two are independent properties of the same record.

**Persistence model:** a plain Eloquent model (`journal_entries`: `id`, `user_id` denormalized from the owner — mirroring `SlipAnalysis.user_id`'s own established precedent — `slip_analysis_id`, `reflection`, timestamps). No status enum, no lifecycle states — a `JournalEntry` simply exists, is editable, and (per `PO-U08.1-AC-001`'s resolution of OQ-1) is not currently deletable by any implemented action.

**Lifecycle:** none. A `JournalEntry` does not gate, block, or participate in `BettingSlip`'s or `SlipAnalysis`'s own lifecycle in any way — it is a satellite record, read and written independently of both.

**Relationship to Workspace:** surfaced only through the Journal screen (`journal.index`/`journal.entry`, `U-08.2`) — never through a separate "Analysis Detail" view (Analysis Detail Access reuses the existing Report screen unmodified, `U-04.1` §7) and never attached to a Planner session directly (Planning History and Journal remain separate, parallel Workspace surfaces — a customer journals about a *result*, and a Planner session's own purpose is to produce a new, journal-able result once exported, not to be journaled about itself).

**Rationale for immutability (of the reference, specifically):** mirrors `ADR-006`'s already-established principle — a record's identity-defining reference is never silently reassigned after creation — applied to a new, smaller domain object rather than restating a new methodology. The reference is what makes a `JournalEntry` trustworthy as "what I actually thought about this specific result"; without it, an edited reference could quietly rewrite the historical context of a customer's own past reflection.

## Consequences

- `slip_analysis_id`'s `cascadeOnDelete()` (not `restrictOnDelete()`) is a deliberate, different choice from the Planner's `source_betting_slip_id` precedent (`ADR-009`) — a `JournalEntry` is the customer's own annotation, not a constitutional audit trail the platform itself must preserve. Presently theoretical: `SlipAnalysis` has no delete path today regardless.
- No new lifecycle enum, no new status field, no new lifecycle diagram — `JournalEntry` deliberately carries none of the machinery `PlannerSession`/`BettingSlip` require, since it needs none.
- Deletability of a `JournalEntry` remains an open Product Office question (`U-08.1`'s OQ-1) — this ADR does not resolve it; `JournalEntryPolicy` simply defines no `delete` ability today, consistent with `PO-U08.1-AC-001`'s "immutable historical records, for now" direction.
