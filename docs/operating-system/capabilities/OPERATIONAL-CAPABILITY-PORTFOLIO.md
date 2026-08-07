# Operational Capability Portfolio v1.0

## Provenance

Produced under CEP-003 ("Capability Portfolio Baseline"), 2026-08-06, at Product
Office direction, closing the Capability Evaluation Programme (CEP-001 through
CEP-002) and transitioning the Design Capability System from evaluation mode to
operational mode. This document is the Product Office baseline for all future
SlipGuard capability decisions. No further capability evaluation work is
authorized after this document without a new, explicit Product Office
directive.

This document does not itself install, approve, or reject anything beyond what
`CAPABILITY-REGISTRY.md` already records as of this date. It consolidates and
cross-references that Registry (and `DESIGN-CAPABILITY-MATRIX.md`,
`SKILL-ROUTING.md`, `CAPABILITY-ADOPTION-STANDARD.md`); it does not supersede
them. Where this document and the Registry ever appear to disagree in future,
the Registry is authoritative, per its own Registry Rules — this document
should be corrected to match, not the reverse.

**Scope.** This Portfolio covers the nine capabilities tracked under the
Design Capability System (Registry / Matrix / Routing / Standard): Frontend
Design, Design Taste Frontend, Emil Design Engineering, Experience Architect,
Brand Governor, Design QA, Capability Auditor, UI/UX Pro Max, Impeccable. It
does not cover SlipGuard's pre-existing, first-party repository skills
(`laravel-engineering`, `pest-verification`, `security-authorization`, etc.)
— those are native project tooling, not external capabilities subject to
`CAPABILITY-ADOPTION-STANDARD.md`. See **Appendix: Adjacent Governance** below
for two tools (21st.dev MCP, Figma MCP) that are real, approved, in-use
capabilities but were never brought into this Registry — a gap this document
surfaces but does not close.

---

## Part 1 — Capability Portfolio

### Active Pilot (3)

#### Emil Design Engineering

| Field | Value |
|---|---|
| Provider | Emil Kowalski |
| Function | Motion and interaction craft |
| Status | **Active Pilot** |
| Evidence | `CEP-001` (evaluation, delivered 2026-08-06, recommendation: proceed to pilot). `CEP-002` (bounded, advisory-only, read-only pilot against `resources/views/livewire/betting-slips/report.blade.php`): installation confirmed on disk and via live skill discovery, explicit invocation confirmed via the `Skill` tool. Three genuine findings produced, all already covered by `MOTION_SYSTEM.md` and existing codebase convention — no finding was novel beyond current SlipGuard standards. The capability's distinctive material (custom easing curves, spring physics, blur-masked crossfades, decorative stagger, `clip-path` reveals) was evaluated and not adopted — several items directly conflict with `MOTION_SYSTEM.md`'s Locked Decisions and Forbidden Animation Patterns. |
| Activation trigger | Per `SKILL-ROUTING.md`: only after static hierarchy and interface states are approved for the interface in question; advisory only; every recommendation filtered against `MOTION_SYSTEM.md` before use. |
| Exit criteria — to Approved with Restrictions | A second bounded pilot, against a different interface, that produces at least one finding materially beyond what `MOTION_SYSTEM.md` plus a disciplined manual review would already catch — demonstrating value the capability itself adds, not just a systematic checklist restating existing docs. Review Date (2026-09-05) reached with a recorded review. |
| Exit criteria — to Retired | Repeated pilots continue to show zero incremental value over manual review, or any pilot surfaces a recommendation adopted without being filtered against `MOTION_SYSTEM.md`/Locked Decisions. |

#### UI/UX Pro Max

| Field | Value |
|---|---|
| Provider | nextlevelbuilder |
| Function | Structured design reference and UX guidance |
| Status | **Active Pilot** |
| Evidence | `CEP-001` (evaluation). `CEP-001A` (portfolio optimisation, Phase 1 authorization — verified live: local, offline guideline database, no network call, no files written, per `CLAUDE.md`'s Tool Invocation Policy). Governed doubly: by this Registry's pilot tracking and by `CLAUDE.md`'s pre-existing Tool Invocation Policy (verified 2026-07-25, `TOOL-UX-001`) — the two are consistent, not conflicting. |
| Activation trigger | During structured UX research/reference lookups, per `SKILL-ROUTING.md`; every recommendation treated as advisory until reconciled against repository evidence; never a source of committed code or assets (`CLAUDE.md`). |
| Exit criteria — to Approved with Restrictions | Demonstrated, disclosed use across more than one qualifying commission (per `CLAUDE.md`'s Mandatory Specialist Design Capability Invocation rule) with outcomes recorded (adopted/modified/rejected, with reasoning) each time. Review Date (2026-09-05) reached with a recorded review. |
| Exit criteria — to Retired | Recommendations are found to have been silently adopted without the required evaluation-and-disclosure step, or the tool's offline/no-network behaviour changes without re-verification. |

#### Impeccable

| Field | Value |
|---|---|
| Provider | pbakaus |
| Function | Interface critique and anti-slop review |
| Status | **Active Pilot** |
| Evidence | `CEP-001` (evaluation). `CEP-002` (bounded pilot: `critique` run against `resources/views/livewire/journal/index.blade.php`, degraded/scoped-down mode, disclosed — its own documentation asserts a non-negotiable default workflow of mandatory parallel sub-agents, live-server browser injection, and a persisted `.impeccable/` snapshot, which this repository's governance does not accept from a capability itself; the pilot ran only in an explicitly scoped-down mode). |
| Activation trigger | After visual direction exists, per `SKILL-ROUTING.md`; advisory only; no file it touches may be modified by it directly; its own default (unscoped) workflow must never run. |
| Exit criteria — to Approved with Restrictions | A second scoped-down pilot against a different interface with no attempt (successful or not) to invoke its default unscoped workflow, and genuine critique value demonstrated. Review Date (2026-09-05) reached with a recorded review. |
| Exit criteria — to Retired | Any pilot in which its default workflow runs (even partially) without explicit per-invocation scoping-down, or repeated pilots show no critique value beyond existing Design QA practice. |

### Deferred (1)

#### Design Taste Frontend

| Field | Value |
|---|---|
| Provider | Leonxlnx |
| Function | Marketing-page visual direction |
| Status | **Deferred** |
| Evidence | `CEP-001` (evaluation, delivered 2026-08-06, recommendation: Defer). `DESIGN-CAPABILITY-MATRIX.md`'s own capability boundary excludes it as primary for dashboards, data tables, complex workflows, multi-step product interfaces, analysis reports, and operational interfaces — nearly all of SlipGuard's actual current surface area (Dashboard, Planner, Builder, Risk Reports, Journal, History). |
| Activation trigger | Product Office identifies a concrete, bounded public marketing/landing-page visual-exploration task — its one documented-fit use case — and commissions a pilot for it specifically. |
| Evidence required before promotion | A bounded pilot against that specific marketing/landing page, run with the same discipline as `CEP-002` (advisory-only, read-only, findings checked against `docs/05-ux/` and `docs/design-intelligence/PROJECT-DESIGN-CONTEXT.md`), producing genuinely adoptable value. |
| Exit criteria — to Rejected | If, when the trigger occurs and a pilot is run, findings are dominated by patterns `PROJECT-DESIGN-CONTEXT.md`'s Prohibited Visual Patterns already forbids (generic SaaS gradients, excessive cards, hero sections copied from unrelated products), the deferral should convert to a formal Rejected classification rather than remain open-ended. |

### Candidate (5)

#### Frontend Design

| Field | Value |
|---|---|
| Provider | Anthropic |
| Function | Frontend visual implementation |
| Status | **Candidate**, with a standing blocking precondition |
| Evidence | No `CAPABILITY-ADOPTION-STANDARD.md` gate evaluation has ever been delivered for this capability. `CEP-001A` separately recorded a portfolio-level classification of "Excluded" (automatic-activation behaviour and aesthetic register conflict with repository governance) — a distinct judgment, not a completed evaluation. "Excluded" is not a recognized status in this Standard's Classification list; it is preserved verbatim as CEP-001A's own term. This capability is therefore reported as Candidate, not Deferred or Rejected — neither would be honestly earned without the gate evaluation it has never received. |
| Activation trigger for a full evaluation | The automatic-activation conflict identified in `CEP-001A` is resolved first (i.e., a way to use this capability without its documented automatic-activation behaviour is confirmed to exist), **and** Product Office issues a new CEP directive naming it for evaluation. |
| Evidence required before promotion | A complete `CAPABILITY-ADOPTION-STANDARD.md` five-gate evaluation (Factuality, Maturity, Scalability, Governance, Return on Complexity), delivered as a CEP record, replacing "No gate evaluation delivered" in the Registry. |

#### Experience Architect, Brand Governor, Design QA, Capability Auditor

All four (provider: Tsotsia) share an identical current record:

| Field | Value |
|---|---|
| Status | **Candidate** |
| Evidence | No evaluation delivered. Filesystem check for this Portfolio confirms `.claude/skills/{experience-architect,brand-governor,design-qa,capability-auditor}/` exist as **empty directories** — no `SKILL.md`, not discoverable, not invokable. Candidate status is accurate and currently self-enforcing: there is nothing installed to accidentally invoke. |
| Activation trigger | Product Office issues a new CEP directive naming the specific capability for evaluation. No standing blocker exists for any of the four (unlike Frontend Design). |
| Evidence required before promotion | A complete `CAPABILITY-ADOPTION-STANDARD.md` five-gate evaluation delivered as a CEP record, followed by actual installation (currently absent) and a bounded pilot, in that order, per the Standard's own Installation Rule. |

### Approved / Approved with Restrictions (0)

No capability in this Registry currently holds Approved or Approved with
Restrictions status. This is reported plainly rather than rounded up: the
Capability Evaluation Programme completed its first full pass (evaluation →
pilot) for three capabilities, and every pilot's own exit criteria (above)
remain unmet as of this baseline. Promotion to Approved or Approved with
Restrictions requires a future, dated Product Office review event — it is not
a status this document confers by closing the programme.

### Rejected / Retired (0)

No capability has been formally Rejected or Retired. Frontend Design's
CEP-001A "Excluded" classification functions similarly in practice (not on
the installation roadmap) but is deliberately not reported as Rejected here,
since no gate evaluation was ever completed for it — see its entry above.

---

## Part 2 — Operational Capability Policy

**Authorised for normal SlipGuard work, within stated restrictions only:**
Emil Design Engineering, UI/UX Pro Max, Impeccable (all Active Pilot). None of
the three is authorised for unrestricted use — each remains bound by its
Registry Restrictions column, `DESIGN-CAPABILITY-MATRIX.md`'s Capability
Boundaries, and `SKILL-ROUTING.md`'s activation conditions, all unchanged by
this Portfolio. Every invocation remains advisory; none may modify a file
directly, approve its own output, or be treated as adopted without explicit
reconciliation against SlipGuard's own governance and UX Constitution
documents, per `CLAUDE.md`'s Mandatory Specialist Design Capability
Invocation rule and this Registry's own restrictions.

**Require explicit Product Office approval before any use:** Frontend Design,
Design Taste Frontend, Experience Architect, Brand Governor, Design QA,
Capability Auditor (Candidate or Deferred). None of these six may be invoked
for SlipGuard work — none is even installed except as an inert, empty
placeholder directory in four cases. Product Office approval here means
issuing a new CEP directive per each capability's Activation Trigger in Part
1, not a general blanket authorization.

**Prohibited:** No capability tracked in this Registry currently holds a
formal Rejected or Retired status, so this Portfolio does not report a
prohibition list within the Registry's own nine capabilities. One standing,
pre-existing prohibition from outside this Registry is restated here for
completeness, since it is directly relevant and already binding: **Indeed
MCP** is explicitly out of scope for SlipGuard and must not be invoked for
SlipGuard work, per `CLAUDE.md`'s Tool Invocation Policy. Frontend Design
(Anthropic) is not formally Rejected but is not on the installation roadmap
pending resolution of its `CEP-001A` automatic-activation conflict — treat as
prohibited in practice until that precondition is resolved and a full
evaluation is delivered.

**Standing rules unchanged by this Portfolio, restated for operational
clarity:**
- Neither Active Pilot nor any other capability status ever overrides a
  Product Office, Architecture Office, Compliance Office, or Parser Office
  decision (`CLAUDE.md`).
- No capability may install itself, approve itself, approve another
  capability, approve product scope, or approve release
  (`DESIGN-WORKFLOW.md`'s Capability Participation section).
- Installation does not equal approval; Candidate/Deferred status does not
  grant authority (`SKILL-ROUTING.md`).

---

## Part 3 — Consistency Verification

Checked per CEP-003's instruction: `CAPABILITY-REGISTRY.md`,
`DESIGN-CAPABILITY-MATRIX.md`, `SKILL-ROUTING.md`,
`CAPABILITY-ADOPTION-STANDARD.md`.

**Inconsistencies found and corrected (all within `CAPABILITY-REGISTRY.md`
and `CAPABILITY-ADOPTION-STANDARD.md` — the only two documents that name
Status/Classification values literally; `DESIGN-CAPABILITY-MATRIX.md` and
`SKILL-ROUTING.md` were checked and reference Registry status only
generically, never by the literal terms below, so neither required a
change):**

1. **Terminology gap.** `CAPABILITY-ADOPTION-STANDARD.md`'s Classification
   list and `CAPABILITY-REGISTRY.md`'s Status Definitions both used "Pilot
   only" and "Experimental" — terms CEP-003's own directive does not use
   ("Active Pilot", "Deferred"), and which this Portfolio is required to
   classify against. Left unreconciled, this Portfolio would have introduced
   status values the governing Standard didn't recognize. **Fixed:** both
   documents renamed "Pilot only" → "Active Pilot" (also resolving a
   pre-existing mismatch between the Registry's "Active Pilots" section
   heading and its own "Pilot only" cell values) and "Experimental" →
   "Deferred" (a semantic correction — no entry has ever used the
   Experimental meaning, while a real need for "evaluated, consciously
   parked" existed and had nowhere accurate to be recorded). Provenance notes
   added at both edit sites.

2. **Design Taste Frontend's status contradicted the Registry's own stated
   rule.** The Capability Evaluation Queue's header states Status "remains
   Candidate... until an Evaluation Record entry replaces 'Not yet
   evaluated.'" Design Taste Frontend's Evaluation Record already read "CEP-001
   delivered — recommendation: Defer," but its Status cell still read
   Candidate. **Fixed:** moved to a new Deferred Capabilities section with
   Status Deferred, a real calendar Review Date (2026-09-05, per
   `CAPABILITY-ADOPTION-STANDARD.md`'s Review and Retirement section, which
   requires a real date once a capability leaves Candidate status), and an
   explicit Activation Trigger.

3. **Frontend Design's Evaluation Record cell was self-contradictory within
   the same document.** The cell read "Not yet evaluated" while an adjacent
   note (CEP-001A, same document, same date) described a portfolio-level
   Excluded classification for the same capability. **Fixed:** reworded the
   cell to state plainly that no formal gate evaluation has been delivered
   and that CEP-001A's classification is a distinct, non-equivalent judgment
   — without inventing a formal evaluation that never happened and without
   changing the underlying Status (remains Candidate, per CEP-001A's own
   prior, deliberate reasoning, which this document did not find grounds to
   overturn).

**Checked and found already consistent (no change made):**

- `DESIGN-CAPABILITY-MATRIX.md`'s Capability Boundaries for Emil Design
  Engineering, UI/UX Pro Max, and Impeccable already match their Active Pilot
  restrictions in the Registry.
- `SKILL-ROUTING.md`'s activation rules for all nine capabilities already
  presume Registry status governs invocability and don't claim standing
  authority beyond it.
- `CAPABILITY-ADOPTION-STANDARD.md`'s Installation Rule, Evidence Rule, and
  Review and Retirement section are consistent with how the Registry actually
  recorded CEP-001, CEP-001A, and CEP-002.
- The four empty Tsotsia-capability skill directories on disk
  (`experience-architect`, `brand-governor`, `design-qa`,
  `capability-auditor`) do not contradict their Candidate status — they are
  not discoverable or invokable, so nothing is actually installed ahead of
  evaluation.

**Not fixed — surfaced only, per CEP-003's "do not modify unless an actual
inconsistency exists":**

- `21st.dev MCP` and `Figma MCP` are real, approved-for-reference-only
  capabilities under `CLAUDE.md`'s Tool Invocation Policy but were never
  brought into this Registry. This is a scope gap, not a contradiction — the
  two governance tracks don't say incompatible things about the same
  capability, since 21st.dev/Figma simply aren't mentioned in the Registry at
  all. Left as a recommendation (see Appendix) rather than an edit, since
  folding them in changes the Registry's scope, which is a Product Office
  call, not something to execute unprompted under a "verify, don't expand"
  instruction.

---

## Appendix: Adjacent Governance (out of Registry scope, noted for completeness)

Two tools are already approved and in active use under `CLAUDE.md`'s
Tool Invocation Policy (verified 2026-07-25, `TOOL-UX-001`) but have no entry
in `CAPABILITY-REGISTRY.md`:

- **21st.dev MCP** — approved for design reference/inspiration only; verified
  live (returns real catalog data over the network); results are
  React/shadcn install commands that must never be installed or copied
  directly (would silently introduce a second frontend framework, breaking
  the Blade+Livewire Locked Decision).
- **Figma MCP** — connected, not yet exercised beyond connectivity; same
  reference-only constraint applies when used.

**Recommendation, not executed here:** if Product Office wants a single
source of truth for all design-adjacent capabilities now that the Design
Capability System is operational, these two should be added to the Registry
in a future, explicitly scoped pass. This Portfolio does not do so, since
CEP-003 asked for verification of the existing four documents, not an
expansion of what they track.
