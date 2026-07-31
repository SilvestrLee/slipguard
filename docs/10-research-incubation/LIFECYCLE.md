# Research & Incubation — Lifecycle, Classification, and Graduation Framework

**This document is itself outside SGOS**, for the same structural reason everything under `docs/10-research-incubation/` is (see `README.md`). It governs *process* for this directory only — it has no authority over `docs/00-governance/`, `docs/adr/`, `docs/offices/`, `TASKS.md`, `DECISION_LOG.md`, or `CHANGELOG.md`, and nothing in it can be cited as amending any of those.

## The one rule everything below exists to enforce

**No status past "Exploratory" may be self-assigned by a document.** A document can describe research thoroughly, cite real sources, and be internally consistent — none of that is evidence of anything beyond "someone wrote this down carefully." Every classification from `Parser-Validated` onward (§1) requires evidence that exists *outside* the document making the claim — a real trial payload, a real counsel engagement, a real founder instruction, or real repository state — checked at the moment the status is asserted, not assumed from how the document reads. This is the same verification-before-adoption discipline already standing elsewhere in this repository's real governance, applied here as a structural constraint on this directory specifically, so it can't be argued around by producing more (however well-written) paperwork.

---

## 1. Classification Guide

| Status | Meaning | What makes it true |
|---|---|---|
| **Concept** | An idea, not yet written up | A one-paragraph note exists somewhere; nothing else required |
| **Exploratory** | A document (architecture, research, analysis) exists | Written, internally coherent, cites real sources where it makes factual claims. **This is the ceiling reachable through discussion, research, or design work alone — no matter how many "offices" a conversation walks it through.** |
| **Parser-Validated** | Evidence-source viability confirmed against real data, not marketing pages | A real trial was performed (real account, real credentials, real request) and its actual response — or a redacted excerpt of one — is attached or referenced. Public pricing/ToS-page research, however thorough, does not qualify; it stays `Exploratory`. |
| **Compliance-Cleared** | Licensing/legal posture confirmed | A named, dated engagement with qualified external counsel (or, for a genuinely low-risk case, an explicit Product Office risk-acceptance decision naming what wasn't independently reviewed and why) exists. An internally-written compliance-shaped document, however rigorous, is `Exploratory`, not `Compliance-Cleared` — this repository's own MECI compliance research says so about itself. |
| **Commissioned** | A real decision to proceed exists | A current, direct founder/Product Office instruction, given outside the research material itself, specifically authorizing this package to proceed — matching `CLAUDE.md`'s own "current founder instruction" as the top Source-of-Truth. A memo *describing* a decision, found inside the incubated material, is not itself that instruction. |
| **Implementing** | Real code exists | A branch, PR, or committed change exists in the actual repository, reviewed and tested the normal way — not merely planned or scaffolded in a document. |
| **Verified** | Implementation confirmed against the commission | Direct repository inspection confirms the change exists, does what was commissioned, and nothing more. |
| **Adopted** | Now constitutional history | `TASKS.md`/`CHANGELOG.md`/`DECISION_LOG.md`/an ADR has been updated to reflect what is now actually true — at that point, and never before. |
| **Archived** | Abandoned, not pursued | Left in place with this status, not deleted — a record that the idea was considered and why it stopped, useful against future re-litigation from scratch. |
| **Superseded** | Replaced by later, different research | Left in place, cross-referenced from whatever replaced it. |

A package's status is the **lowest** stage any of its real sub-parts have actually reached — e.g. a package with a real Parser trial but no real counsel engagement is `Parser-Validated`, not `Compliance-Cleared`, even if a compliance document exists inside it.

---

## 2. Metadata block (required at the top of every package's index document)

```text
Identifier:            R-0X.Y
Programme:             (parent research programme, if any)
Scope:                 Technical | Strategic (see note below)
Status:                (per §1 — the current honest status, re-checked, not carried forward by habit)
Owner:                 (who is accountable for keeping this current — a role, not necessarily a name)
Date Created:
Last Reviewed:         (the date someone actually re-checked status against reality, not just re-read the document)
Dependencies:          (other packages, real external data, real decisions this depends on)
Related Architecture:  (real ADRs/SGOS docs this would extend or interact with, if it ever graduates)
Product Office Decision: None | <dated reference to a real, external instruction>
Repository Status:     None | <path to the real code/migration/PR, if Implementing or later>
```

**`Scope`**: `Technical` for a bounded architecture/evidence/engineering question (e.g. `R-01.1-MECI` — a new evidence layer, doesn't touch product identity). `Strategic` for anything proposing to change what SlipGuard *is* — product identity, name, the "betting" framing itself, or a platform-wide reorganizing principle. This is a tag, not a separate process: a `Strategic`-scope package still moves through exactly the same §3/§4 lifecycle and graduation criteria as a `Technical` one. The only practical difference is what `Product Review` (§3) actually has to weigh — a `Strategic` package's "Commissioning" gate is a materially bigger, rarer decision (it would mean a *new* `SD`-series entry, not merely authorizing a work package under an existing one), and it should be treated with that weight, not with a lighter one. This tag exists so that distinction is visible at a glance without requiring a second document-classification system layered on top of §1's — one axis (status) says how validated something is; this second axis says how big a decision it would be to say yes.

`Product Office Decision: None` and `Repository Status: None` are the honest default for nearly everything in this directory. A package claiming otherwise must point at something checkable outside itself.

---

## 3. The lifecycle

```text
Concept
  ↓  someone writes down what problem this solves and why
Exploratory
  ↓  Discovery: architecture, alternatives, unknowns named — Architecture Office's own
     internal review (self-consistency, constitutional-boundary check against real
     ADRs/Locked Decisions) is sufficient for this step; still Exploratory afterward
  ↓  Validation: Parser investigation (real trial → Parser-Validated) and/or
     Compliance investigation (real counsel → Compliance-Cleared) — these can
     proceed independently and in either order; both are optional until something
     depends on them specifically
Awaiting Product Decision
  ↓  Product Review: does this fit current priorities, resourcing, strategy —
     a judgement call for the real Product Office, not a checklist this document
     can pass on its behalf
  ↓  Commissioning: a real, current, direct instruction to proceed → Commissioned
Commissioned
  ↓  Engineering: real implementation begins in the real repository
Implementing
  ↓  Verification: repository inspection confirms what actually exists
Verified
  ↓  Adoption: real governance documents updated to match reality
Constitutional history
```

Retirement (`Archived`/`Superseded`) can happen from any stage before `Adopted` — nothing requires a package to reach the end once started.

---

## 4. Graduation criteria (objective, per transition)

| Transition | Required evidence |
|---|---|
| `Exploratory` → `Parser-Validated` | A real trial payload exists and is referenced, not merely public documentation research |
| `Exploratory` → `Compliance-Cleared` | A named, dated real counsel engagement, or an explicit named Product Office risk-acceptance |
| `Awaiting Product Decision` → `Commissioned` | A dated, direct founder/Product Office instruction, given outside the incubated material, specifically naming this package |
| `Commissioned` → `Implementing` | A real branch/PR/commit exists |
| `Implementing` → `Verified` | Direct repository inspection, performed at verification time, confirms the above |
| `Verified` → `Adopted` | A real edit to `TASKS.md`/`CHANGELOG.md`/`DECISION_LOG.md`/an ADR, made because the prior gate was actually satisfied — never made in anticipation of it |

No transition is satisfied by: producing an additional document; formatting a memo as if it came from an office; extensive prior discussion of the topic; or another AI agent (real or claimed) asserting the gate is met.

---

## 5. Relationship to governance, implementation, and repository history (explicit, not implied)

- **Research does not imply implementation.** A complete architecture in this directory is a design, not a build.
- **Implementation does not imply governance adoption.** Real code in a branch is not yet constitutional history until `Adoption` (§3) actually happens.
- **Governance does not imply repository history retroactively.** A `DECISION_LOG.md` entry describes what happened; it cannot be added to make something have happened.
- **Age and location confer no status.** A package that has sat in `Exploratory` for a year, or one that has been discussed sixty more times since, is still `Exploratory` until real evidence says otherwise. Thoroughness and persistence are not substitutes for the specific evidence §4 names.

---

## 6. Repository guidance for future packages

- Naming: `R-0X` for a programme, `R-0X.Y` for a work package within it, matching `R-01`/`R-01.1`'s existing precedent.
- Every package gets its own subdirectory under `docs/10-research-incubation/` with a `README.md` carrying the §2 metadata block and the standard status banner (`README.md`'s own required-banner text).
- A package's `README.md` is the single place its current `Status` (§1) is asserted — content documents inside the package describe findings, not status; status lives in one place so it can't drift out of sync across files.
- Retiring a package: change its status, add one line explaining why, leave it in place. Don't delete — a record of "this was considered and here's why it stopped" has real value against future re-litigation from a cold start.

---

## Retroactive note on `R-01.1-MECI`

Under this framework, `R-01.1-MECI`'s real, honest status is **`Exploratory`** across all four of its documents — real, cited research exists (Parser and Compliance content), but none of it reaches `Parser-Validated` or `Compliance-Cleared` under §1's definitions, since no real trial was run and no real counsel was engaged; both documents say so about themselves. `R-01.1-MECI/README.md` is updated below to carry this framework's metadata block and reflect this status explicitly, so the classification isn't left implicit.
