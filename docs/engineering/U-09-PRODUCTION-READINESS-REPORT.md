# U-09 — Production Readiness & Beta Release Preparation

| Field | Value |
|---|---|
| Commissioned By | `PO-U09-001` |
| Scope | Hardening/validation of the existing MVP only — no new capability |
| Owner | Engineering Office |
| Date | 2026-07-27 |

## Environment

`/usr/local/bin/php` (PHP 8.3), SQLite (dev/test), Pest, Pint. **No browser, device, or accessibility-tool automation is available in this environment** — the same constraint recorded at `U-07.7`/`U-07.8`. Stated directly per §3.9's own instruction, not worked around.

## 3.1 Authentication — PASS (pre-existing coverage, reconfirmed)

Registration, login, logout, password reset, password confirmation, and password update are all Laravel Breeze scaffolding with existing, still-passing test coverage (`tests/Feature/Auth/*`, 5 files). Route protection: every authenticated route sits inside `Route::middleware('auth')`, confirmed via `WorkspaceAccessTest`'s parametrised "guests redirected to login" / "authenticated users can access" pair, extended this sprint to cover the newest routes (`planner.history`). Session lifetime (120 min, configurable via `SESSION_LIFETIME`) and remember-me are both standard Laravel Breeze defaults — no custom code, nothing to hAudit beyond confirming neither was altered. Role protection: `User::canAccessPanel()` gates the Filament `/operations` panel on `is_internal`, unchanged since first built.

## 3.2 Customer Workspace — PASS

Dashboard (`U-02`), History/Journal/Planning History (`U-08.2`), Report screen (`U-03`) all covered by existing Feature tests. Navigation consistency verified — every new route added a corresponding desktop nav + mobile drawer entry (`U-08.2`). Loading/empty states: every screen in the Workspace reuses `EMPTY_STATES.md`'s approved copy verbatim; no ad hoc empty-state text exists anywhere in the Workspace.

## 3.3 Planner — PASS (see also `U-07.8`)

Full lifecycle, persistence, export, revision, and lock-behaviour validation already completed and accepted (`PO-U07.8-AC-001`). Abandoned/exported sessions confirmed to leave the source slip untouched. **Concurrent Planner protection** was already identified and accepted as a Category C gap at `U-07.8` (no optimistic locking on `PlannerSession`/`PlannerSelection`) — reconfirmed unchanged, not re-litigated here; the same absence of optimistic locking is true product-wide (`BettingSlip`, `JournalEntry`), not Planner-specific, and is recorded once, below (§3.6/§3.8), rather than three times.

## 3.4 Analysis Engine — PASS

Determinism and repeatability already verified at `U-07.8` (an exported Planner selection's independent Risk Engine analysis matches the Planner's own persisted MSC baseline exactly). Report generation, persistence, and the `ADR-007` read-only presentation boundary are covered by the existing `SlipAnalysisReportTest` suite (including its own "viewing a report never re-invokes the engine" test). MSC/weakest-leg ranking: covered by `RankLegsByStructuralWeaknessTest` and the Planner's own integration tests. Nothing in this engine has changed since `U-07.8`'s validation; re-confirmed via this sprint's full regression run only, not re-audited line by line.

## 3.5 Administration — FINDING (Category B, not resolved — requires Product Office scoping)

**No admin visibility exists for customers, analyses, or Planner sessions.** The `/operations` Filament panel (auth-gated on `is_internal`, confirmed correctly configured) contains exactly one resource: `LabsFeatureResource` (Programme U-13). There is no way for internal staff to look up a customer's account, betting slips, analyses, or Planner sessions except by querying the database directly. This is a genuine operational-support risk once real customers exist in beta — but building the actual admin resources is new engineering scope (what fields are visible, what actions are permitted, whether admin actions are themselves audit-logged) that requires Product Office judgement on exactly what support staff should be able to see and do, per this commission's own §8 boundary ("Engineering shall not expand scope under the banner of hardening"). **Not built here.** Recorded as a finding for Product Office to scope and separately commission, not silently expanded into.

## 3.6 Error Handling — PASS, with one already-fixed defect (`U-07.8`) reconfirmed, and one new observation

- Validation failures: every form in the product (slip builder, Planner replacement-leg form, Journal entry) uses the same `BettingSlipValidationRules`/Livewire validation pattern; all reject invalid input before mutation, verified by test in every relevant suite.
- Missing/deleted records: route-model-binding 404s automatically for any non-existent id; verified explicitly for `SlipAnalysis` (`SlipAnalysisReportTest`), `PlannerSession` (`PlannerSessionUiTest`), and implicitly for `JournalEntry`/`BettingSlip` via the same framework mechanism.
- Deleted records mid-reference: the delete-while-referenced defect found and fixed at `U-07.8` (deleting a slip with Planner history) is unchanged and still covered by regression tests.
- Concurrent updates: **no optimistic locking exists anywhere in this application** — not Planner-specific (`U-07.8`'s finding), true of `BettingSlip`, `JournalEntry`, and `SlipAnalysis` as well. No evidence of real-world occurrence in a single-customer-editing-their-own-data product; recorded once, here, as a standing Category C observation across the whole app rather than repeated per model.
- Unexpected exceptions: Laravel's default exception handler renders a generic error page when `APP_DEBUG=false` (framework default, not custom code) — contingent on that setting being correct in the actual deployed environment, which this report cannot verify from inside this environment (see Operational Readiness Checklist, below).

## 3.7 Performance — PASS (new measurements this sprint)

The three Workspace screens built under `U-08.2` had never been benchmarked before this sprint. Measured directly (isolated, single-page-load queries, not conflated with authentication-loop artefacts — an earlier combined measurement in this same sprint produced inflated, misleading numbers from repeated `actingAs()` calls in one test loop; corrected before recording):

| Screen | Queries (5 analyses / 3 sessions) |
|---|---|
| History | 2 |
| Journal | 4 |
| Planning History | 3 |

All well within the existing `<15` budget established for the Report screen (`SlipAnalysisReportTest`). No N+1 pattern found — each screen's single `with([...])` eager-load call is sufficient. Permanent regression tests added (`tests/Feature/Workspace/*Test.php`) so this stays true going forward. No other obvious inefficiency identified; this is not an optimisation programme, and none was attempted beyond confirming the above.

## 3.8 Security — PASS

- Authorisation: every model with a policy (`BettingSlipPolicy`, `SlipAnalysisPolicy`, `PlannerSessionPolicy`, `JournalEntryPolicy`) scopes `view`/`update`(/`delete` where applicable) to `user_id` ownership; cross-user access is explicitly tested for every one of them across this conversation's test suites.
- CSRF: unmodified Laravel default (`bootstrap/app.php` makes no CSRF customisation) — the `web` middleware group's `ValidateCsrfToken` applies to every route.
- Mass assignment: every model in `app/Models/` was checked directly — all declare either `$fillable` or the `#[Fillable]` attribute; none rely on an unguarded/`$guarded = []` state.
- Hidden attributes: `User` model declares `#[Hidden(['password', 'remember_token'])]`, unchanged.
- Route protection: confirmed above (§3.1).
- Customer isolation: confirmed above (Authorisation) and by the parametrised guest/authenticated route tests.

## 3.9 Accessibility — LIMITED (unchanged constraint, stated explicitly)

Structural accessibility (ARIA attributes, colour-independent indicators, focus-ring reuse, heading hierarchy) has been verified by code review at each relevant delivery (`U-03`, `U-07.7`, `U-08.2`) and judged consistent with `ACCESSIBILITY.md`. **Genuine screen-reader, keyboard-only, and measured-contrast verification has never been possible in any environment this work has run in** — no such tooling exists here. This is recorded plainly, once, rather than re-asserted per screen.

## 3.10 Mobile Experience — LIMITED (unchanged constraint, stated explicitly)

Every screen in the product uses the same mobile-first Tailwind structure (single column by default, breakpoint-gated multi-column only where already established, e.g. the Planner's Compare view). No new responsive pattern was introduced by `U-08.2`. **No real device or browser is available to verify rendered behaviour** — same limitation as `U-07.7`/`U-07.8`, not newly discovered, not re-litigated at length here.

## Explicitly Out of Scope — Confirmed Untouched

Verified directly, not merely asserted: no code in this sprint touches notifications, favourites, search, AI/recommendation logic, bookmaker integration, payments, social features, marketing pages, localisation, or the API. `git`/file-level changes this sprint are limited to: `.env` configuration guidance (documentation only, see below), the three Workspace test files' new performance assertions, and this report plus governance updates.

## Defect Register

| # | Finding | Category | Status |
|---|---|---|---|
| 1 | No admin visibility into customers/analyses/Planner sessions (§3.5) | **B — High** | **Not resolved.** Requires Product Office scoping before Engineering can build it — flagged, not built, per this commission's own scope boundary. |
| 2 | No optimistic locking anywhere in the application (§3.6) | **C — Minor** | Not resolved. No evidence of real-world occurrence; would be new domain behaviour, not a defect correction. Product-wide restatement of `U-07.8`'s Planner-specific finding — not three separate findings. |
| 3 | Genuine browser/device/accessibility-tool verification still not possible (§3.9/§3.10) | **B — High, but environmental** | Unresolved, unchanged since `U-07.7`. Not a code defect — no code change can fix a missing tool. |
| 4 | `.env.example`'s `APP_DEBUG=true`/`LOG_LEVEL=debug` are correct **for local development** (Laravel's own scaffolding default) but must not be copied unchanged into a real deployment | **Operational checklist item, not a defect** | Not a code change — see Operational Readiness Checklist below. Editing `.env.example` itself would be wrong: it exists to serve local development, and a production `.env` is a separate, non-committed file whoever deploys must configure correctly regardless of what the example shows. |

No Category A (release-blocking) defect was found in this pass.

## Test Evidence (Deliverable D)

- Full suite: **403/403 passing** (up from 400 — 3 new permanent query-count regression tests added, one per Workspace screen).
- `pint --test`: clean.
- No test was weakened, skipped, or removed to reach this result.

## Operational Readiness Checklist (Deliverable E)

| Item | Status |
|---|---|
| Deployment | Not evaluated — no deployment target/process exists in this repository to assess; out of Engineering's reach in this environment. |
| Rollback | Same as above — no deployment pipeline exists yet to define a rollback path for. |
| Logging | `config/logging.php` uses Laravel's default `stack`/`single` channel, unmodified. Sufficient for a beta; no custom logging was needed or added. |
| Monitoring | None configured — no APM/error-tracking service is wired into this application. Flagged as a pre-beta gap, not built (would be a new operational integration, outside this commission's "no new capability" boundary and requiring a Product/Ops decision on which service to use). |
| Backups | Database backup strategy is an infrastructure/hosting decision, not something this repository's code can establish — out of Engineering's reach here. |
| **Configuration validation** | **Action required before any real deployment:** the actual production `.env` (not `.env.example`, which correctly keeps Laravel's local-development defaults) must set `APP_ENV=production`, `APP_DEBUG=false`, and an appropriately non-verbose `LOG_LEVEL` — none of which this report can verify from inside this environment, since no such file is committed or deployed here. |

## Release Readiness Assessment

**READY FOR CONTROLLED BETA, WITH THE FOLLOWING NOTED (not blocking):**

- Zero Category A (release-blocking) defects found across all ten reviewed areas.
- One Category B finding (Item 1, admin visibility) is a genuine pre-beta gap worth Product Office's attention — customers having problems during a beta with no support tooling beyond direct database access is a real operational risk — but it is scoping work, not a code defect, and building it under this commission would itself be the kind of scope expansion §8 forbids.
- The environmental limitations (Items 3, and the monitoring gap) are honestly stated, not fixed, because they cannot be fixed by writing code in this environment — they are exactly the kind of thing a human reviewer or a properly-provisioned environment needs to close before real customers are exposed to the product.
- Every other reviewed area — authentication, workspace, Planner, analysis engine, error handling, performance, security — passes cleanly, much of it via already-accepted validation from `U-07.8`, reconfirmed rather than re-litigated in this pass, plus new performance measurements for the screens `U-08.2` added since.

## Handover

Constitutional ownership returns to the Product Office for release determination, per this commission's own terms. `TASKS.md`, `CHANGELOG.md`, and `docs/00-governance/DECISION_LOG.md` updated accordingly.
