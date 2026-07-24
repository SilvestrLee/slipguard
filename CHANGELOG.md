# Changelog

## Unreleased

### Added
- SGOS v1.0 repository foundation.
- Permanent Claude project context.
- Governance and product documents.
- Architecture and engineering standards.
- Risk engine contract.
- UX, quality, compliance, and delivery guidance.
- ADR foundation.
- Active task list and incident log.
- `is_internal` flag on users, defaulting to false, with a corresponding factory state.
- Filament operations panel, restricted to internal users.
- `slipguard:health` diagnostic command covering app key, database, cache, queue, and storage checks.
- `slipguard:make-internal-user` command to promote a user to internal staff, guarded against accidental production use.
- Pest coverage for operations panel access, the health check command, the user-promotion command, and `is_internal` defaults.
- Repository and SGOS context audit, covering documentation health, security posture, testing readiness, and E-02 implementation ordering.
- ADR-005, the authentication strategy (Laravel Breeze, Livewire stack) — accepted, see Sprint E-02A entry below.
- `docs/00-governance/SGOS_VERSION.md`, formally declaring SGOS v1.0 and its versioning policy.
- Repository root `README.md`, rewritten as a five-minute onboarding document referencing SGOS instead of duplicating it.
- Documentation-authority notice in `docs/README.md` establishing SGOS as authoritative over conflicting documents.
- First incident log entry (`INC-2026-001`) recording the duplicate-documentation bootstrap incident and its resolution.

### Changed
- Project documentation now follows just-in-time documentation.
- Enabled `RefreshDatabase` for the Pest feature suite.
- Duplicate bootstrap-era documentation (`docs/01-foundation/`, `02-product/`, `03-architecture/`, `04-intelligence/`, `06-delivery/`, `07-decisions/`) archived, unmodified, to `docs/_legacy-bootstrap/` and marked superseded by SGOS v1.0.

## SGOS v1.0 Final Stabilization

### Added
- Documentation Freeze section in `CLAUDE.md`, governing when SGOS may be expanded going forward.
- `docs/00-governance/REPOSITORY_STATE.md`, declaring current repository maturity, architecture stability, and active sprint.
- `docs/00-governance/WORKING_PRINCIPLES.md`, capturing how engineering should operate day to day.
- "Where to Begin" pointer in the root `README.md`.

### Changed
- `ADR-005-AUTHENTICATION-STRATEGY.md` corrected from Accepted to **Proposed — Pending Founder Approval**; it was previously recorded as accepted without an explicit founder decision. `docs/adr/ADR-INDEX.md` updated to match.
- `TASKS.md` updated to reflect that authentication implementation is blocked on ADR-005 approval, not ready to start.
- Reviewed `docs/_legacy-bootstrap/` against SGOS; confirmed no unique architectural decisions exist there that SGOS lacks (see audit report).

### Fixed
- None.

### Not Done
- No feature implementation. No UI, Livewire components, Breeze installation, routes, migrations, controllers, or models were touched — governance and documentation only.

## Sprint E-02A — Customer Identity Foundation

### Added
- Customer authentication via Laravel Breeze (Livewire/Volt stack): registration, login, logout, password reset, session and CSRF protection.
- Permanent customer layout (`resources/views/layouts/app.blade.php`) with header, navigation, user menu, notifications placeholder, and footer.
- Customer navigation (`resources/views/livewire/layout/navigation.blade.php`): Dashboard, Analyze Slip, History, Journal in the primary nav; Profile, Settings, Help in the account menu.
- First-use dashboard (`resources/views/dashboard.blade.php`): welcome message, primary CTA, plain-language explanation, empty states for recent analyses and journal, getting-started tips.
- "Coming soon" screen (`resources/views/coming-soon.blade.php`) for Analyze Slip, History, Journal, Help, and Settings, rendered on the permanent layout rather than as dead links.
- Profile management (view/edit name and email, change password) via restyled Breeze Livewire components.
- `App\Policies\UserPolicy`, establishing the ownership convention (`view`/`update` scoped to the model instance, never a client-supplied ID) that future customer-owned resource policies will follow.
- On-brand public homepage, guest layout, and wordmark, replacing the default Laravel/Breeze scaffolding; `APP_NAME` set to SlipGuard.
- Pest coverage: guest redirects and authenticated access for every workspace route, dashboard content, coming-soon pages, and the authorization policy — 52 tests passing (up from 14).

### Changed
- ADR-005 accepted — the Sprint E-02A directive constituted founder approval of the recommended option (Laravel Breeze, Livewire stack). `docs/adr/ADR-INDEX.md` and `docs/00-governance/DECISION_LOG.md` updated to match.
- Restored the project's existing Tailwind v4 (`@tailwindcss/vite`) setup after Breeze's installer silently downgraded `package.json`, `vite.config.js`, and `resources/css/app.css` to a Tailwind v3/PostCSS configuration.
- `routes/web.php` regenerated by the Breeze installer had dropped the `/health` endpoint; restored it, and grouped all workspace routes under `auth` middleware.

### Removed
- Email verification (routes, controller, Volt page) and the `verified` middleware on the dashboard route — not approved per ADR-005's explicit non-scope, and the `User` model does not implement `MustVerifyEmail`.
- Unused default Breeze/Laravel branding: the Laravel wordmark logo component and the Livewire `welcome.navigation` component (inlined as plain Blade — no interactivity was needed).

### Out of Scope (unchanged this sprint)
Slip analysis, risk engine, weakest-leg detection, history, journal, parser, OCR, AI, payments, subscriptions, notifications, premium features, sports data, bookmaker integrations, social login, teams, MFA, enterprise identity, API tokens.

## Sprint E-02A Closeout

**Status:** Complete, Product Office approved.

Sprint E-02A (Customer Identity Foundation) is closed. Delivered: authentication (Breeze, Livewire/Volt), the permanent customer workspace layout and navigation, the first-use dashboard, profile management, the `UserPolicy` ownership convention, and on-brand public/guest screens. Pest: **52/52 passing**.

Engineering is authorized to begin Sprint E-03 — Manual Slip Capture.

## Sprint E-03 — Manual Slip Capture

**Status:** Delivered, pending Product Office review.

### Added
- `BettingSlip` and `BettingSlipLeg` models, migrations, and factories. `BettingSlip.user_id` is intentionally excluded from `$fillable` — ownership is always set directly from `Auth::user()`, never from client input.
- `App\Policies\BettingSlipPolicy` — extends the `UserPolicy` ownership convention (`view`/`update`/`delete` scoped to `user_id`; `viewAny`/`create` open to any authenticated user).
- `App\Actions\BettingSlip\SaveBettingSlip` — transactional create/update that replaces a slip's legs in one write.
- Slip builder (`/analyze/create`, `/analyze/{bettingSlip}/edit`): add, remove, reorder, and edit legs (sport, competition, event, market, selection, decimal odds), with plain-language validation errors.
- Slip index (`/analyze`, replacing its former "coming soon" placeholder): lists only the current user's own slips, "No slips yet." empty state, delete with a confirmation modal.
- `config/slipguard.php` — configurable `min_legs` (1) and `max_legs` (20).
- Pest coverage: create, update (legs fully replaced), delete, zero-leg rejection, max-legs enforcement, per-field validation, guest redirects, cross-user forbidden access, index scoped to the owner only.

### Changed
- `/analyze` now serves the real slip index instead of the "coming soon" placeholder used since Sprint E-02A. History, Journal, Help, and Settings remain placeholders.

Pest: **62/62 passing** (up from 52).

### Out of Scope (confirmed untouched)
Risk engine, weakest leg, risk score, confidence score, accumulator tax, history logic, journal logic, OCR, image upload, bookmaker parser, live odds, notifications, payments, premium, subscriptions, AI, and anything related to analyzing a slip. This sprint only creates and stores betting slips.

## Sprint E-03B — Slip Domain Hardening

**Status:** Delivered, pending Product Office review. Directed as "Sprint E-04," tracked as E-03B since it contains no mathematics — see the naming note in `TASKS.md` and `docs/08-operations/DELIVERY_ROADMAP.md`'s existing E-04 definition (Deterministic Risk Analysis, still blocked).

### Added
- `App\Domain\BettingSlip\BettingSlipStatus` — the slip lifecycle enum (Draft, Ready, Analysed, Archived) with explicit `allowedTransitions()`.
- `App\Exceptions\InvalidBettingSlipTransitionException` and `App\Exceptions\BettingSlipNotEditableException`.
- `App\Domain\BettingSlip\AnalysisEligibility` / `AnalysisIneligibilityReason` — `BettingSlip::analysisEligibility()` / `isAnalysisEligible()`, so the future Risk Engine never has to ask whether a slip is ready.
- `App\Domain\BettingSlip\BettingSlipValidationRules` — centralizes slip/leg validation rules and messages in one place instead of inline in the Livewire component.
- `App\Rules\NoDuplicateBettingSlipLegs` — rejects two legs describing the same event, market, and selection.
- `BettingSlip::markReady()`, `returnToDraft()`, `markAnalysed()`, `archive()` — the only way a slip's status changes; each validates the transition before saving.
- Lifecycle UI: status banner and contextual actions (Mark as Ready / Return to Draft / Archive) on the builder; status badges, a status filter, and four distinct empty states (no slips, no drafts, no ready slips, no completed slips, no archived slips) on the index.
- UI polish: leg counter ("X of Y legs"), top-level validation error banner, `wire:loading` states on Save and Add Leg, disabled state on the Add Leg button at the configured maximum.
- `ADR-006` — recorded the decision not to add separate content versioning to `BettingSlip`; the lifecycle already makes a locked slip immutable.
- Pest coverage: valid and illegal lifecycle transitions, analysis eligibility for every reason, immutable-input enforcement at both the action and UI layer, duplicate-leg rejection, ownership on the new lifecycle actions, deletion at every lifecycle stage, and status filtering (29 new tests).

### Changed
- `App\Actions\BettingSlip\SaveBettingSlip` now refuses to edit a slip that has left Draft, throwing `BettingSlipNotEditableException`.
- The slip builder renders read-only (disabled fields, hidden add/remove/reorder/save controls) once a slip is Ready, Analysed, or Archived.
- `BettingSlipFactory` gained `ready()`, `analysed()`, and `archived()` states.

### Not Done (by design)
- No soft delete or restore — considered per the sprint's persistence-hardening review and not implemented; nothing currently requires recovering a deleted slip. Hard delete was allowed at any lifecycle stage at the time — **narrowed in Sprint E-05A below** once analysis durability became a real concern.
- No new database indexes — `user_id` is already indexed via its foreign key; no genuine inefficiency was found at this scale.
- No mathematics, risk scoring, weakest-leg detection, or anything analysis-related — `BettingSlip::markAnalysed()` exists as a domain contract for E-04 to call, but nothing in this sprint calls it.

## Sprint E-05A — Football Taxonomy & Deterministic Normalization

**Status:** Delivered, pending Product Office / Data Science review. See the naming note in `TASKS.md` — tracked as directed (E-05A), though this is engine-preparation work that precedes E-04's mathematics rather than a part of canonical E-05 (Risk Report).

### Changed — Deletion Policy Correction (preliminary task)
- `BettingSlipPolicy::delete()` now permits **Draft and Ready only**. Analysed and Archived slips can no longer be deleted through the normal workflow — archiving is the only removal action available once a slip has been analysed. This protects analysis durability once `SlipAnalysis` records exist (they would otherwise be cascade-deleted or orphaned).
- Recorded as an addendum to `ADR-006` (no new ADR needed) plus a `docs/00-governance/DECISION_LOG.md` row.
- Slip index UI: Delete hidden for Analysed slips (replaced with "Archive to remove"); no action shown for Archived slips.
- Existing lifecycle tests updated: the old "a slip can be deleted at any lifecycle stage" test is replaced with separate deletable (Draft/Ready) and non-deletable (Analysed/Archived) coverage.

### Added — Football Taxonomy & Normalization
- Taxonomy version `1.0` (`FootballMarketTaxonomyV1::VERSION`) and its policy record, `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md`.
- `App\Domain\Risk\Normalization\NormalizeSport` — football-first sport normalizer; distinguishes `unsupported` (a real named sport outside v1 scope, e.g. Tennis) from `unrecognized` (no match at all). Exact alias matching only, no fuzzy matching.
- `App\Domain\Risk\Taxonomy\{MarketFamily,MarketComplexity,MarketDefinition,FootballMarketTaxonomyV1}` — nine football market families with stable codes and fixed complexity (Match Result, Double Chance, Draw No Bet, Total Goals, Both Teams to Score, Team Total Goals, Correct Score, Half-Time Result, Half-Time/Full-Time). Handicap markets deliberately deferred.
- `App\Domain\Risk\Normalization\NormalizeFootballMarket` — deterministic matching pipeline (normalize → exact alias lookup → controlled pattern extraction for total-goals line / BTTS yes-no / correct score) with `NormalizationStatus`: complete, partial, unrecognized, unsupported.
- `App\Domain\Risk\Normalization\{NormalizeBettingSlip,NormalizedBettingSlip,NormalizedBettingSlipLeg}` — the snapshot-normalization boundary. Reads a Ready slip's legs and returns normalized results; throws `App\Exceptions\BettingSlipNotReadyException` for Draft/Analysed/Archived. Does not calculate risk, persist an analysis, or change slip state.
- `slipguard:normalize-slip {bettingSlip}` — developer diagnostic console command (table output). No Filament taxonomy resource was built, per scope.
- Pest coverage: every approved alias, all 8 worked fixtures (NT-001–NT-008) from the sprint brief, generalized total-goals line detection beyond the literal "2.5" example, determinism, raw-text preservation, Ready-only lifecycle enforcement, max-legs support, and the deletion-policy regression above (67 new tests: 60 Unit, 7 Feature).

### Not Done (by design / explicitly out of scope)
- No database changes — normalization runs entirely at analysis-preparation time against the existing free-text `sport`/`market_name`/`selection_name` columns; no taxonomy columns added to `betting_slip_legs`.
- No risk score, weakest-leg detection, accumulator tax, data-quality formula, `SlipAnalysis`/`LegAnalysis` persistence, input fingerprinting, analysis-request idempotency, full engine orchestration, AI explanation, customer risk report, OCR, bookmaker parsing, live sports data, or correlation scoring.
- No UI changes to the slip builder beyond what already existed — sport and market remain free text; a picker/selector was not introduced.

Pest: **159/159 passing** (up from 92).

### Fixed — Code Review (medium effort, 8 finder angles + verification)
- `NormalizeFootballMarket`: total-goals direction detection now prefers the selection text over the market text, fixing a bug where an ambiguous market alias (e.g. "Over Under Goals") always resolved to "over" regardless of the actual selection ("Under 2.5").
- `BettingSlip::analysisEligibility()` now requires the Ready state, fixing a contract inconsistency where a Draft slip with complete legs reported itself eligible while `NormalizeBettingSlip::execute()` would reject the same slip. Added `AnalysisIneligibilityReason::NotReady`.
- The builder's `archive()` and `returnToDraft()` now catch `InvalidBettingSlipTransitionException` (matching `markReady()`'s existing pattern), turning an uncaught 500 on a double-transition race into a graceful flash message.
- `NoDuplicateBettingSlipLegs` now trims each field individually before building the dedup key, fixing a whitespace edge case where two legs identical except for incidental internal whitespace weren't flagged as duplicates.
- The builder's `save()` now flashes a message when a stale, no-longer-editable form is submitted, instead of silently no-op'ing.
- Added test coverage for non-owner deletion attempts on Draft/Ready slips (previously only tested against Analysed/Archived).
- Five lower-priority cleanup/efficiency findings (taxonomy rebuilding its definition array on every lookup, lifecycle-status rules duplicated across the policy and both views instead of asking the enum, hand-rolled text normalization repeated across four files) were identified and deliberately left unfixed — see the code-review report for detail.

Pest: **167/167 passing** (up from 159).

### Pre-Commit Correction

- Corrected the sprint report's stale framing: the taxonomy-location and unrecognized-market-baseline questions raised in the earlier Stage 1 contract review (`next-step.md`) were already resolved by this sprint's own implementation (`app/Domain/Risk/Normalization`, nine market families covering common accumulator markets) — the report incorrectly still called them open. No code change; documentation/report accuracy only.
- Style: ran `./vendor/bin/pint` — fixed formatting in 8 files this sprint had touched (mostly `new Foo()` → `new Foo`, brace positioning) that the earlier review pass missed.
- Created 12 genuine project skills under `.claude/skills/` (`laravel-engineering`, `livewire-product-interface`, `filament-operations`, `pest-verification`, `domain-modelling`, `deterministic-risk-mathematics`, `explainability-responsible-betting`, `security-authorization`, `database-migration-safety`, `accessibility-review`, `documentation-stewardship`, `sprint-verification`), each `user-invocable: false` (background domain knowledge, not user slash-commands). Confirmed discovered and loadable in the same session immediately after creation.
- No `.claude/rules/` path-enforcement system was created — this is not a verified Claude Code feature, and fabricating one would create false confidence rather than real governance. "UI UX Pro Max" and a "21st.dev" MCP server were confirmed absent from this environment (no `.mcp.json`, no such plugin installed) and were correctly unneeded this sprint since no material UI design work occurred.

Pest: **167/167 passing** (unchanged — no application code changed in this correction).

## Sprint E-06A — Deterministic Risk Factor Mathematics (Design Only)

**Status:** Design proposed — `READY WITH OPEN DECISIONS`, not accepted. No implementation occurred; this sprint produced exactly one authoritative document plus the required documentation-index updates.

### Added
- `docs/03-data-science/RISK_RULE_SET_2026_1.md` — the complete proposed mathematical specification for Engine v1: exact formulas, thresholds, and maximum contributions for all six factors (Leg Count, Combined Odds, Individual Odds Elevation + Outlier, Risk Concentration, Market Complexity, Relationship [disabled]); two group interaction caps with proportional-scaling adjustment; an achievable-ceiling rescale (78 → 100); risk bands; independent data-quality scoring and bands; a resolved Limited Analysis policy; an updated, authoritative reason-code catalogue; 12 mathematical invariants; and 18 fully computed test vectors.
- Pointer from `docs/03-data-science/RISK_ENGINE.md` to the new rule-set document.
- A `Proposed` row in `docs/00-governance/DECISION_LOG.md` for Rule Set 2026.1.

### Key Design Decisions
- **Rejected every logarithm-based candidate formula** (leg-count and combined-odds log-normalization, log-odds-share concentration) after confirming by direct reflection against the installed `brick/math` library that `BigDecimal` has no `ln`/`log`/`exp` method at all — using one would require binary floating point, which the decimal precision policy and ADR-002 both forbid. Replaced with piecewise-linear interpolation (combined odds, individual-odds sub-factors) and a Herfindahl-style concentration index built on linear `(odds − 1)` shares instead of log-odds shares.
- **Corrected the sprint brief's own decimal-precision assumption**: odds input is 2 decimal places (`betting_slip_legs.decimal_odds DECIMAL(6,2)`), not the 4 the brief's "recommended starting direction" proposed — verified against the actual migration, not assumed.
- **Found `brick/math` is already installed** (transitively, via `laravel/framework`) — not a new dependency requiring justification, only a possible direct-requirement formalization.
- **Identified a real prerequisite for E-06B**: `NormalizeBettingSlip` (Sprint E-05A) normalizes every leg's market against the football taxonomy regardless of that leg's sport-normalization result. Market Complexity (RF-005) must gate on `sport.status === Complete` before trusting a leg's complexity — not yet implemented anywhere, a requirement on the future scoring layer.
- Resolved the Limited Analysis Policy to a single data-quality gate (score ≥ 40 produces a full score with its quality band disclosed; below 40 produces no score and the slip stays `Ready`) rather than the three separate, potentially-conflicting gates the brief posed as options.
- All 18 test vectors computed via a `BigDecimal` reference script for this document, not hand arithmetic — verified zero floating-point involvement end to end.

### Not Done (by design — scope)
- No scoring classes, rule-set PHP classes, `brick/math` `composer.json` change, database migration, `SlipAnalysis`/`LegAnalysis`, analysis request IDs, input fingerprints, calculation persistence, weakest-leg selection, customer reports, Filament work, AI, or UI changes of any kind.

### Open (blocking E-06B — see the rule-set document's §22)
Seven explicit decisions: the factor tables themselves, the two group cap values, the Limited Analysis data-quality floor, two recommended-but-missing boundary vectors, the RF-005 sport-gating requirement, the `MARKET_UNRECOGNIZED` reason-code catalogue addition, and the timing of making `brick/math` a direct dependency.

Pest: **167/167 passing** (unchanged — no application code changed this sprint).

## Fix — Cross-Sport Normalization Isolation (commit `7d13c27`)

### Fixed
- `App\Domain\Risk\Normalization\NormalizeBettingSlip` no longer runs football-market normalization against a leg unless that leg's sport was recognized as football (`sport->sportCode === NormalizeSport::FOOTBALL_CODE`). Previously every leg was normalized through `FootballMarketTaxonomyV1` regardless of sport, risking a Tennis/Basketball/Cricket leg being misclassified through a coincidentally overlapping market phrase (e.g. "Match Result").
- `App\Domain\Risk\Normalization\NormalizedMarket::notClassifiedForSport()` — new factory method returning `market_code: null`, `complexity: unknown`, and `status` mirroring the leg's own sport status (`unsupported`/`unrecognized`), with raw market/selection text always preserved.

### Added
- 6 regression tests in `tests/Feature/Risk/NormalizeBettingSlipTest.php`: a football no-regression case, three unsupported-sport cases deliberately using overlapping football-alias phrasing (Tennis+Match Winner, Basketball+Match Result, Cricket+Total Goals) to prove no leakage, one unrecognized-sport case, and one determinism case.

Pest: **173/173 passing** (up from 167).

## Sprint E-06A Review — Prerequisite Correction and Rule-Set Resolution

**Status:** `docs/03-data-science/RISK_RULE_SET_2026_1.md` upgraded from `READY WITH OPEN DECISIONS` to `READY FOR PRODUCT APPROVAL`.

### Changed
- All seven previously-open decisions (§22 of the prior draft) individually resolved with question/choice/alternatives/consequences/recommendation for each, not summarized as a count — see the rule-set document's new §22.1–22.7.
- **Redesigned the analysis-availability gate into three tiers**: a sport hard-gate (any non-football leg → unavailable, per Product Office's explicit recommendation), a 25% market-unrecognized-proportion gate, and a partial-normalization deduction score — replacing the single data-quality threshold from the prior draft, after finding a pure score-based gate could disagree with a proportion-based reading of the same slip.
- **Proved the 78-point achievable ceiling is exactly reachable**, not merely theoretical: a new vector (20 legs, one concentrated outlier, every market complex) saturates both group caps and Market Complexity simultaneously, scoring exactly 100.
- **Proved a single-leg slip's true ceiling is 54 (High)** and **a two-leg slip can reach 77 (Very High)** under deliberately extreme construction — both previously derived, now backed by real computed vectors.
- **Data quality's scope narrowed**: sport-level problems are no longer a soft deduction blended into the data-quality score — they're the Tier 1 hard gate. Data quality (§16) now measures only how well an all-football slip's markets normalized.
- Reason-code catalogue finalized: added `RELATIONSHIP_FACTOR_NOT_EVALUATED` (always emitted, replacing two previously-dormant codes), `SPORT_UNSUPPORTED`, `ANALYSIS_LIMITED`, `ANALYSIS_UNAVAILABLE`.
- Risk bands (0–24/25–49/50–74/75–100) validated against a full 20-vector distribution (8 Low, 5 Moderate, 4 High, 3 Very High) rather than retained by default.

### Not Done (by design — scope)
No production scoring classes, no database migration, no persistence, no weakest-leg implementation, no UI, no AI, no external sports data. The only code touched in this review was the normalization-boundary fix above, committed separately from the still-unapproved design document per the review's own commit gate.

Pest: **173/173 passing** (unchanged from the fix above — no additional code changed in the document review itself).
