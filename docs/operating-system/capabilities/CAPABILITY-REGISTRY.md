# Tsotsia Capability Registry

## Registry Rules

**Corrected (Constitutional Review, Finding 3 — Pre-Evaluation Restrictions):**
the first rule below previously read "Only evaluated capabilities appear in
this registry," which directly contradicted this same document's own
Candidate definition ("Identified for evaluation; not authorized...") and the
fact that every entry currently in the Capability Evaluation Queue is
Candidate status with no recorded evaluation. Corrected to describe what is
actually true.

- Only capabilities that have entered the evaluation process defined in
  `CAPABILITY-ADOPTION-STANDARD.md` appear in this registry — appearing here
  does not mean evaluation is complete; see Status and each entry's
  Evaluation Record.
- Installation does not equal approval.
- Each project records its own installed version.
- An approved capability may still have project-specific restrictions.
- Provider recommendations remain subordinate to project authority.
- **(Constitutional Review, Finding 4)** Review date is a condition only
  while a capability is Candidate status. Once a capability leaves Candidate
  status it must carry a real calendar date, owned by Product Office — see
  `CAPABILITY-ADOPTION-STANDARD.md`'s Review and Retirement section for the
  enforcement rule and the consequence of a missed review. This registry does
  not restate that rule so it cannot drift out of sync with it.
- **(Constitutional Review, Finding 5 — No Reconciliation Mechanism)** This
  Registry's Status is always authoritative over
  `DESIGN-CAPABILITY-MATRIX.md`'s Responsibility Matrix and
  `SKILL-ROUTING.md`'s activation rules for the same capability. If either
  document has not yet been updated to match a Status change recorded here,
  the stale entry never overrides what is recorded here — a capability
  Rejected, Retired, or reverted to Candidate (see the Review rule above) may
  not be invoked regardless of what the Matrix or Routing document still
  says. Whoever changes a Status in this table must check both documents for
  references to that capability's name and update or flag them in the same
  change.
- **(Constitutional Review, Finding 6 — No Multi-Repository Custody Model)**
  This Registry, like `CAPABILITY-ADOPTION-STANDARD.md`, is SlipGuard's
  local copy — see that document's Custody section. The Capability
  Evaluation Queue and Installation Record below reflect SlipGuard's own
  evaluation and installation history only; a capability's status here does
  not imply, and cannot be assumed to match, its status in another Tsotsia
  project's own registry.

## Status Definitions

**Renamed (CEP-003, 2026-08-06 — Operational Capability Portfolio v1.0):**
"Pilot only" is renamed **Active Pilot** (matching this document's own
"Active Pilots" section heading below — the two previously didn't match).
"Experimental" is replaced by **Deferred** (evaluated and consciously
parked, rather than "research permitted" — no entry in this registry has
ever used the Experimental meaning; see `CAPABILITY-ADOPTION-STANDARD.md`'s
Classification section for the full rationale). Both are renames/refinements
of an existing status, not a new gate.

| Status | Meaning |
|---|---|
| Candidate | Identified for evaluation; not authorized for installation or operational use |
| Approved | Proven and permitted for defined use |
| Approved with restrictions | Permitted only within stated boundaries |
| Active Pilot | May be tested in the named pilot repository |
| Deferred | Evaluated; consciously parked pending a named future trigger, not rejected on the merits |
| Rejected | Not permitted |
| Retired | Previously used but no longer permitted |

## Capability Evaluation Queue

Entries in this section are candidates awaiting evaluation. Inclusion does not
authorize installation, invocation, production use or cross-project rollout.

**Corrected (Constitutional Review, Finding 3 — Pre-Evaluation Restrictions):**
every entry below already carried a Pilot repository and Restrictions before
any Factuality, Maturity, Scalability, Governance, or Return-on-Complexity
finding had been recorded for it, which is out of sequence with
`CAPABILITY-ADOPTION-STANDARD.md`'s own Installation Rule (evaluate, then
register, then pilot). No evaluation is invented here to close that gap —
none has occurred. Instead, an **Evaluation Record** column makes that
honestly explicit, and Pilot/Restrictions are redefined as **proposed**
scope — the boundaries evaluation would apply *if* the capability is
approved — not a claim that evaluation already happened. None of these rows
authorize invocation; Status remains Candidate for all of them until an
Evaluation Record entry replaces "Not yet evaluated."

| Capability | Function | Provider | Status | Evaluation Record | Proposed Pilot | Proposed Restrictions | Review date |
|---|---|---|---|---|---|---|---|
| Frontend Design | Frontend visual implementation | Anthropic | Candidate | No `CAPABILITY-ADOPTION-STANDARD.md` gate evaluation delivered. CEP-001A separately recorded a portfolio-level classification (Excluded) below — that is a distinct judgment, not a completed Factuality/Maturity/Scalability/Governance/Return-on-Complexity evaluation, so this cell is not upgraded to a delivered Evaluation Record on its basis | SlipGuard | Cannot redefine product or brand direction | After evaluation |
| Experience Architect | Journey and interface-state definition | Tsotsia | Candidate | Not yet evaluated | SlipGuard | Must not add product scope | After evaluation |
| Brand Governor | Project identity protection | Tsotsia | Candidate | Not yet evaluated | SlipGuard | Requires project design context | After evaluation |
| Design QA | Implementation audit | Tsotsia | Candidate | Not yet evaluated | SlipGuard | Must audit against accepted requirements | After evaluation |
| Capability Auditor | External capability evaluation | Tsotsia | Candidate | Not yet evaluated | SlipGuard | Does not install or approve capabilities independently | After evaluation |

**(CEP-001A, 2026-08-06):** Anthropic Frontend Design was portfolio-classified **Excluded** — its automatic-activation behaviour and documented aesthetic register conflict with this repository's own governance and design constitution. It remains listed above at Candidate status (evaluation is not itself reversed by a portfolio classification), but is not on the installation roadmap; re-opening it requires the automatic-activation question to be resolved first, per CEP-001A. **(CEP-003 note, 2026-08-06):** "Excluded" is not one of this Standard's recognized classifications (`CAPABILITY-ADOPTION-STANDARD.md`'s Classification section); it is retained here verbatim as CEP-001A's own term rather than silently reinterpreted. For Operational Capability Portfolio purposes this capability is reported as Candidate with a standing blocking precondition, not as Deferred or Rejected — neither term would be honestly earned without the gate evaluation this capability has never received.

## Deferred Capabilities

**(CEP-003, 2026-08-06):** Design Taste Frontend moves here from the Capability Evaluation Queue. Its own Evaluation Record entry ("CEP-001 delivered — recommendation: Defer") had already replaced "Not yet evaluated," which by the Queue's own stated rule above means Candidate status should already have changed; it had not, until this correction. Deferred is a real, evaluated-and-parked outcome (per `CAPABILITY-ADOPTION-STANDARD.md`'s Classification section), not an unevaluated Candidate — it now carries a real calendar Review Date rather than a condition, per that document's Review and Retirement section.

| Capability | Function | Provider | Status | Evaluation Basis | Deferral Reason | Activation Trigger | Review Date |
|---|---|---|---|---|---|---|---|
| Design Taste Frontend | Marketing-page visual direction | Leonxlnx | Deferred | `CEP-001` (evaluation, delivered 2026-08-06) | `DESIGN-CAPABILITY-MATRIX.md`'s own boundary excludes it from dashboards, data tables, complex workflows, multi-step product interfaces, analysis reports, and operational interfaces — which is nearly all of SlipGuard's actual surface area today | Product Office commissions a bounded pilot against a specific public marketing/landing-page visual-exploration task (its one documented-fit use case) | 2026-09-05 |

**(CEP-002, first attempt, 2026-08-06):** Emil Design Engineering initially remained at Candidate status unchanged — `CEP-002`'s directive stated it had been installed, but direct filesystem verification (`find` across every plausible plugin/skill location) found no trace of it anywhere in this environment at that time. Per the Standard's own Factuality gate and `CEP-002`'s explicit "do not assume success" instruction, installation was not assumed from the directive's own claim, and no pilot was attempted.

**(CEP-002, completed, 2026-08-06):** Re-verification following actual execution of the installation command found `.claude/skills/emil-design-eng/SKILL.md` present on disk (project-local, `skills-lock.json` records source `emilkowalski/skill`, GitHub, `skillPath: skills/emil-design-eng/SKILL.md`), listed in Claude Code's live skill-discovery output, and successfully invokable by name via explicit `Skill` tool call (full `SKILL.md` content returned, arguments accepted). A bounded, advisory-only, read-only pilot was then run against `resources/views/livewire/betting-slips/report.blade.php`'s disclosure panels, chevron indicators, and exit-action buttons — an interface with existing motion/state-transition surface. No project file was modified. Findings: (1) the report-details disclosure chevron lacks the `transition-transform` class its sibling methodology chevron carries in the same file; (2) the file's three `x-show`/`x-cloak` disclosure panels carry no `x-transition` at all, unlike the codebase's own established convention (`dropdown.blade.php`, `modal.blade.php`, `public-nav.blade.php`) and unlike `MOTION_SYSTEM.md`'s own stated expectation for progressive-disclosure panels; (3) the exit-action buttons transition colour only, missing the `active:scale-95` press feedback `MOTION_SYSTEM.md`'s Micro-Interactions section requires and that `theme-toggle.blade.php` already implements elsewhere in the codebase. All three findings are genuine and independently verifiable, but all three are implementation gaps against standards `MOTION_SYSTEM.md` and this codebase's own existing components already state — none is a new principle beyond what a manual read of `MOTION_SYSTEM.md` plus a grep of sibling components would have surfaced; Design QA's existing "motion restraint" mandate (`DESIGN-CAPABILITY-MATRIX.md`) already covers this ground. The skill's distinctive material — custom cubic-bezier easing curves, spring/momentum physics, `filter: blur()` crossfade masking, list stagger, decorative `clip-path` reveals — was evaluated and not adopted: several (spring/bounce easing, list stagger, decorative motion) directly conflict with `MOTION_SYSTEM.md`'s Locked Decisions and Forbidden Animation Patterns, and the remainder would introduce undocumented new motion patterns requiring the Frontend Work Rule's Gap Rule before any use. Recommendation: **Pilot only** — moved below; see `DESIGN-CAPABILITY-MATRIX.md`'s existing "Emil Design Engineering" boundary and `SKILL-ROUTING.md`'s existing activation rule for this capability, both checked and found already consistent with this status (no update required to either).

## Active Pilots

**(CEP-001A, 2026-08-06):** Product Office confirmed proceeding to Phase 1 of the recommended installation roadmap. Per the Installation Rule, evaluation and registration are complete for this capability (`CEP-001`, `CEP-001A`); it now moves to controlled pilot. Inclusion here does not itself authorize any use beyond the pilot's own stated boundary — capability boundaries, activation rules, and authority ordering remain governed by `DESIGN-CAPABILITY-MATRIX.md` and `SKILL-ROUTING.md` exactly as before, unchanged by this status move.

| Capability | Function | Provider | Status | Pilot Repository | Restrictions | Evaluation Basis | Pilot Started | Review Date |
|---|---|---|---|---|---|---|---|---|
| UI/UX Pro Max | Structured design reference and UX guidance | nextlevelbuilder | Active Pilot | SlipGuard | Validate stack relevance; recommendations are not authoritative; reference and inspiration only, never a source of committed code (`CLAUDE.md` Tool Invocation Policy) | `CEP-001` (evaluation), `CEP-001A` (portfolio optimisation, Phase 1 authorization) | 2026-08-06 | 2026-09-05 |
| Impeccable | Interface critique and anti-slop review | pbakaus | Active Pilot | SlipGuard | Advisory only, no file it touches may be modified by it directly; must never run its own default workflow (mandatory parallel sub-agents, live-server browser injection, persisted `.impeccable/` snapshot) without explicit per-invocation scoping down — its own documentation asserts that default as non-negotiable, which this repository's governance does not accept from a capability itself | `CEP-001` (evaluation), `CEP-002` (bounded pilot: `critique` run against `resources/views/livewire/journal/index.blade.php`, degraded/scoped-down mode, disclosed) | 2026-08-06 | 2026-09-05 |
| Emil Design Engineering | Motion and interaction craft | Emil Kowalski | Active Pilot | SlipGuard | Use only after static hierarchy and interface states are approved (`DESIGN-CAPABILITY-MATRIX.md`); advisory only, no file it touches may be modified by it directly; its default vocabulary includes techniques `MOTION_SYSTEM.md` forbids outright (spring/bounce easing, decorative stagger, decorative motion) or has not documented (custom cubic-bezier curves, blur-masked crossfades, `clip-path` reveals) — every recommendation must be filtered against `MOTION_SYSTEM.md` and the Frontend Work Rule's Gap Rule before any adoption, never assumed safe by default | `CEP-001` (evaluation), `CEP-002` (bounded pilot: advisory review of disclosure-panel, chevron, and button motion in `resources/views/livewire/betting-slips/report.blade.php`, read-only, no files modified — all findings were compliance gaps against existing `MOTION_SYSTEM.md`/codebase convention, not novel beyond it) | 2026-08-06 | 2026-09-05 |

## Installation Record

| Project | Capability | Source | Version/commit | Installed date | Installed by | Status |
|---|---|---|---|---|---|---|
| SlipGuard | Emil Design Engineering | `emilkowalski/skill` (GitHub), `skills/emil-design-eng/SKILL.md`, project-local `.claude/skills/emil-design-eng/SKILL.md` | `skills-lock.json` records `computedHash: 41b0a4dc1a27164fe297845a6c6850a39e9242c42c9be999967fcee9df2c5974`; local sha256 of the installed file does not reproduce this value under sha256/sha1/md5/git-hash-object — algorithm unconfirmed, flagged as an open verification gap, not treated as a factuality failure given the stronger direct evidence (file present, correct frontmatter `name`/`description`, listed in live skill discovery, successfully invoked) | 2026-08-06 | User/agent | Installed (project-local; untracked in git as of this record) |

## Central Capability System Integration

**(`CAPSYS-P2`, 2026-08-09):** A standalone Capability System repository now
holds canonical, cross-project capability identity, provenance, and
governance contracts — remote `SilvestrLee/capability-system` (local
operator path `/Users/silvestr/Documents/capability-system` where present).
This Registry remains SlipGuard's own authoritative record of status,
evaluation, and pilot history exactly as before, unchanged by this
integration — see this document's own Registry Rules above. What changes is
that SlipGuard now also carries real, project-local **Project Adapter**
records expressing this Registry's own values (status, restrictions) in the
central system's shared vocabulary, so a capability's identity is never
duplicated or redefined locally. These records, and their supporting
evidence, live under `docs/operating-system/capabilities/adapters/` (see
that directory's own `README.md`) — not in this file, and not in the
central repository. The central repository does not gain any authority
this Registry did not already have; it supplies shared identity only, per
its own `governance/CENTRAL_GOVERNANCE.md`.

The first, and currently only, capability with a real adapter record is
UI/UX Pro Max (`docs/operating-system/capabilities/adapters/ui-ux-pro-max.adapter.yaml`),
mapping this Registry's "Active Pilot" status to the central vocabulary's
`PILOT` value.
