# Research & Incubation (R-01)

**This directory is explicitly outside SGOS.** `docs/README.md` defines SGOS's authority as `docs/00-governance/` through `docs/09-compliance/`, `docs/adr/`, `docs/offices/`, and `docs/engineering/` — a closed list. `docs/10-research-incubation/` is not on that list, by design, the same mechanism that already keeps `docs/_legacy-bootstrap/` non-authoritative. Nothing here requires an edit to `docs/README.md`, `CLAUDE.md`, `TASKS.md`, `DECISION_LOG.md`, any ADR, or any office document to remain excluded — exclusion is structural, not a promise.

## What this is

A place to preserve architectural, research, and analytical work that was produced before implementation — so it isn't lost to conversation history — without letting it masquerade as repository history, an accepted decision, or implementation evidence.

## What this is not

- **Not constitutional history.** Nothing here is cited by `TASKS.md`, `CHANGELOG.md`, `DECISION_LOG.md`, any ADR, or any `docs/offices/` document, and nothing here should be treated as if it were, by any future reader — human or AI — regardless of how formally a document in here is formatted.
- **Not a second source of truth.** If SGOS and this directory ever appear to disagree, SGOS wins, unconditionally — there is no "reconciliation" owed to this directory.
- **Not implementation evidence.** Nothing here proves, or should be read as claiming, that any code, migration, provider contract, or credential exists. Check the actual repository for that, never a document in here.
- **Not self-adopting.** A document graduating out of this directory requires the same real, evidenced process any other constitutional change requires (see below) — it does not happen by virtue of having been written carefully, formatted formally, or reviewed extensively in conversation.

## Required status banner

Every document under this directory must open with:

> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review — not merely further discussion — before any implementation may begin. See `docs/10-research-incubation/README.md`.

## Lifecycle, classification, and graduation framework

The short version is below; the full framework — status classification (§1), the required per-package metadata block (§2), objective per-transition evidence requirements (§4), and repository guidance for future packages (§6) — is `LIFECYCLE.md`. The one rule that matters most, stated there and restated here: **no status past `Exploratory` may be self-assigned by a document.** A thorough, well-cited, internally consistent write-up is still just `Exploratory` — real graduation requires evidence that exists outside the document making the claim.

```text
Research (this directory)
  ↓  Architecture Office review against current, real repository state — not against
     the incubated document's own internal consistency alone
  ↓  Product Office commissioning — a real, specific instruction to proceed, given
     outside the incubated material itself, the same "current founder instruction"
     standard CLAUDE.md's Source-of-Truth Order already applies everywhere else
  ↓  Engineering implementation — real code, in the real repository, reviewed and
     tested the same as any other change
  ↓  Repository verification — the change is confirmed to exist, by inspecting the
     repository directly, not by any prior claim about it
  ↓  Governance adoption — TASKS.md / CHANGELOG.md / DECISION_LOG.md / an ADR updated
     to reflect what is now actually true, at that point, and not before
  ↓  Constitutional history
```

No step is skippable by citing an earlier step's own formality or length. In particular: extensive discussion of a proposal, in this directory or in conversation, is not itself evidence the proposal graduates — only real implementation, verified against the real repository, is.

## Current contents

- **`R-01.1-MECI/`** — Match Evidence & Context Intelligence: an out-of-band architecture, evidence-source assessment, and licensing review conducted entirely in conversation, before any repository change. Status: exploratory only, per every document inside it.
- **`R-01.4-SGIP/`** — SlipGuard Intelligence Platform Foundation concept: a strategic-scope (`LIFECYCLE.md` §2) proposal to broaden SlipGuard's identity and add several new capabilities, preserved after its original citation of `SD-001` was found not to support what it claimed. Status: exploratory, disclosed defect included rather than corrected silently.
- **`R-01.5-DECISION-JOURNEY/`** — a six-stage customer decision journey / navigation philosophy, content-checked clean against `ADR-003`/`ADR-012`; includes a completed UX exploration (both specialist design tools genuinely invoked) finding most areas already decided, two genuinely open (Builder, Intelligence Presentation), and one (Learning/behavioural summaries) a compliance question, not a design one.
- **`R-01.6-PLATFORM-PRINCIPLES/`** — duplication analysis: nine of ten proposed "platform principles" restate already-locked real governance; not recommended for adoption. One real defect found and named (Principle 7's "encourage continuous engagement" conflicts with `ADR-012` and real prior product decisions).
- **`R-01.7-CROSS-REFERENCE-INDEX/`** — a real, verified topic-to-document index (every row checked against an actual file this session). Unlike everything else here, this one has no substantive open question — it's a placement question only. Says so explicitly in its own README.
