# SlipGuard MVP Launch Readiness Audit

| Field | Value |
|---|---|
| Commission | `PO-MVP-005`, Product Office, Critical priority, direct founder instruction, 2026-08-03 |
| Type | **Audit only.** No redesign, no implementation, no scope expansion, no silent fixes, no governance change is authorized by this document. |
| Method | Four parallel evidence-gathering passes (Scope+Functional, UX+Intelligence, Database+Performance, Security+Compliance+Operations), each independently verifying claims against real code, real routes, real test runs, and — where safely possible — a real running instance, rather than trusting prior documentation. Synthesized into this single document by Engineering. |
| Status | Delivered. Awaiting Product Office release decision. |

---

# 1. Executive Summary

## Is MVP launch-ready?

**No — not for a real, public production launch today.** The application itself — the deterministic core, Capability A, Capability B, History, Journal, Explainability, security, and data model — is genuinely strong and would independently qualify as **GO WITH CONDITIONS**. But three categories of Critical, blocking gaps exist entirely outside the application layer, and none of them are things this audit is authorized to fix:

1. **Operations/Infrastructure does not exist.** No production environment, no deployment pipeline, no monitoring, no error reporting, no backup, no restore procedure. This alone makes a production launch impossible regardless of how complete the features are — there is no environment to launch *to*.
2. **Compliance has two Critical, unresolved blockers.** Terms of Service and Privacy Policy are both honest stubs, not real legal content. A third, newly-surfaced item — no Responsible Gambling signposting exists anywhere in the product, and no Compliance Office ruling exists on whether it's required — is Major and blocking pending that determination.
3. **Governance scope is not actually frozen.** `MVP_SCOPE_LOCK.md` still carries its own "NOT FROZEN" status line, with four Product Office decisions genuinely outstanding (pricing, Settings, Help, Privacy/Terms authorship).

None of this reflects badly on the engineering work itself — every part of this audit found the implemented product to be real, tested, and honestly built. The gap is entirely in what surrounds it.

## If not, why not?

See the Launch Blockers list (§12) for the definitive, classified list. In short: no production environment (Critical), no deployment/CI-CD (Critical), no monitoring/error reporting (Critical), no backup (Critical), no restore procedure (Critical), Terms of Service stub (Critical), Privacy Policy stub (Critical), Responsible Gambling determination pending (Major, blocking pending that ruling), MVP scope not frozen (Major), Capability B authorized but its feature flag was never flipped (Major, specific to that capability), and one production-configuration gap (`SESSION_SECURE_COOKIE` undocumented, Major).

## Confidence level

**High**, on the evidence gathered. Every finding in this document is backed by a real file citation, a real test run, or (for the UX/Intelligence pass) real browser verification via Playwright driving the system's installed Chrome, cross-checked against axe-core WCAG 2.1 A/AA scans. Two honest gaps in verification depth are disclosed, not hidden: the Database Audit could not obtain root MySQL credentials this pass and fell back to file-based verification plus reliance on `I-01.2`'s prior real disposable-database run (still recent, one session old, no schema changes since); and the UX pass time-boxed its live coverage to the highest-traffic screens the commission named rather than every screen in the product.

## Overall recommendation

**NO GO today.** **GO WITH CONDITIONS is realistically close** — nearly every Critical blocker is provisioning/content/configuration work, not a defect requiring redesign. Recommended sequencing: (1) resolve the Critical Operations/Infrastructure gaps (provision an environment, wire monitoring/error-reporting/backup — genuinely new work, not a fix to existing code), (2) commission real Terms/Privacy content and a Responsible Gambling determination from Compliance Office, (3) close the four remaining `MVP_SCOPE_LOCK.md` decision items, (4) flip `MARKET_WIDE_PLANNER_ENABLED` and confirm `SESSION_SECURE_COOKIE`/`APP_DEBUG` as part of a documented production checklist. None of the Minor/Observation findings below need to block a GO WITH CONDITIONS decision once the above clears.

---

# 2. Scope Verification

**Method:** every claim was re-verified directly against real routes (`routes/web.php`, `routes/auth.php`), real files, and either real test evidence or direct code reading — not taken from `MVP_SCOPE_LOCK.md`'s own prose.

**Finding R-11 — "MVP Scope Lock" is not accepted; it remains explicitly unfrozen.** `docs/product/MVP_SCOPE_LOCK.md`'s own status line: *"Draft for Product Office decision — NOT FROZEN."* Four items remain "Product Office Decision Required": **#19 Settings**, **#20 Help**, **#21 Public Marketing Website** (Privacy/Terms content), **#22 Subscription/Billing** (pricing). Still pending `AO-MVP-005`'s acceptance. Only feature #14 (Capability B) is actually resolved, by `PO-MVP-004`. **Severity: Major. Blocking:** No for features that don't depend on these decisions; **Yes** for any launch communication claiming scope is frozen. **Owner:** Product Office.

**Finding R-17 — the `PO-MVP-005` commission's own Background section overstates "Reference Data Model Phase 1 accepted."** `docs/02-architecture/U-18.4.2-REFERENCE-DATA-MODEL-ARCHITECTURE.md`'s own status line: *"Proposed — not accepted."* No implementation exists yet regardless; Capability B ships on the existing, unmodified football implementation per `PO-MVP-004`, so this doesn't affect launch readiness directly. **Severity: Minor. Blocking: No. Owner:** Product Office (documentation accuracy only).

### Feature-by-feature (all 22 items, `MVP_SCOPE_LOCK.md` §3 inventory)

| # | Feature | Planned | Implemented | Tested | Accepted | Outstanding work |
|---|---|---|---|---|---|---|
| 1 | Authentication (incl. Registration) | Yes | Yes — real Volt pages, `routes/auth.php` | Yes | **Yes** | None |
| 2 | Customer Dashboard | Yes | Yes — `dashboard` route | Yes — `DashboardTest.php` | **Yes** | None |
| 3 | Slip Builder (manual) | Yes | Yes — `analyze/create`, `analyze/{id}/edit` | Yes — 7 `BettingSlip*` test files | **Yes** | None |
| 4 | Intake — Screenshot Upload | Yes | Yes, no OCR (by design, disclosed) | Yes | **Yes (Polish Required)** | None launch-blocking |
| 5 | Intake — PDF Upload | Yes | Yes — real text extraction | Yes | **Yes** | None |
| 6 | Intake — Paste Text | Yes | Yes | Yes | **Yes** | None |
| 7 | Intake — Bet Code / Share Link | Yes (UI only) | No backend, confirmed | N/A | **Deferred, correctly so** | None — deferred is correct |
| 8 | Deterministic Rule Engine | Yes | Yes — `app/Domain/Risk/*` | Yes — 5 dedicated test files | **Yes** | None |
| 9 | Explainability | Yes | Yes | Yes | **Yes** | None |
| 10 | Risk Report | Yes | Yes — `analyze/{id}/report` | Yes | **Yes** | None |
| 11 | History | Yes | Yes — `history` route | Yes — incl. pagination | **Yes** | None |
| 12 | Decision Journal | Yes | Yes | Yes — flakiness did not reproduce this audit | **Yes (Polish Required)** | Continue monitoring |
| 13 | Planner (Capability A) | Yes | Yes | Yes — 5 test files | **Yes** | None |
| 14 | Capability B (Market Intelligence) | Yes | Yes — real 1,300+-line implementation | Yes — 4 test files | **Yes, launch-enabled** (`PO-MVP-004`) | **R-16, R-12** — see below |
| 15 | Demo Workspace | Yes | Yes — production-gated | Yes | **Yes (Internal)** | None |
| 16 | SlipGuard Labs | Yes | Yes | Not independently re-confirmed this pass | **Yes** | Minor — confirm dedicated test coverage exists (Engineering, not blocking) |
| 17 | Operations Panel | Yes | Yes — real access control | Yes — 3 test files | **Yes (Internal)** | None |
| 18 | User Profile | Yes | Yes | Yes | **Yes** | None |
| 19 | Settings | Yes (deferred) | No — generic `coming-soon` | N/A | **Not decided** — R-11 | Awaiting Product Office |
| 20 | Help | Yes (deferred) | No — generic `coming-soon` | N/A | **Not decided** — R-11 | Awaiting Product Office |
| 21 | Public Marketing Website | Yes | Yes, mostly; `/privacy`/`/terms` stub | Yes, extensively | **Not fully decided** — R-11, R-01 | Real legal authorship (Critical) |
| 22 | Subscription / Billing | No | No — confirmed no billing package | N/A | **Not decided** — R-11 | Pricing decision required first |

**Finding R-16 — Capability B's documented access-control mechanism does not match its real implementation.** Both `routes/web.php`'s inline comment and `MVP_SCOPE_LOCK.md` §4.14 claim the route "doesn't register" / the component's `mount()` "aborts 404" when `MARKET_WIDE_PLANNER_ENABLED` is false. Neither is true — the route always registers, the component has no `mount()` method and never calls `abort()`. The real mechanism is a graceful in-page "Experience preview" state (arguably better UX than a 404, and leaks no real functionality or data). **Severity: Minor. Blocking: No. Owner:** Engineering. **Resolution:** correct the stale comment and `MVP_SCOPE_LOCK.md` §4.14's evidence line.

---

# 3. Functional Audit

**Test suite baseline:** `/usr/local/php -d memory_limit=1024M vendor/bin/pest` (PHP 8.5.8, SQLite in-memory test DB per `phpunit.xml`). **Result: 689 tests, 680 passed, 2680 assertions, 2 failed.** Both failures are the already-known, pre-existing `PublicPagesTest.php` stale logo-rotation assertions from the deliberately-reverted "Static Product Mark" work — not a regression. No new failures. The previously-disclosed `DecisionJournalTest` flakiness did not reproduce this run.

**Finding R-10 — this environment's default PHP CLI `memory_limit` (128M) is too low to run the test suite; two different pages exhausted it on different runs.** Two separate attempts at the platform default 128M hit `Fatal error: Allowed memory size... exhausted` — once on Journal, once on History's pagination test. Passes cleanly at 1024M. **Severity: Major** — this is a real production-risk signal (128M is a common conservative PHP-FPM default; Journal/History are real customer-facing pages). **Blocking: No** for code correctness; **Yes** for the Operations checklist. **Owner:** Operations/Infrastructure. **Resolution:** document and enforce a minimum production `memory_limit` (recommend 512M).

### Per-area findings

- **Authentication:** Works. Real Breeze/Volt flow, rate-limited. No MFA — existing, previously-accepted decision, not a new gap.
- **Workspace (Dashboard):** Works.
- **Manual Slip Intake:** Works across all three live methods; Bet Code/Share Link correctly shows as disabled UI.
- **Capability B:** Functionally real and working, but see R-16. Additionally: `capabilityEnabled()` reads `config('slipguard-market-intelligence.enabled')` ← `env('MARKET_WIDE_PLANNER_ENABLED', false)`, currently `false` in `.env.example`.
  **Finding R-12 — Capability B is launch-authorized (`PO-MVP-004`) but the feature flag has never actually been flipped.** **Severity: Major. Blocking: Yes, for Capability B specifically to be customer-visible at launch** (not for MVP overall — the current `false` default is a safe, honest state, not an error). **Owner:** Engineering/Operations. **Resolution:** flip `MARKET_WIDE_PLANNER_ENABLED=true` in production `.env` as a release-checklist item, once the "normal quality gates" `PO-MVP-004` referenced are confirmed satisfied (this audit's own findings above are the relevant evidence).
- **Deterministic Analysis:** Works — 5 dedicated test files plus incidental coverage.
- **History:** Works, including pagination.
- **Journal:** Works; flakiness did not reproduce.
- **Profile:** Works.
- **Admin (Operations/Filament):** Works — real `is_internal` access control, 3 dedicated test files.
- **Demo Workspace:** Works — production-gated by `SLIPGUARD_DEMO_ENABLED`, 2 test files.
- **Feature Flags:** Two real flags confirmed (`MARKET_WIDE_PLANNER_ENABLED`, `SLIPGUARD_DEMO_ENABLED`), both plain `env()` booleans, no framework — consistent with this codebase's "avoid speculative complexity" principle.
- **Explainability:** Works — Main/Other Contributing Factors, Methodology, all real and test-covered.
- **Persistence:** Works — 680 passing tests exercise real Eloquent persistence throughout.
- **MySQL:** Confirmed real `.env` connection is MySQL/`slipguard`, consistent with `I-01.2`'s prior verified migration parity.

---

# 4. UX Audit

**Method:** Real browser verification achieved — Playwright driving the system's installed Google Chrome (`channel: 'chrome'`), matching the `I-01.2`/`U-20.8` precedent. Local dev server against the real local `slipguard` MySQL database. Logged in as `demo@slipguard.local` (password temporarily reset for this audit — see **Action Item A-1** below). Full-page and mobile-viewport (390×844) screenshots captured for Home, Login, Dashboard, History, Journal, Manual Builder, Capability B Builder, and two Risk Report states. **Zero page/console errors captured across the full run.**

- **Navigation:** Desktop sidebar and active-state highlighting work correctly. Mobile off-canvas drawer confirmed working (Analyse/Planner/Reports/Pricing, Search/Language/Theme, Sign In/Get Started). No finding.
- **Loading / Empty / Error states:** A real "We couldn't analyze this slip" empty state was captured live, with an honest reason and a trust statement satisfying the Human-Designed Standard's "Evidence Before Theatre" principle. Processing animations were not re-exercised live this pass (**Finding UX-3, Observation Only** — time-boxed scope decision, not a defect; recommend a dedicated pass if desired before sign-off. **Owner:** UX Studio).
- **Mobile behaviour:** Home renders cleanly at mobile width; not exhaustively re-tested across every authenticated screen this pass (same time-boxing as above).
- **Dashboard consistency:** Real, personalized, real counts shown (not placeholder data); Capability B card correctly avoids gambling language.
- **Human-designed principles / language clarity:** Spot-checked against `UX_RULES.md` across Dashboard, Capability B Builder, Risk Report — all comply ("SlipGuard evaluates risk—it doesn't predict who wins," etc.).
- **Accessibility (tool-verified, `@axe-core/playwright`, WCAG 2.1 A/AA):** Dashboard **0 violations**. Risk Report **0 violations**.
  - **Finding UX-1 (R-14) — Journal "Linked analysis" label fails color contrast**, 4.34:1 vs. 4.5:1 required (`journal/index.blade.php:266`). **Severity: Minor. Blocking: No. Owner:** Engineering/UX Studio. **Resolution:** `text-neutral-500` → `text-neutral-600` (the same fix `U-21` already used for an analogous failure), scoped to this label only.
  - **Finding UX-2 (R-15) — Capability B Builder's four `<dl>` blocks flagged by axe's `definition-list` rule** for div-wrapped `dt`/`dd` pairs, valid under current HTML5 spec. **Severity: Minor. Blocking: No. Owner:** Engineering. **Resolution:** confirm against the installed axe-core version's own guidance before treating as real; likely a rule false-positive, not a user-facing defect.
  - Not verified this pass: History, public marketing pages, auth pages — time-boxed to the four highest-traffic screens the commission named plus one representative Capability B screen.
- **Footer dark-theme contrast (`U-21` re-verification):** Sampled real computed colors — comfortably light-on-dark, **no regression found.**
- **Sticky header / logo motion contract:** Computed `transform: none` after scroll — matches the static-product-mark decision, **no regression found.**

---

# 5. Intelligence Audit

- **Deterministic outputs / repeatability:** Re-ran `tests/Unit/Risk/Engine/CalculateStructuralRiskPropertiesTest.php` fresh — **13/13 passed, 102 assertions.** Combined with the full-suite result, this is real, current, passing evidence of determinism.
- **Risk scoring / RF status:** Verified directly against source — `RelationshipFactor.php` (RF-006) `weight()` returns `0`, with an explicit inline note matching `MVP_SCOPE_LOCK.md` §4.8's disclosed claim exactly. **Confirmed accurate, no finding.**
- **Explainability:** Verified end-to-end in the browser against real persisted data — Structural Risk band, Main Contributing Factor with plain-language reason, Other Contributing Factors — all real, sourced from persisted `SlipAnalysis.factor_results`. No finding.
- **Journal generation:** Verified live — 12 real entries, correctly grouped, real free-text reflections. No finding.
- **Builder recommendations (Capability B):** `CandidateRejectionReason`/`CandidateExclusionReason`/replacement-reason label classes confirmed real, not just documented. Full live candidate-discovery cycle not independently re-driven this pass (depends on live provider data; covered by the Scope/Functional pass instead, to avoid duplicate/conflicting coverage). **Observation Only.**
- **Manual slip handling (Capability A):** Verified live — Manual Builder loads correctly for a real existing slip. Full create→analyze cycle already covered by the Scope/Functional pass, not duplicated here.

**No Critical or Major UX/Intelligence findings.** Everything independently verified matches what this repository's own history already claims, with no regression found.

---

# 6. Database Audit

**Verification method disclosed upfront:** no root MySQL credentials were available this pass; the real `slipguard` database was never touched. Verification fell back to direct migration/schema file reading, the standard Pest suite (hard-wired to SQLite regardless of `.env`), and confirming `MySqlIntegrationTest.php` behaves safely under that config.

| Item | Finding | Severity | Blocking? |
|---|---|---|---|
| Schema integrity | 23 migrations present, matching `I-01.2`'s prior real end-to-end verification (all 23 ran, one batch, from zero). Not re-run this pass (no root access); relying on that recent, real verification. | Observation Only | No |
| Foreign keys | Confirmed present and correctly scoped across all major tables by direct file read — restrict/cascade/null-on-delete choices are deliberate and correct (e.g. a slip a Planner session was built from can't be silently deleted). | Observation Only | No |
| Indexes | Real, purposeful indexing matching actual query shapes (`market_intelligence_fixtures` unique + composite index; `planner_regeneration_events` composite unique). | Observation Only | No |
| Migration reproducibility (R-18) | Not re-verified against a fresh database this pass. Last real verification: `I-01.2`, one session ago, all 23 in a single batch. No new migration added since. **Severity: Minor** — stale by one session, not by an unverified change. | Minor | No |
| Seeders / demo data | Not re-run this pass (same root-access constraint); `I-01.2` verified `slipguard:demo --fresh` end-to-end and its idempotency, no changes since. | Observation Only | No |
| `MySqlIntegrationTest.php` | Ran this session: **7 tests, 0 assertions, 7 skipped** — correctly self-skips under the suite's hard-wired SQLite config, confirming the safety mechanism works exactly as designed. Its real constraint coverage last executed for real in `I-01.2` (7/7 passing). | Observation Only | No |
| **Backup strategy (R-06)** | **Does not exist.** No backup scripts, cron jobs, `mysqldump` usage, or documented recovery procedure found anywhere in the tracked tree. | **Critical** | **Yes — for production launch** |
| **Recovery procedure (R-07)** | **Does not exist.** No documented restore process anywhere under `docs/`. Same root cause as R-06; consistent with this repo's own prior honest disclosure. | **Critical** | **Yes — for production launch** |

---

# 7. Performance Audit

*(Basic verification only, no formal benchmarking, per the commission's own instruction.)*

| Area | Finding | Severity | Blocking? |
|---|---|---|---|
| Dashboard queries | Eager-loaded, explicitly bounded (`->with([...])->limit(5)->get()`). No N+1. | Observation Only | No |
| History loading | Eager-loaded and **paginated** (`->paginate(10)`). | Observation Only | No |
| Journal loading | Eager-loaded and **paginated** (`->paginate(8)`); the entry-creation `availableAnalyses` dropdown query is unbounded — **R-13a**. | Minor | No |
| Planner loading | Session view eager-loads correctly. Planner History loads **every** session a customer has ever created in one unbounded query — **R-13b**. | Minor | No — no realistic near-term volume risk |
| Capability B (Builder) | Discovery query is bounded by competition whitelist + time window, using a purpose-built composite index — not a table scan. A secondary "recent sessions" query shares the same unbounded-but-low-volume shape as Planner History. | Observation Only | No |
| Large slips | The 20-leg ceiling is real and enforced server-side (`BettingSlipValidationRules`) and client-side (disabled "add leg" control), not just documented. | Observation Only | No |
| Environment memory ceiling | Same as **R-10** — directly relevant here since pagination-heavy pages are exactly where it surfaced. | Major | Yes, for PHP-FPM sizing |

**Nothing found in this pass blocks MVP feature-completeness or correctness.** Schema, foreign keys, indexes, and query patterns are sound and deliberate. The two Critical database items (R-06, R-07) and the Major memory-limit item (R-10) are production-operations readiness gaps, not application-layer defects.

---

# 8. Security Audit

| # | Area | Finding | Severity | Blocking? |
|---|---|---|---|---|
| 7.1 | Authentication | Real Breeze/Volt flow, genuine rate limiting (`LoginForm.php`, 5 attempts/`email\|ip`, real `Lockout` event). Social auth buttons honestly disabled (Socialite not installed) — disclosed, not misleading. | No gap | No |
| 7.2 | Authorization | 7 real Policy classes, ownership enforced by direct model comparison — never client-supplied ID. `$this->authorize(...)` called at 25 real sites, not merely defined. Listing queries scope through the authenticated user's own relations. | No gap | No |
| 7.3 | Roles / Permissions | Real two-tier internal model: `is_internal` gates the whole Filament panel; `can_manage_customer_data` (narrower, requires both) separately gates customer-data visibility specifically. | No gap | No |
| 7.4 | Secrets | `.env` confirmed gitignored and never tracked; `.env.example` values all empty/placeholder; repo-wide grep for credential-shaped literals returned zero matches. | No gap — **no leaked secret found** | No |
| 7.5 | Logging | Standard Laravel channels; grep for `Log::` calls against sensitive-value patterns returned zero matches. | No gap | No |
| 7.6 | Audit Trails | `RecordAuditEvent` is the sole write path; immutability enforced at the authorization layer (no update/delete ability defined), not just by UI omission. 2 real invocation points confirmed. | No gap | No |
| 7.7 | Session Handling | Database-backed sessions, `http_only`, `same_site=lax`, session regeneration on login (fixation-safe). **R-09 — `SESSION_SECURE_COOKIE` has no fallback default and is undocumented in `.env.example`** — the cookie won't be marked `Secure` in production unless an operator independently knows to set it. | **Major** | **Yes, for production launch** |
| 7.8 | Input Validation | Real, bounded validation on the two highest-risk paths (file upload: `mimes:pdf`/`image`, `max:10240`; manual entry: bounded odds/text/leg-count). Not exhaustively re-verified across every Form Request. | No gap in paths sampled | No |

---

# 9. Compliance Audit

| # | Area | Finding | Severity | Blocking? |
|---|---|---|---|---|
| 8.1 | Terms of Service (**R-01a**) | Confirmed stub — `/terms` → `public-coming-soon`. Already known, not new. | **Critical** | **Yes** |
| 8.2 | Privacy Policy (**R-01b**) | Confirmed stub — same route pattern. Already known. | **Critical** | **Yes** |
| 8.3 | Disclaimers | Required language from `TRUST_SIGNALS.md` genuinely implemented, not just documented — verified on homepage, Risk Report, and Manual Builder. | No gap | No |
| 8.4 | Responsible Gambling (**R-02**) | Zero matches for common signposting patterns anywhere in the product. Product design itself avoids excitement mechanics (real, verified mitigation), but no explicit support signposting exists. Whether this is a hard requirement depends on SlipGuard's actual regulatory position — a determination this audit cannot make. | **Major** | **Yes, pending Compliance Office determination** |
| 8.5 | Jurisdiction Assumptions | No false or overreaching licensing claim found — correctly attributes licensing to "your licensed operator" throughout. | No gap | No |
| 8.6 | No-Funds-Handling Guarantee | Genuinely true in the running code (no billing/payment package anywhere), not just asserted in copy. | No gap | No |
| 8.7 | Affiliate Disclosures | No affiliate-tracking or commercial-partnership code found; explicit customer-facing statement exists. Nothing to disclose because nothing exists. | No gap | No |

---

# 10. Operations Audit

| # | Area | Finding | Severity | Blocking? |
|---|---|---|---|---|
| 9.1 | Deployment (**R-04**) | No CI/CD, no `Dockerfile`/`docker-compose.yml`, no deployment automation anywhere. | **Critical** | **Yes, for production launch** |
| 9.2 | Configuration (**R-09b**) | Adequate for local dev; no production-environment config template exists — directly connects to §8.7's `SESSION_SECURE_COOKIE` gap. | Major | Yes, for production |
| 9.3 | Logging (operational) (**R-08**) | Default file-based channel only; no centralized aggregation/shipping. | Major | Yes, for production |
| 9.4 | Monitoring (**R-05**) | No error-tracking/APM package integrated anywhere. A production incident would be invisible until a customer reports it. | **Critical** | **Yes, for production launch** |
| 9.5 | Health Checks | A real, minimal endpoint exists (Laravel's framework default `/up`), not custom-built for this app's specific dependencies. | Minor | No — present, just shallow |
| 9.6 | Production Environment (**R-03**) | Does not exist anywhere — no provisioning, no hosting documentation. Confirms this repo's own prior honest disclosure; nothing has changed. | **Critical** | **Yes** — there is no environment to launch to |
| 9.7 | Error Reporting | Same underlying gap as R-05, restated for this checklist item. | Critical | Yes |
| 9.8 | Backup | Same as **R-06** (§6). | Critical | Yes |
| 9.9 | Restore / Recovery | Same as **R-07** (§6). | Critical | Yes |

**This is where the real launch risk concentrates.** Every Operations item is currently non-existent, not merely immature — consistent with, not a new discovery beyond, this repository's own prior disclosures that Infrastructure was never in scope for any engineering work package to date. **No production launch is possible today regardless of any other section's findings.**

---

# 11. Risk Register

| ID | Description | Evidence | Severity | Likelihood | Impact | Owner | Recommended action | Blocking? |
|---|---|---|---|---|---|---|---|---|
| R-01a | Terms of Service is a stub | `routes/web.php:40` → `public-coming-soon` | Critical | Certain (already true) | Legal exposure launching to real customers | Compliance/Legal | Commission real ToS content | **Yes** |
| R-01b | Privacy Policy is a stub | `routes/web.php:36` → `public-coming-soon` | Critical | Certain | Legal exposure, data-protection risk | Compliance/Legal | Commission real Privacy content | **Yes** |
| R-02 | No Responsible Gambling signposting; no Compliance ruling on requirement | Repo-wide search, zero matches | Major | Certain | Regulatory exposure depending on jurisdiction | Compliance Office | Rule explicitly on requirement before launch | **Yes, pending ruling** |
| R-03 | No production environment exists | No provisioning/hosting docs anywhere | Critical | Certain | Cannot launch at all | Infrastructure/Operations | Provision an environment | **Yes** |
| R-04 | No deployment/CI-CD pipeline | No `.github/workflows`, `Dockerfile`, etc. | Critical | Certain | No repeatable, safe release process | Infrastructure/Operations | Build minimal deploy pipeline | **Yes** |
| R-05 | No monitoring/error tracking | No APM package in `composer.json` | Critical | Certain | Incidents invisible until customer-reported | Infrastructure/Operations | Integrate an error-tracking service | **Yes** |
| R-06 | No backup strategy | No backup scripts/packages anywhere | Critical | Certain | Unrecoverable data loss on any incident | Infrastructure/Operations | Implement automated backups | **Yes** |
| R-07 | No recovery/restore procedure | No runbook under `docs/` | Critical | Certain | Cannot recover even if a backup existed | Infrastructure/Operations | Document and test a restore procedure | **Yes** |
| R-08 | No production log aggregation | Default file-based channel only | Major | Certain | Slower incident diagnosis | Infrastructure/Operations | Add centralized log shipping | Yes, for production |
| R-09 | `SESSION_SECURE_COOKIE` undocumented, no safe default | `config/session.php:172`, absent from `.env.example` | Major | High (easy to miss) | Session cookie not marked `Secure` in production | Engineering | Add to `.env.example` + production checklist | Yes, for production |
| R-10 | Default 128M PHP `memory_limit` insufficient for real pages | Two independent OOM failures, Journal + History | Major | High (common PHP-FPM default) | Real customer-facing page failures | Operations/Infrastructure | Set/document minimum 512M in production | Yes, for production |
| R-11 | MVP Scope Lock still not frozen | `MVP_SCOPE_LOCK.md`'s own status line | Major | Certain | Ambiguity about what's actually in v1.0 | Product Office | Resolve 4 remaining decision items + accept `AO-MVP-005` | Yes, for a "scope frozen" claim |
| R-12 | Capability B authorized but flag never flipped | `.env.example:104` still `false` | Major | Certain | Capability B invisible to customers despite authorization | Engineering/Operations | Flip flag as part of release checklist | Yes, for Capability B visibility |
| R-13a | Journal's `availableAnalyses` dropdown query unbounded | `journal/index.blade.php:115` | Minor | Low near-term | Slow page for very high-volume accounts | Engineering | Add a reasonable cap/pagination | No |
| R-13b | Planner History loads every session unbounded | `planner/history.blade.php:32-34` | Minor | Low near-term | Same as above | Engineering | Add pagination | No |
| R-14 | Journal label fails WCAG AA contrast (4.34:1) | `journal/index.blade.php:266` | Minor | Certain | Minor accessibility non-compliance | Engineering/UX Studio | `text-neutral-500` → `text-neutral-600` | No |
| R-15 | Capability B `<dl>` blocks flagged by axe `definition-list` rule | `market-intelligence/builder.blade.php:967` etc. | Minor | Likely false positive | None if confirmed false positive | Engineering | Verify against current axe guidance before changing | No |
| R-16 | Capability B flag-gating docs describe a 404 that never happens | `routes/web.php` comment, `MVP_SCOPE_LOCK.md` §4.14 | Minor | Certain | Misleading internal documentation only | Engineering | Correct the comments/doc | No |
| R-17 | Commission Background overstates Reference Data Model status | `U-18.4.2` doc's own status line | Minor | Certain | Documentation accuracy only | Product Office | None needed beyond this audit's correction | No |
| R-18 | Migration reproducibility verification is one session stale | No new migrations since `I-01.2` | Minor | Low | Negligible — no schema change since | Engineering | Re-verify opportunistically with root DB access | No |
| R-19 | `demo@slipguard.local` password was reset during this audit | Disclosed by the UX/Intelligence audit pass | Minor | Certain | Demo account credential no longer matches whatever it was before | Engineering | **Resolved 2026-08-03** — rotated again to a fresh random value via the app's own `Str::password(16)` generation mechanism, shown once, not stored | No — closed |
| R-20 | Health-check endpoint is framework-default only | `bootstrap/app.php:12`, `/up` | Minor | Certain | Doesn't verify DB/queue connectivity specifically | Infrastructure/Operations | Extend health check to real dependencies, post-launch acceptable | No |

---

# 12. Launch Blockers

**Critical:**
- R-01a — Terms of Service stub
- R-01b — Privacy Policy stub
- R-03 — No production environment
- R-04 — No deployment/CI-CD pipeline
- R-05 — No monitoring/error tracking
- R-06 — No backup strategy
- R-07 — No recovery/restore procedure

**Major:**
- R-02 — No Responsible Gambling signposting / no Compliance determination
- R-08 — No production log aggregation
- R-09 — `SESSION_SECURE_COOKIE` undocumented
- R-10 — Production PHP `memory_limit` unconfirmed/likely too low by default
- R-11 — MVP Scope Lock not frozen
- R-12 — Capability B flag never flipped

**Minor:**
- R-13a, R-13b — Two unbounded, low-volume queries
- R-14 — Journal contrast failure
- R-15 — Capability B `<dl>` axe flag (likely false positive)
- R-16 — Stale Capability B gating documentation
- R-18 — Migration reproducibility one session stale

**Observation Only:**
- R-17 — Commission background overstatement (already corrected by this audit)
- R-19 — Demo account password rotation needed
- R-20 — Health check is shallow
- UX-3 — Processing animations / full mobile coverage not independently re-verified this pass

---

# 13. Release Checklist

## Engineering
- [x] Core MVP journey code-complete (§3)
- [x] Test suite green modulo 2 known pre-existing, unrelated failures (680/689, real evidence this session)
- [ ] `MARKET_WIDE_PLANNER_ENABLED` flipped for launch (**R-12**) — Incomplete
- [ ] `SESSION_SECURE_COOKIE` documented and set for production (**R-09**) — Incomplete
- [ ] Production PHP `memory_limit` set ≥512M (**R-10**) — Incomplete
- [ ] Capability B / `MVP_SCOPE_LOCK.md` documentation corrected (**R-16**) — Incomplete, Minor
- [x] Demo account password rotated post-audit (**R-19**) — Complete, 2026-08-03
- [ ] `DecisionJournalTest` flakiness — did not reproduce this session; **Deferred** (monitor, not launch-blocking per existing classification)

## Product
- [ ] MVP scope frozen (**R-11**) — Incomplete, 4 items open
- [ ] Pricing / Premium boundary decision — Incomplete
- [ ] Settings / Help scope decision — Incomplete
- [ ] Capability B launch-visibility decision — **Complete** (`PO-MVP-004`)

## Compliance
- [ ] Privacy Policy approved (**R-01b**) — **Blocked**, awaiting real legal content
- [ ] Terms of Service approved (**R-01a**) — **Blocked**, awaiting real legal content
- [ ] Responsible Gambling determination made (**R-02**) — Incomplete

## Infrastructure / Operations
- [ ] Production environment provisioned (**R-03**) — **Blocked**, not started
- [ ] Deployment pipeline built (**R-04**) — **Blocked**, not started
- [ ] Monitoring/error-reporting integrated (**R-05**) — **Blocked**, not started
- [ ] Backup automated (**R-06**) — **Blocked**, not started
- [ ] Recovery procedure documented and tested (**R-07**) — **Blocked**, not started
- [ ] Log aggregation configured (**R-08**) — **Blocked**, not started

## Database
- [x] Schema/FK/index integrity confirmed (this session, file-based + prior real verification)
- [ ] Fresh migration re-verified against a real disposable database this session — **Deferred** (root credentials unavailable this pass; last real verification `I-01.2`, one session old, no schema change since)

## UX
- [x] Accessibility spot-check complete for Dashboard/Risk Report (0 violations each)
- [ ] Two Minor accessibility findings resolved (**R-14**, **R-15**) — Incomplete
- [ ] Full mobile-viewport and processing-animation re-verification across every screen — **Deferred** (Observation Only, time-boxed this pass)

---

# 14. Day-One Operations

Given §10's findings, **none of the infrastructure below exists yet** — these are recommendations for what must exist before Day One, not a description of anything already in place.

- **Monitoring:** integrate a real error-tracking service (Sentry or equivalent) before launch — without it, R-05 means the team learns about production errors only when a customer reports one.
- **Support:** no support-ticket/contact mechanism was found beyond the public `/contact` stub referenced elsewhere in `MVP_SCOPE_LOCK.md` — needs a real channel (even a monitored inbox) before Day One.
- **Rollback:** with no deployment pipeline (R-04), there is currently no defined rollback mechanism either — define one as part of building the pipeline, not as an afterthought.
- **Incident handling:** no incident-response process exists in this repository's documentation — recommend a minimal runbook (who gets paged, where logs live, how to roll back) exists before launch, not after the first incident.
- **User feedback:** no in-product feedback mechanism was found in this audit's scope — consider whether one is needed for Day One or can follow shortly after.
- **Metrics:** beyond the Dashboard's own customer-facing counts, no operational/business metrics collection was found (no analytics package in `composer.json`). Recommend deciding what Day-One success looks like (signups, completed analyses, Capability B usage if enabled) and how it will be measured, before launch rather than reconstructing it after the fact.

---

# 15. Post-Launch Roadmap

Every non-MVP item found across this audit and the governance history it draws on, classified so nothing is left unclassified.

## Immediate Post Launch
- R-13a, R-13b — pagination/bounding for Journal dropdown and Planner History
- R-14, R-15 — the two Minor accessibility findings
- R-16 — Capability B documentation correction
- Explainability-coverage expansion (`PO-REVIEW-001` Priority 1)
- Builder intelligence filters (`PO-REVIEW-001` Priority 2)

## Phase 2 (per existing `MVP_SCOPE_LOCK.md` §9 roadmap, unaffected by this audit)
- OCR automation for screenshot intake
- Bet Code / share-link ingestion
- Settings and Help real content (pending Product Office scope decision)
- Subscription / Premium tier (pending pricing decision)
- Deterministic regeneration improvements (`PO-REVIEW-001` Priority 3)
- Builder toolbar enhancements (`PO-REVIEW-001` Priority 4)

## Architecture
- Reference Data Model Phase 2 — a real second sport (`U-18.4.2`, explicitly not authorized yet, needs its own Product Office commission)
- Sport Selector, Mixed-Sport Accumulators, Live Intelligence Sidebar, Candidate Library — all gated on the above (`U-18.4.1` gap analysis)
- The long-term "Candidate Workspace" concept (`PO-REVIEW-001` observation — explicitly future, not scoped)
- Health-check endpoint depth (R-20)

## Data Science
- Evidence Quality, Correlation, Volatility, Confidence metrics (`PO-REVIEW-001` ownership boundary) — Correlation specifically remains the disclosed, deliberately-inactive RF-006 gap

## Parser Office
- A second bookmaker/data-source provider integration (referenced by both `AO-MVP-005` and this audit's Performance section as still needing its own source-viability review)
- Any real second-sport evidence source, once Reference Data Model Phase 2 is authorized

## Future Research
- Expanded live market intelligence beyond Capability B's current scope
- Mobile application / browser extension (no implementation exists; not part of any current programme)

---

*This document is Engineering's evidence-grounded audit deliverable. It does not itself authorize any fix, redesign, or implementation — per the commission's explicit constraints, every finding above remains exactly as found until Product Office (and, where named, Compliance Office/Infrastructure) directs otherwise.*
