# RC1 Launch Blocker Closure Plan

| Field | Value |
|---|---|
| Directive | `PO-RC1-001`, Product Office, direct founder instruction, 2026-08-04. **`PO-RC1-002`** (2026-08-04) subsequently authorized Engineering to execute the low-risk, configuration/content-only items below — status updated in place, evidence added; see each item's row. |
| Type | **Planning document only.** No implementation, no infrastructure change, no legal document authored, no code change — converts the accepted backlog (`PO-MVP-005`, `OO-RC1-001`, `CO-MVP-001`) into an executable plan. Per the directive's own singular Deliverable ("Produce a single document"), this plan is what's delivered now; execution of each item remains a separate, subsequent action once this plan itself is reviewed. |
| Inputs accepted as authoritative, not re-audited | `docs/product/MVP_LAUNCH_READINESS_AUDIT.md` (`PO-MVP-005`), `docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md` (`OO-RC1-001`), `docs/09-compliance/CO-MVP-001-COMPLIANCE-TRUST-CERTIFICATION.md` (`CO-MVP-001`, with directives). No new discovery was performed — every item below traces to a finding already recorded in one of those three documents. |
| Status | Delivered. Awaiting Product Office review before any item below is executed. |

---

# Executive Summary

Every item below is drawn directly from the three accepted source documents — nothing new was discovered, per the directive's own instruction not to perform another audit. **30 backlog items** are consolidated into one closure plan, grouped by owning office, each classified into exactly one of three stages. *(Correction, `PO-RC1-002`: this section originally miscounted as 22/7/3/32 — the real row counts, verified directly against the tables below, are 20/7/3/30. Disclosed rather than silently fixed.)*

- **Must complete before RC1** — 20 items, **12 now closed** (10 via `PO-RC1-002` — all 7 Engineering items + 3 of 5 Compliance items; 2 more via `PO-CO-002` — `CO-01`/`CO-02`, real Terms of Service and Privacy Policy content). The convergence point where `PO-MVP-005`'s "NO GO today, GO WITH CONDITIONS realistically close" becomes an actual GO. **8 remain**: all 7 Operations items (`OP-01`–`OP-07`, no infrastructure exists yet — `PO-OPS-001` has since prepared execution-ready artifacts for these, see `docs/08-operations/RC1_OPERATIONS_PROGRAMME.md`) and 1 Product Office item (`PO-01`). All 5 Compliance items are now closed; qualified legal counsel sign-off on `CO-01`/`CO-02` remains a genuinely external condition, not an internal blocker.
- **Can complete before public launch** — 7 items. Real, tracked, but not RC1-blocking. Untouched by this pass.
- **Post-launch backlog** — 3 items. Explicitly deferred, not forgotten. Untouched by this pass.

**One reclassification made by this plan, directly authorized by the directive itself, not a judgment call this plan invents**: `CO-MVP-001`'s jurisdiction-naming request (`CO-06`) is no longer a Must-complete-before-RC1 blocker. The directive explicitly instructs *"use jurisdiction-neutral wording where appropriate until Product Office names launch markets"* — this converts what `CO-MVP-001` correctly flagged as an open blocker into a defined interim path, moved to Can-complete-before-public-launch below.

No architecture redesign, no MVP scope expansion, and no new product capability appears anywhere in this plan, per the directive's own explicit constraint.

---

# Must Complete Before RC1

## Engineering

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| EN-01 | ✅ **DONE** — Flip `MARKET_WIDE_PLANNER_ENABLED` to enable Capability B, per `PO-MVP-004`'s existing authorization (closes `R-12`) | Engineering | Critical | Trivial (single config value) | None — `PO-MVP-005` already confirmed the feature works | Flag set `true`; Capability B reachable and functional; full test suite still green | `.env.example:113` set `true` (was `false`), comment updated citing `PO-MVP-004`; local `.env` already had `true`; `artisan tinker` confirmed `config('slipguard-market-intelligence.enabled')` resolves `true`. **Not fully closed**: this changes the *default*, not a real production environment's value — that step depends on `OP-01` (no production environment exists yet) |
| EN-02 | ✅ **DONE** — Set and document minimum PHP `memory_limit` for development and production guidance (closes `R-10`'s dev-time symptom) | Engineering | Major | Trivial | Coordinated with Operations' `OP-01` for the real production PHP-FPM value | Test suite passes without OOM; documented, not tribal | `phpunit.xml` now sets `<ini name="memory_limit" value="1024M"/>` — fixes the actual dev/CI symptom, not just documentation. Added a "PHP memory limit" section to `docs/06-engineering/DATABASE_SETUP.md` cross-referencing the production 512M minimum already in `PRODUCTION_OPERATIONS_BLUEPRINT.md` §O-01. Full suite re-run clean: 682/682 executed tests passing, 0 failures |
| EN-03 | ✅ **DONE** — Resolved the two known `PublicPagesTest.php` failures (stale logo-motion assertions from the Static Product Mark decision) | Engineering | Minor | Trivial | None | Full suite green with zero unexplained failures | Both tests rewritten to assert the current, correct static behaviour (mirroring `GlobalShellTest`'s already-existing "product marks remain static" pattern) instead of the removed rotation mechanics. **One real correction made during verification**: the first rewrite incorrectly asserted absence of `reduceMotion: window.matchMedia`, which is still genuinely used by unrelated homepage features (the mobile drag-to-explore carousel, a number-counter animation) — caught by actually running the suite rather than assuming the fix was correct, then fixed to check only the truly rotation-specific tokens. Final suite run: **682 passed, 0 failed, 7 self-skipped (as designed), Pint clean** |
| EN-03b | ✅ **DONE** — Formally classified `DecisionJournalTest`'s archive-grouping flakiness | Engineering | Minor | Trivial | None | A written classification exists | Already recorded in `TASKS.md` (Decision Journal entry: "Polish Required... flakiness did not reproduce in this audit's own fresh run") and independently reconfirmed by `PO-MVP-005` §3 and this pass's own fresh run (no flake observed). No code fix attempted — correctly classified as intermittent/monitored, not a defect requiring a fix before RC1 |
| EN-04 | ✅ **DONE (config/docs only — real production value is `OP-05`'s job)** — Documented `SESSION_SECURE_COOKIE` (closes `R-09`'s root cause), `APP_ENV`/`APP_DEBUG` guidance | Engineering | Major | Trivial | Operations' `OP-05` sets the real production value | Variable can no longer be silently missed | `.env.example` now documents `SESSION_SECURE_COOKIE=false` (correct for local http) with an explicit comment that production (https) must set `true`, cross-referencing `OP-05`. `APP_ENV`/`APP_DEBUG` were already present in `.env.example`; no change needed there — the gap was specifically the undocumented cookie variable |
| EN-05 | ✅ **NO ACTION NEEDED — finding corrected.** A footer copyright/IP notice was believed missing (`CO-MVP-001` C-02/C-06 `CO-05`); direct re-verification found one already exists | Engineering | Minor | N/A | N/A | N/A | **Correction**: `resources/views/components/public-footer.blade.php:102` already renders `&copy; {{ now()->year }} SlipGuard` — the original audit's search for the literal `©` character and the word "copyright" missed the HTML-entity-encoded `&copy;`. `CO-MVP-001` corrected in place. No footer edit made for this item |
| EN-06 | ✅ **DONE** — Added age statement + Responsible Gambling signposting as static footer copy (Engineering execution of `CO-03`/`CO-04`'s content) | Engineering | Major | Small | None — implemented as static content, deliberately not a registration-flow change, to stay inside this directive's "must not alter existing workflows" constraint | Statement present, jurisdiction-neutral, no deposit-loss/reality-check language per `CO-03`'s explicit exclusion | `public-footer.blade.php` now includes: *"SlipGuard is intended for users aged 18 and over. If gambling stops being enjoyable or starts affecting your wellbeing, confidential support is available — search for a responsible gambling helpline in your region."* Checked against `docs/05-ux/TRUST_SIGNALS.md`'s Forbidden/Required Language lists before writing — no conflict. **Scope note**: added only to the public footer (where `CO-03` specified "visible on public pages"), not to the deliberately minimal authenticated-workspace footer (`partials/authenticated-footer.blade.php`) — expanding that footer's content would be a real UX judgment call against its own documented minimalism principle, outside this directive's content-only authorization; flagged, not decided unilaterally |

## Operations

*(Converts `OO-RC1-001`'s blueprint sections O-01–O-07 into executable work packages, per the directive's own instruction — no new design, the blueprint's own recommendations, now scheduled.)*

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| OP-01 | Provision the production server (blueprint §O-01: VPS + Nginx + PHP 8.3-FPM at ≥512M + MySQL + SSL + DNS) (closes `R-03`) | Operations | Critical | Medium | **Real dependency: hosting/vendor choice needs Product Office confirmation** — the blueprint named a recommendation (Forge/Ploi-managed VPS) but did not commit spend unilaterally | A live, HTTPS-reachable environment matching the blueprint's O-01 specification exists | Live URL, valid SSL certificate, `/up` health check returns 200 |
| OP-02 | Build the deployment pipeline and rollback path (blueprint §O-02) (closes `R-04`) | Operations | Critical | Medium | `OP-01` | A deploy can be performed and rolled back by following the playbook alone; the scripted release-verification smoke test (login → create-slip → analyse → view-report) runs automatically on every deploy | At least one real deploy executed successfully; verification-script output attached |
| OP-03 | Integrate error-tracking/monitoring (blueprint §O-04) (closes `R-05`) | Operations | Critical | Small–Medium | `OP-01` | A real error-tracking service (Sentry or Flare, per the blueprint's recommendation) receives real events; the alert channel is confirmed working | A deliberately-triggered test error appears in the dashboard; a test alert is received on the monitored channel |
| OP-04 | Extend the health check to verify real database connectivity (blueprint §O-04) (closes `R-20`) | Operations | Minor | Trivial | `OP-01` | `/up` (or an extended endpoint) fails meaningfully if the database connection is down, not just if the app boots | A test against a deliberately-broken DB connection in a non-production environment |
| OP-05 | Configure production environment variables and secrets via the platform's own editor (blueprint §O-02, "Environment variables") | Operations | Major | Small | `OP-01` | Every `.env.example`-documented variable has a real production value set through the platform's secret storage, never a committed plaintext file | Reviewed checklist sign-off (values, not secrets, per established practice) |
| OP-06 | Automate daily database backup to off-server object storage; run one real restore drill (blueprint §O-05) (closes `R-06`, `R-07`) | Operations | Critical | Medium | `OP-01`; an S3-compatible storage account | Automated daily backup running; at least one real restore drill completed against a genuinely disposable database, mirroring `I-01.2`'s already-established method | Backup job logs; a written restore-drill result |
| OP-07 | Configure production log aggregation (blueprint §O-04) (closes `R-08`) | Operations | Major | Small–Medium | `OP-01` | Logs are shipped somewhere durable and searchable, not left only on local disk | A test log line traced through the aggregation service |

## Compliance

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| CO-01 | ✅ **DONE, `PO-CO-002`, 2026-08-04** — real Terms of Service content, jurisdiction-neutral wording | Compliance Office (drafted) | Critical | Large | Qualified legal counsel sign-off remains outstanding `⚠` (genuinely external, not closeable internally) | Real content live at `/terms` | `resources/views/pages/terms.blade.php`, `docs/09-compliance/CO-005-LEGAL-REVIEW-REGISTER.md` |
| CO-02 | ✅ **DONE, `PO-CO-002`, 2026-08-04** — real Privacy Policy content, jurisdiction-neutral wording, cookies folded in per `CO-MVP-001`'s recommendation | Compliance Office (drafted) | Critical | Large | Qualified legal counsel sign-off remains outstanding `⚠` | Real content live at `/privacy` | `resources/views/pages/privacy.blade.php`, `docs/09-compliance/CO-005-LEGAL-REVIEW-REGISTER.md` |
| CO-03 | ✅ **DONE** — Responsible Gambling signposting: footer statement + generic, jurisdiction-neutral support-resource reference (per `CO-MVP-001` C-03's own recommendation — explicitly no deposit-loss disclaimers, no reality-check language) | Compliance (content) + Engineering (implementation) | Major | Small | None | Visible on public pages; wording matches C-03's specification | Implemented using `CO-MVP-001` C-03's own already-written recommendation verbatim as the wording source (no separate Compliance approval step occurred beyond that certification itself) — live in `public-footer.blade.php`. No deposit-loss/reality-check language included, as specified |
| CO-04 | ✅ **DONE** — Age statement wording: generic "18+" baseline, explicitly flagged for revision once launch markets are named | Compliance | Major | Small | Feeds `EN-06` | Present at minimum | Same footer addition as `EN-06` — *"SlipGuard is intended for users aged 18 and over."* A registration-time checkbox was deliberately **not** added — would alter the registration workflow, outside `PO-RC1-002`'s explicit constraints; a ToS clause remains recommended once real ToS content is authored (`CO-01`) |
| CO-05 | ✅ **NO ACTION NEEDED — finding corrected**, see `EN-05` | Compliance | Minor | N/A | N/A | N/A | A real copyright notice already existed; the original finding was a search false-negative, corrected in `CO-MVP-001` |

## Product Office

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| PO-01 | Accept `AO-MVP-005` (Reference Data Ownership review) — the one remaining item explicitly blocking `MVP_SCOPE_LOCK.md` from being frozen (part of `R-11`) | Product Office | Major | Trivial (a decision, not new work — the review itself is already delivered) | None | `MVP_SCOPE_LOCK.md`'s own status line no longer names this as an open blocker | Decision recorded in `DECISION_LOG.md` |

---

# Can Complete Before Public Launch

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| CO-06 | Name intended launch market(s), or explicitly adopt a market-agnostic soft launch as a deliberate decision (`CO-MVP-001` C-05) | Product Office | Major | Small (a decision) | None | A named market (or an explicit "market-agnostic" decision) is recorded | Decision recorded in `DECISION_LOG.md`; `CO-MVP-001`'s Jurisdiction Matrix populated against the real market once named |
| PO-02 | Resolve `MVP_SCOPE_LOCK.md`'s pricing/Premium boundary decision (part of `R-11`) | Product Office | Major | Medium (a business decision) | `PO-01` | A pricing model is approved, or Free-only v1.0 is explicitly confirmed | Decision recorded |
| PO-03 | Resolve `MVP_SCOPE_LOCK.md`'s Settings scope decision (part of `R-11`) | Product Office | Minor | Small | `PO-01` | Settings confirmed deferred, or its minimum MVP scope defined | Decision recorded |
| PO-04 | Resolve `MVP_SCOPE_LOCK.md`'s Help scope decision (part of `R-11`) | Product Office | Minor | Small | `PO-01` | Help confirmed deferred, or its minimum content defined | Decision recorded |
| EN-07 | Add pagination/bounding to Journal's `availableAnalyses` dropdown and Planner History (`R-13a`, `R-13b`) | Engineering | Minor | Small | None | Both queries bounded | Code review + a test with high-volume seed data |
| EN-08 | Fix Journal's "Linked analysis" label contrast (4.34:1 → ≥4.5:1) (`R-14`) | Engineering | Minor | Trivial | None | Passes WCAG AA | Re-run `axe-core` against the fixed page |
| EN-09 | Correct stale Capability B flag-gating documentation (route comment + `MVP_SCOPE_LOCK.md` §4.14) (`R-16`) | Engineering | Minor | Trivial | None | Documentation matches the real mechanism | Diff review |

---

# Post-Launch Backlog

| ID | Item | Owner | Priority | Est. effort | Dependencies | Acceptance criteria | Evidence required for closure |
|---|---|---|---|---|---|---|---|
| EN-10 | Confirm Capability B's `<dl>` axe `definition-list` flag is a rule false-positive, or flatten the markup if not (`R-15`) | Engineering | Minor | Trivial | None | Confirmed either way, not left ambiguous | A written finding either way |
| EN-11 | Re-verify migration reproducibility against a fresh disposable MySQL database once root credentials are available (`R-18`) | Engineering | Minor | Small | Root DB access in whatever environment does this | All 23 migrations still run clean in one batch | Fresh disposable-database run, mirroring `I-01.2` |
| CO-07 | Affiliate Compliance Framework (`CO-MVP-001` C-04) — dormant, no current affiliate proposal exists | N/A | Observation Only | N/A | A real future affiliate proposal, if one is ever made | N/A until triggered | N/A |

---

# What This Plan Does Not Do

Per `PO-RC1-001`'s own explicit constraints: no architecture was redesigned, no MVP scope was expanded, no new product capability was introduced, and no new governance document was created beyond this single closure plan — every item above closes a blocker the three accepted source documents already found, none invents a new one. Execution of every item above remains a separate action from this planning document itself; **RC1 Certification should be commissioned once every "Must Complete Before RC1" item above is closed with the evidence specified**, not before.
