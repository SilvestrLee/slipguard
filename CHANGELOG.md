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

### Fixed
- Public navigation transitions no longer shift the header or theme controls between routes. The sticky header now keeps fixed padding and logo geometry while scroll state changes elevation only, and a stable scrollbar gutter prevents horizontal movement between short and tall pages.
- Light theme no longer flashes a dark background during `wire:navigate` transitions. A root-theme attribute guard preserves the saved explicit preference while Livewire morphs the incoming document, before the post-navigation synchronizer runs.
- The light/dark toggle no longer jumps to its default position on every navigation. Its thumb position and icon colours now derive directly from the pre-paint root theme, remaining visually correct while Alpine rebinds the reconstructed control.
- Added regression coverage for stable navigation geometry, pre-paint toggle state, and Livewire theme-attribute preservation; the focused shell/theme/public-page suite passes 55 tests with 461 assertions.

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

## Sprint E-06B — Deterministic Risk Engine Implementation

**Status:** Delivered, pending Product Office / Data Science review.

### Added
- `App\Domain\Risk\Engine\CalculateStructuralRisk` — the engine's entry point. Pure calculator: accepts a `NormalizedBettingSlip`, returns an immutable `RiskAnalysisResult`. Never persists, never mutates the slip, never fetches external data. Follows Rule Set 2026.1 §7's calculation order exactly.
- `App\Domain\Risk\RuleSets\RuleSet2026_1` — exposes every weight, cap, band boundary, and version constant; "no hidden mathematics."
- `App\Domain\Risk\Contracts\RiskFactor` and all six factor implementations (`LegCountFactor`, `CombinedOddsFactor`, `IndividualOddsFactor`, `RiskConcentrationFactor`, `MarketComplexityFactor`, `RelationshipFactor` — RF-006 remains inactive, contribution 0, always visibly marked via its reason code).
- `App\Domain\Risk\Engine\CalculateDataQuality` and `DetermineAnalysisAvailability` — the independent data-quality score and the three-tier analysis-availability gate (§17), never blending with structural risk.
- Immutable result objects: `RiskAnalysisResult`, `FactorResult`, `FactorTrace`, `InteractionAdjustment`, `RiskBand`, `DataQualityBand`, `AnalysisAvailability`, `AnalysisGateResult`, `ReasonCode` (all 18 approved codes).
- `App\Domain\Risk\Support\PiecewiseLinearInterpolation` and `ProportionalGroupCap` — shared, reusable BigDecimal arithmetic for the anchor-table factors and the two group caps (Group A ≤ 40, Group B ≤ 28).
- Pest coverage: a dedicated test file per factor (boundary tables, monotonicity, reason-code thresholds), `CalculateDataQuality` (deduction, category cap, sport exclusion), `DetermineAnalysisAvailability` (all three tiers, worse-of-band resolution), every scoreable canonical vector compared exactly against the approved rule set, and a property-style suite (order independence, determinism, bounds, monotonicity, symmetry) — 121 new tests.

### Fixed
- **`MarketComplexityFactor` (RF-005):** computed its average using native PHP float division, then passed the float into `BigDecimal::of()` — a method whose signature only accepts `BigNumber|int|string`. PHP silently coerces a float argument to `int`, truncating (e.g. `5/3 = 1.667` became `1`), which is exactly the "silent conversion to floating point" the rule set's decimal precision policy forbids. Fixed to perform the division in BigDecimal throughout. Caught by a failing test (`8.3333` expected, `5.0000` got) during the full regression run, not the earlier hand-checked vectors.
- **`IndividualOddsFactor` (RF-003) and `CombinedOddsFactor` (RF-002):** their anchor tables used PHP float literals (e.g. `1.5`) instead of strings for the same `BigDecimal::of()` call. `CombinedOddsFactor`'s anchors are all whole numbers so the defect was latent (no numeric effect), but `IndividualOddsFactor::RELATIVE_ANCHORS`' `1.5` breakpoint silently truncated to `1`, colliding with the `1.0` anchor and corrupting the whole relative-outlier table. Fixed by making every anchor table's x-values strings.

### RF-003A — Canonical Vector Correction (Product Office review)
- Investigating six vector-fidelity test failures that appeared after the fix above led to discovering that **the reference script which generated four of `RISK_RULE_SET_2026_1.md` §20's canonical vectors (TV-003, TV-004/TV-015, TV-006, TV-009) carried the identical float-truncation defect**, corrupting exactly the vectors whose max-to-median ratio falls between 1.0 and 2.0. §8's RF-003 formula itself was never ambiguous and required no change. Per Product Office's ruling, the rule set's formula is authoritative over the buggy script's output — the four affected vectors were corrected in the document instead (see `DECISION_LOG.md`). The other 15 of 19 scoreable vectors were confirmed correct throughout.
- Also corrected three test-data errors of my own, unrelated to the bug: TV-004, TV-006, and TV-018 had guessed leg complexities (`moderate`/mixed `complex`) that didn't match the rule set's own documented RF-005 column; TV-009 was missing one `complex` leg. All three are corrected to match the document exactly.

### Verified
- **Formula fidelity:** every active factor (RF-001 through RF-005) and RF-006's explicit inactivity match Rule Set 2026.1 exactly — no weight, cap, band, taxonomy, or normalization changed.
- **All 19 scoreable canonical vectors** (TV-001–TV-010, TV-014–TV-021) match the corrected rule set exactly, no tolerance. TV-013 (a single Tennis leg) is confirmed `Unavailable`, not scored — the Tier 1 sport gate (§17) supersedes the vector table's pre-gate-redesign standalone value, documented explicitly in a test.
- **Performance:** 0.615ms per calculation on a 20-leg slip (well within the 10ms target), measured over 200 iterations after warm-up.
- **Full regression:** 294/294 Pest tests passing (up from 173). `./vendor/bin/pint --test` clean.

### Not Done (by design — scope)
No persistence (`SlipAnalysis`, `LegAnalysis`, `AnalysisRequest`, `AnalysisFingerprint`, migrations, repositories) — delivered in Sprint E-06C below. No weakest-leg or highest-risk-leg identification (per-leg provisional factor contributions only — ranking is E-06D). No AI, OCR, parser, UI, premium logic, or localisation. No change to any approved weight, cap, band, taxonomy, or normalization rule.

## Sprint E-06C — Analysis Persistence

**Status:** Delivered, pending Product Office / Data Science review.

### Naming Note
This sprint's code initially labelled itself "E-06C," which collided with the roadmap's then-current E-06C (weakest-leg/highest-risk-leg ranking), so it was briefly tracked as E-06D pending clarification. Product Office then ruled (`docs/00-governance/DECISION_LOG.md`, 2026-07-25) that analysis persistence is a genuine prerequisite for U-02 — Dashboard, History, Risk Report retrieval, Journal linkage, and rule-set/version traceability cannot be built correctly against an in-memory-only `RiskAnalysisResult`. It is inserted into the sequence as **E-06C — Analysis Persistence**, and weakest-leg/highest-risk-leg ranking is renumbered **E-06D**. Sequence: E-06B (engine) → E-06C (persistence) → E-06D (weakest-leg) → U-02.

### Added
- `App\Actions\Analysis\AnalyzeBettingSlip` — the orchestration boundary between the pure Risk Engine (E-06B) and persistence. Checks `BettingSlip::analysisEligibility()`, normalizes the slip, runs `CalculateStructuralRisk`, persists the complete immutable result inside one database transaction, and transitions the slip to Analysed. Throws `App\Exceptions\BettingSlipNotAnalysableException` (carrying the specific `AnalysisIneligibilityReason`s) when ineligible — nothing is persisted and the slip is left untouched.
- `App\Models\SlipAnalysis` and its migration — one immutable row per completed analysis (`betting_slip_id` unique at the database level, `user_id` denormalized from the slip's own owner and deliberately excluded from mass assignment). Persists `availability`, a nullable `structural_score`/`risk_band` (null when Unavailable), `data_quality_score`/`band`, `limited_analysis`, and the engine's full output (`factor_results`, `interaction_adjustments`, `data_quality_deductions`, `factors_not_evaluated`, `reason_codes`) as plain, JSON-safe arrays — every `BigDecimal` and enum is reduced to a string/value by the orchestration action first, so the model carries no dependency on the engine's value objects. Records four independent version axes (`engine_version`, `rule_set_version`, `input_schema_version`, `market_taxonomy_version`) rather than one — `engine_version` was added later in this sprint, see the ADR-007 entry below.
- `App\Models\LegAnalysis` and its migration — one immutable row per leg, preserving the normalized snapshot (sport/market codes, family, complexity, status, decimal odds, raw inputs) exactly as it was at analysis time, independent of any later taxonomy version.
- `App\Policies\SlipAnalysisPolicy` — `view` only, scoped to `user_id`. A `SlipAnalysis` is never created or edited through a user-facing request.
- `NormalizedBettingSlipLeg` extended with a `decimalOdds` fixed-precision string, carried through the normalization boundary since the engine needs it.
- Relationship graph completed both ways: `BettingSlip::analysis()`, `SlipAnalysis::bettingSlip()`/`user()`/`legAnalyses()`, `LegAnalysis::slipAnalysis()`/`bettingSlipLeg()`.
- `database/factories/SlipAnalysisFactory.php` and `LegAnalysisFactory.php`.
- 15 new Pest tests (`tests/Feature/Analysis/`): availability persisted correctly for both Full and Unavailable slips (null score/band on Unavailable), per-leg snapshot fidelity and display-order, JSON round-trip fidelity for factor results and interaction adjustments, enum-collection round-trip for reason codes, every ineligibility path (Draft, empty, already-Analysed, Archived) rejected with zero rows written, one-analysis-per-slip enforced at the database level (`QueryException` on violation), confirmation the engine itself persists nothing, determinism across two independently-built slips with identical inputs, both-directions relationship retrieval, and the policy.

### Verified
- Full regression: 309/309 Pest tests passing (up from 294). `./vendor/bin/pint --test` clean.
- No change to any approved weight, cap, band, taxonomy, or normalization rule; the Risk Engine itself remains untouched and still never persists anything (asserted directly by a dedicated test).

### Not Done (by design — scope)
No weakest-leg/highest-risk-leg ranking (E-06D, a separate sprint). No UI, no AI, no `AnalysisRequest`/`AnalysisFingerprint` deduplication tracking, no history/journal surface, no localisation.

### ADR-007 — Analysis Persistence Boundary (Product Office + Architecture Office)
- **Accepted.** Formalizes the permanent boundary this sprint's code already followed: the engine (Layer 3) never touches persistence/HTTP/UI/infrastructure; persistence (Layer 4, `AnalyzeBettingSlip`) wraps the engine and never recalculates; presentation (Layer 5 — Dashboard, History, Journal, Risk Report, API, none built yet) reads persisted analyses only and must never invoke the engine or mutate a persisted record. See `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`.
- **Fixed a real gap the ADR surfaced:** `engine_version` was entirely missing from the persisted record — only `rule_set_version`/`input_schema_version`/`market_taxonomy_version` existed, despite `engine_version` being a documented requirement since the original architecture docs (`docs/02-architecture/DOMAIN_MODEL.md`, `docs/03-data-science/RISK_ENGINE.md`) and explicitly flagged "Not yet implemented" in `RISK_RULE_SET_2026_1.md` §1. Added `CalculateStructuralRisk::ENGINE_VERSION` (`1.0`), threaded through `RiskAnalysisResult` and `AnalyzeBettingSlip`, persisted as a new `slip_analyses.engine_version` column (migration edited in place — pre-commit, locally re-migrated with `migrate:fresh`, not a follow-up migration). Existing test extended with an assertion; count unchanged at 309/309 since the column is required with no default, so every call site had to supply it or the whole suite would fail.
- **Flagged, deliberately not fixed:** ADR-007 describes re-analysis (same slip analysed again → new independent record, historical records untouched) as permanent architecture, but that's not reachable today — `slip_analyses.betting_slip_id` is a unique database constraint and `Analysed` is a terminal `BettingSlipStatus` (E-03B; no transition back to `Ready`). Closing this gap means dropping the unique constraint, likely moving `BettingSlip::analysis()` to a `latestOfMany()` relation alongside a plain `HasMany`, and revisiting the E-03B lifecycle rule — a real behavior change to a previously locked decision, not a documentation fix. Recorded in `docs/00-governance/DECISION_LOG.md` (2026-07-25) and left for an explicit Product Office decision rather than silently implemented or silently left unmentioned.

Pest: 309/309 passing (unchanged — see above). `./vendor/bin/pint --test` clean after the `engine_version` addition.

## Sprint U-01 — SlipGuard UX Foundation

**Status:** Delivered. Documentation only — no frontend components, pages, or placeholder screens were built, per the sprint's explicit scope.

### Added
- `docs/05-ux/DESIGN_LANGUAGE.md` — the UX constitution: philosophy, emotional goals, product personality, hierarchy, white-space/typography/layout philosophy, trust-first principles, progressive disclosure, cognitive load reduction, data explanation philosophy, brand tone, and constraints.
- `docs/05-ux/VISUAL_INSPIRATION.md` — approved characteristics (generous spacing, restrained colour, calm interactions, etc.) and explicitly rejected ones (casino colours, gambling imagery, flashing indicators, dense dashboards, etc.), each with its reasoning.
- `docs/05-ux/MOTION_SYSTEM.md` — durations, easing, allowed transitions, hover/loading/scroll behaviour, micro-interactions, reduced-motion accessibility, and forbidden animation patterns.
- `docs/05-ux/COMPONENT_PRINCIPLES.md` — purpose, spacing, radius, elevation, interaction, accessibility, responsive behaviour, usage rules, and anti-patterns for every component the product currently needs (buttons, cards, badges, risk indicators, navigation, section headers, hero blocks, forms, upload areas, analysis cards, journal cards, reports, timeline, progress indicators).
- `docs/05-ux/HOMEPAGE_STORYBOARD.md` — the seven-section homepage narrative (Hero → Problem → How It Works → Example Report → Trust → Journal → CTA), each section's purpose, emotion, message, visual priority, interaction, and exit action.
- `docs/05-ux/DESIGN_TOKENS.md` — colours, typography, spacing scale, radius scale, elevation, opacity, animation timing, container widths, grid, breakpoints, icon sizes, and button heights, mapped to Tailwind v4's `@theme` CSS-variable approach.
- `docs/05-ux/ACCESSIBILITY.md` — contrast ratios, keyboard navigation, focus states, screen reader expectations, motion reduction, touch targets, readable typography, colour independence, and dark mode considerations.
- `docs/05-ux/ICONOGRAPHY.md` — preferred icon style (outline, 1.5px stroke, soft joins), approved metaphors (shield, magnifier, warning, check), and icons to avoid (football, money bags, casino chips, slot machines, roulette, confetti).
- `docs/05-ux/IMAGE_GUIDELINES.md` — approved imagery (minimal illustrations, abstract shapes, ticket mockups, shield graphics, product screenshots) and rejected imagery (sport action photography, celebrations, fans/crowds, bookmaker screenshots, casino imagery).
- `docs/05-ux/RESPONSIVE_RULES.md` — desktop/tablet/mobile behaviour for containers, spacing, typography, stacking, navigation, cards, touch spacing, and scrolling.
- `CLAUDE.md`'s new Frontend Work Rule: before any UI implementation, review `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `DESIGN_TOKENS.md`, and `HOMEPAGE_STORYBOARD.md` — documents take precedence over conflicting implementation choices.
- `PROJECT.md`'s new "UX Foundation Documents" pointer.

### Note — Directory Correction
The sprint brief specified `docs/06-ux/`. That number is already used by `docs/06-engineering/`, and a UX directory already existed at `docs/05-ux/` (containing `UX_RULES.md`, referenced throughout existing skills and `CLAUDE.md`'s Required Reading). All ten documents were added to the existing `docs/05-ux/` instead of creating a colliding, duplicate-numbered folder.

### Not Done (by design — scope)
No frontend components, Blade views, Livewire components, or Filament resources were built or modified. No redesign of any existing screen.

## Sprint U-01A — UX Foundation Governance Hardening

**Status:** Delivered. Documentation and governance only — no frontend code changed.

### Added
- `docs/05-ux/EXPLAINABILITY_SYSTEM.md` — the constitutional guide for how every analysis screen communicates its results (not how the engine calculates them): philosophy, principles, the Explanation Hierarchy (reconciled explicitly against `UX_RULES.md`'s existing Risk Report Hierarchy, not a silent replacement), progressive disclosure (what's visible vs. hidden by default), always/never language rules, customer-trust rules for limitations, and a Future Compatibility section (weakest-leg, rule-set versioning, historical comparisons, the future relationship factor, multiple sports) that documents only the accommodation, not the unbuilt features themselves.
- `docs/05-ux/EMPTY_STATES.md` — 15 empty/error/unavailable states (no slips, no analyses, no journal entries, no archived slips, no history, no notifications, no saved reports, unavailable analysis, unsupported sport, no OCR results, future parser unavailable, network failure, permission denied, unexpected error, maintenance mode), each with purpose, headline, supporting text, recommended illustration, primary/secondary CTA, user emotion, and accessibility considerations. The two OCR/parser-related states are explicitly marked reserved placeholders (OCR and bookmaker parsing are out of MVP scope, `PROJECT.md`) rather than designed speculatively.
- `docs/05-ux/TRUST_SIGNALS.md` — every reserved trust mechanism (versioned rule sets, deterministic analysis, data quality independence, analysis timestamps, normalization status, supported/unsupported sports, limited/unavailable messaging), a permanent forbidden-language list, and a required-vocabulary list, plus where each trust signal is required to appear.

### Changed
- Every document in `docs/05-ux/` (all 10 from Sprint U-01, plus the pre-existing `UX_RULES.md`, plus the 3 new documents above — 14 total) now begins with a consistent Version / Status / Applies To / Owner / Last Updated / Related Documents header (a table, not YAML front matter) and cross-references its related documents — no orphan documents.
- `CLAUDE.md`'s Frontend Work Rule strengthened: the full applicable-document list now includes the three new documents; added a rule that no new component/interaction/animation/spacing/typography/colour/icon/illustration pattern may be introduced without first being documented in `docs/05-ux/`; added an explicit Gap Rule (stop → extend the UX Constitution → get Product Office approval → resume, rather than inventing UX decisions in code).
- `PROJECT.md`'s UX Foundation Documents pointer updated to list all 14 documents and the Gap Rule.

### Not Done (by design — scope)
No frontend components, Blade views, Livewire components, Filament resources, Tailwind config, CSS, or JavaScript were built or modified. No existing UX philosophy from Sprint U-01 was contradicted — only extended and cross-referenced.

## Sprint E-06C Validation — Production-Ready Foundation Validation

**Status:** Delivered. Engineering validation only — no product behaviour, mathematics, taxonomy, UX, or feature code was changed.

### Added
- `docs/engineering/` — twelve deliverables from a full engineering validation sprint (repository audit, ADR-007 architecture boundary check, static analysis, database validation, persistence integrity, performance benchmark, security review, test review, documentation sync, engineering debt register, production readiness assessment, and the master `engineering-validation-report.md`).
- Final engineering recommendation: **READY WITH OBSERVATIONS**. Zero Category A (release-blocker) findings across all ten audit stages; 309/309 Pest tests passing (799 assertions), `pint --test` and `composer validate` clean. Two dormant Category B items and fourteen Category C items logged to `docs/engineering/engineering-debt-register.md` for future sprints; three Category D items logged as Product/Architecture Office observations tied to unstarted roadmap work (U-02's presentation layer, ADR-007's already-tracked re-analysis gap).

### Findings of note (non-blocking, tracked — see the debt register for full detail)
- `NormalizeBettingSlip` (Domain/Normalization layer) reads the Eloquent `App\Models\BettingSlip` directly — a boundary-purity softness, not a call-flow violation of ADR-007.
- `slip_analyses`/`leg_analyses` foreign keys use `cascadeOnDelete()`; a hard delete of a `BettingSlip`/`User` would silently remove historical analysis records. Currently unreachable via any customer path — flagged for explicit Architecture Office resolution before any future account/admin-deletion feature.

### Environment note
The local Herd-linked `php`/`php83` binaries fail at process start (`dyld` symbol error, unrelated to the repository). All validation commands were run via `/usr/local/bin/php` (Homebrew, 8.5.8), which satisfies `composer.json`'s `^8.3` constraint.

### Not Done (by design — scope)
No code was modified. No product/UX/mathematics decisions were made or changed. Customer-facing engineering (U-02 and beyond) is cleared to proceed per this sprint's final recommendation.

## Milestone — Production Foundation Certified, Platform Engineering Closed

**Status:** Delivered. Formal close-out of Platform Engineering following the E-06C Validation sprint's `READY WITH OBSERVATIONS` recommendation.

### Added
- `docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md` — the permanent engineering certification of the SlipGuard platform foundation: governance version axes (Rule Set 2026.1, Risk Engine 1.0, Input/Analysis Schema 1.0, Football Taxonomy 1.0, Governance 1.0, UX Constitution 1.0), the full validation-stage summary, test/performance/architecture/security/documentation summaries, outstanding engineering debt overview, the final `READY WITH OBSERVATIONS` recommendation, platform status, and the Platform Engineering → Customer Experience Engineering transition statement.

### Recorded, Not Resolved
- The certificate explicitly notes that **Risk Rule Set 2026.1 has not yet received formal Product Office / Data Science sign-off** (`docs/00-governance/DECISION_LOG.md` still records it as "Proposed, ready for approval"). This is a governance fact carried into the certificate, not something Engineering can or does resolve — it does not block certifying the engineering foundation that implements the rule set.
- The certificate also records that the repository is **not** git-clean at the moment of certification (12 modified, 83 untracked files spanning several already-delivered but uncommitted sprints — E-06B, E-06C, ADR-007, U-01/U-01A, the E-06C Validation sprint itself, and this milestone). Content validity and commit status are recorded as independent facts.

### Not Done (by design — scope)
No further engineering debt was sought out. No refactoring, redesign, or recalibration of deterministic mathematics occurred. This milestone closes Platform Engineering; future work proceeds under Customer Experience Engineering (see `TASKS.md`).

## Sprint G-01 — Governance Consolidation

**Status:** Delivered. Documentation only — no PHP, Blade, Livewire, Tailwind, migrations, database, tests, Risk Engine, Rule Set, persistence, or UX implementation was changed.

### Added
- `docs/00-governance/ENGINEERING_CONSTITUTION.md` — the permanent authority-and-change-governance reference: an Authority Matrix (Product Office, Data Science Lab, Architecture Office, Engineering Office, UX Studio), the Behaviour Change Policy (stop/document/escalate on ambiguity), Certified Architecture Preservation, Repository Evolution Philosophy, the Documentation Authority Hierarchy, an Observability Convention (documentation only), a Refactoring Policy, and the Foundation Freeze (the nine components now baseline infrastructure as of certifying commit `18d1c4c`, and the four ways they may still evolve).
- `docs/00-governance/GOVERNANCE_CONSOLIDATION_G01.md` — the audit trail behind the Constitution: which existing repository decisions each section codifies, a repository validation report (no duplicated governance, no conflicting authority definitions, no orphan documents, all cross-references resolve), a cross-reference table mapping the Documentation Authority Hierarchy's nine tiers to real files, a summary of the nine constitutional additions, and a final governance audit against all ten G-01 success criteria.

### Changed
- `CLAUDE.md`'s Source-of-Truth Order now points to the Engineering Constitution under "Governance."
- `PROJECT.md` gained a short "Engineering Constitution" pointer section, alongside the existing UX Foundation Documents pointer.
- `docs/adr/ADR-INDEX.md` gained a one-line pointer to the Constitution for authority/escalation questions, without restating its content.
- `docs/00-governance/DECISION_LOG.md`: added the Production Foundation certification and the Engineering Constitution consolidation as Locked decisions.
- `docs/00-governance/REPOSITORY_STATE.md` refreshed — it was still dated 2026-07-23 and named E-02A as the current sprint; now reflects Production Foundation Certified status, G-01, and the Customer Experience Engineering focus. (This document is explicitly a living snapshot, not a durable record, per its own Note — refreshing it is expected maintenance, not scope creep.)

### Explicitly Not Done (by design — scope)
No new governance was invented — every addition traces to a prior, demonstrated repository decision (see the audit trail). No Locked decision was changed. No code, test, migration, Risk Engine, Rule Set, persistence, or UX implementation was touched.

## Sprint U-02 — Customer Dashboard Experience

**Status:** Delivered — **Product Office approved**, 2026-07-25. The first customer-facing screen built against the UX Constitution (`docs/05-ux/`, U-01/U-01A) and the certified Production Foundation.

### Added
- `resources/views/livewire/dashboard.blade.php` — a full-page Volt component replacing the E-02 placeholder dashboard: Hero with one primary CTA and a conditional secondary CTA, Recent Analyses (non-interactive — no Risk Report screen exists yet to link to), a scoped-down Progress section (literal counts only), a Journal preview (permanent empty state — Journal isn't built), and a Trust panel using only `TRUST_SIGNALS.md`'s approved language.
- Concrete colour values for every token `DESIGN_TOKENS.md` had named but never specified (neutral scale, accent, 4 risk-band colours, 4 data-quality-band colours, light + dark) — proposed by Engineering, approved by the founder, documented in `DESIGN_TOKENS.md`, implemented in `resources/css/app.css` as Tailwind v4 `@theme` tokens with a `prefers-color-scheme: dark` override layer and a global `prefers-reduced-motion: reduce` rule (`MOTION_SYSTEM.md`).
- A skip-to-content link in `resources/views/layouts/app.blade.php` (`ACCESSIBILITY.md` requires one on every page; none existed anywhere in the app before this).
- `User::slipAnalyses()` Eloquent relationship.
- `tests/Feature/DashboardTest.php` (8 tests): empty states, Continue-previous-slip visibility, populated analyses rendering, Progress gating, Unavailable-analysis handling, cross-user isolation, query-count ceiling.

### Changed
- `routes/web.php`: `dashboard` is now a Volt full-page route (was a static `Route::view`).
- `tests/Feature/WorkspaceAccessTest.php`: updated its dashboard assertion to match the new copy and the new No-Slips-vs-No-Analyses distinction (the old assertion checked for "No analyses yet." even for a user with zero slips, which the new, more correct empty-state logic no longer says).

### Gaps Identified and Resolved by Deliberate Scope Reduction, Not Silently Guessed
- **Colour values** (above) — the single largest gap; escalated to the founder before proceeding rather than inventing values unilaterally.
- **Progress metrics** — omitted "Average Risk Band" (would require inventing a categorical→numeric averaging method) and "Discipline Trend" (an undefined product concept); kept only literal, already-computable counts.
- **Analysis Card interactivity** — cards are non-interactive; `COMPONENT_PRINCIPLES.md` says they should link to "the full report," which doesn't exist yet (E-05).
- **Trust copy** — omitted "No AI Guesswork" (not in `TRUST_SIGNALS.md`'s Required Language; no AI feature exists to reference).
- **Hero vs. Primary Action Card** — merged into one section; having both as separately-styled primary CTAs would have violated `COMPONENT_PRINCIPLES.md`'s "exactly one primary button per screen" rule.

### Fixed (found during verification, unrelated to this sprint's own changes)
- A stale, gitignored `public/hot` file (leftover from a previous `npm run dev` session) was causing **every page in the application** to load with no CSS or JS applied at all, in any environment. Deleted locally; not a git-visible change since the file was never tracked, but recorded here so the cause isn't a mystery if it recurs.

### Not Done (by design — scope)
No change to the Risk Engine, Rule Set, persistence layer, ADR-007, or any governance document. No new screens beyond the dashboard itself (the skip-link and colour-token additions are shared-layer prerequisites, not new screens).

### Product Office Review (2026-07-25)
Approved without changes requested. The proposed design-token values were formally accepted as canonical — not provisional — and `docs/05-ux/DESIGN_TOKENS.md`'s provenance note updated accordingly; future UI work must reuse them rather than introducing new values. Product Office also explicitly reconfirmed, for the record, that the following remain reserved for a future, separately-approved milestone and must not be inferred or implemented ahead of that approval: Discipline Trend, Customer Betting Behaviour Analytics, Historical Improvement Metrics, Average Risk Band, Customer Scorecards, Recommendations, Coaching Features (`docs/00-governance/DECISION_LOG.md`). Next authorized sprint: U-02.5, Deterministic Analysis Report Experience — presentation/explainability only, no mathematical or architectural changes authorized.

## Governance Programme G-02 — Office Operating System

**Status:** Delivered. Documentation only — no PHP, Blade, Livewire, Tailwind, migrations, database, tests, Risk Engine, Rule Set, persistence, or UX implementation was changed.

### Added
- `docs/00-governance/office-operating-system/` — the permanent cross-office governance framework: `OFFICE_OPERATING_SYSTEM.md` (master philosophy: office lifecycle, how work enters/leaves an office, ownership and authority-inheritance principles, definitions of autonomy/escalation/completion), `AUTHORITY_MODEL.md` (the five ownership types, decision precedence tied to `CLAUDE.md`'s existing Source-of-Truth Order, conflict resolution, and a full RACI Responsibility Matrix across all six offices and thirteen decision types), `DECISION_ESCALATION_MODEL.md` (generalises Engineering's pre-existing stop/document/escalate discipline to every office, with worked examples per office), `HANDOVER_STANDARD.md` (the single ten-section format every future office-to-office handover follows), `OFFICE_TEMPLATE.md` / `PLAYBOOK_TEMPLATE.md` / `WORKFLOW_TEMPLATE.md` (the structure every office constitution, working method, and per-assignment lifecycle inherits), and `README.md` (reading order plus a full provenance table showing every mechanism already existed in this repository's history — RF-003A, ADR-007, the E-06C Validation sprint, U-02 — before G-02 formalised it).
- `docs/offices/` — standing constitutions for all six offices: `PRODUCT_OFFICE.md`, `ARCHITECTURE_OFFICE.md`, `ENGINEERING_OFFICE.md`, `UX_STUDIO.md`, `DATA_SCIENCE_LAB.md`, `COMPLIANCE_OFFICE.md`. Each fills every section `OFFICE_TEMPLATE.md` requires, grounded in this repository's actual history rather than generic governance boilerplate (e.g. Architecture Office cites ADR-001–007 and the AV-1 finding; Data Science Lab cites the RF-003A correction and Rule Set 2026.1's actual current "Proposed" status; UX Studio states its strategic/execution authority split plainly, citing the U-02 design-token escalation as its worked example).
- **Compliance Office formally established** as a standing office for the first time — `docs/09-compliance/PRODUCT_GUARDRAILS.md` previously existed with no explicit owner field; it is now jointly owned with Product Office, a formalisation of existing content, not a new requirement.

### Changed
- `CLAUDE.md`'s Source-of-Truth Order now points to `docs/00-governance/office-operating-system/` and `docs/offices/` under "Governance."
- `PROJECT.md` gained an "Office Operating System" pointer section alongside the existing Engineering Constitution and UX Foundation Documents pointers.
- `docs/00-governance/ENGINEERING_CONSTITUTION.md`: added a scope note clarifying its §1 (Authority Matrix) and §2 (Behaviour Change Policy) are retained unchanged as Engineering's own specific instance of the new general `AUTHORITY_MODEL.md` and `DECISION_ESCALATION_MODEL.md` — nothing in that document was reopened or altered.

### Validation
Every cross-reference across the 14 new documents was checked against the actual filesystem; one broken self-referential phrasing was found (a Working-Principles sentence in `ARCHITECTURE_OFFICE.md` that accidentally cited a non-existent path while making a comparison) and corrected. No duplicated governance (each office cross-references the shared framework rather than restating it), no orphan documents (both new directories are linked from `CLAUDE.md` and `PROJECT.md`), no existing Locked decision reopened.

### Not Done (by design — scope)
No application code, Risk Engine, UI, persistence, database, migration, or test was modified. No new governance was invented beyond what's grounded in this repository's own prior decisions and behaviour — see each document's citations.

## U-03.1 — Approved Source Review and Analysis Output Inventory

**Status:** Delivered, returned to Product Office. UX Studio, inventory only — no design, no wireframes, no frontend components, no implementation code, no Engineering instruction, no Risk Engine reinterpretation.

### Added
- `docs/05-ux/U-03/U-03.1-SOURCE-REVIEW-AND-OUTPUT-INVENTORY.md` — the source-of-truth evidence register for the U-03 Analysis Experience milestone. Full Risk Engine output contract (every factor, DTO, enum, and threshold cross-checked between `docs/03-data-science/RISK_RULE_SET_2026_1.md` and the actual `app/Domain/Risk/` implementation — zero conflicts found), the betting-slip and analysis-availability lifecycles (two distinct, non-interchangeable status concepts), a Risk Band Register and Risk Factor Register, dedicated contracts for Weakest-Selection, Confidence, Data-Quality, Explainability, Recommendation, and Rule-Set-Information, existing customer entry points and reusable components, a Customer Vocabulary Register, a State Coverage Matrix, a Conflict Register (3 minor conflicts), an Open Questions/Escalation Register (8 questions, each routed to a specific owning office), and a Constraints Register.

### Key Findings
- **Weakest-selection ranking has zero implementation anywhere** — an approved `docs/00-governance/PRODUCT_GLOSSARY.md` concept with no corresponding code (confirmed by repository-wide grep and `RISK_RULE_SET_2026_1.md` §G's own explicit scope confirmation). `docs/05-ux/EXPLAINABILITY_SYSTEM.md` already pre-approves "top contributing factor" as the interim substitute, so this does not block U-03.2.
- **Recommendations have zero implementation and are explicitly reserved** pending separate Product Office approval (`docs/00-governance/DECISION_LOG.md`, 2026-07-25) — confirmed by grep, not just by absence of a document reference.
- **No customer-facing route exists that displays a completed analysis** — `history` and `journal` are both `coming-soon` placeholders; there is no report/analysis-detail route in `routes/web.php` at all.
- Rule Set 2026.1 remains status "Proposed," not "Accepted" — recorded accurately in the register rather than assumed resolved.
- Three minor conflicts recorded (not resolved, per scope): "Leg" vs. "selection" terminology inconsistency, two coexisting button-component implementations (pre-token Blade components vs. U-02's inline token-based markup), and "Accumulator Tax" being an approved glossary term with no corresponding computed field.

### Readiness Classification
**READY WITH NON-BLOCKING QUESTIONS.** U-03.2 (Customer Analysis Journey and Experience Storyboard) may proceed for the fully-specified Full/Limited/Unavailable outcome structure. Weakest-selection and recommendation presentation are explicitly excluded from U-03.2's scope until separately approved.

### Not Done (by design — scope)
No wireframes, no frontend components, no implementation code, no test changes, no Risk Engine changes, no Engineering instruction issued. U-03 is not marked complete — only U-03.1. This document is returned to Product Office; U-03.2 does not begin automatically.

## U-03.2 — Customer Analysis Journey and Experience Storyboard

**Status:** Delivered, returned to Product Office for review. UX Studio, specification only — no implementation code, no application changes.

### Added
- `docs/05-ux/U-03/U-03.2-CUSTOMER-ANALYSIS-JOURNEY-AND-STORYBOARD.md` — the full customer-facing specification for the Analysis Experience, built directly on `U-03.1`'s evidence and eight binding Product Office decisions (PD-01–PD-08). Covers the complete journey from submission transition through Full/Limited/Unavailable report states to exit, with a 13-component inventory, complete proposed customer copy (Risk Band explanations, Data Quality explanations, Limited/Unavailable copy, trust and methodology statements, Accumulator Tax framing — all checked against a binding Copy Guardrails list), full responsive and accessibility specifications, a governance traceability matrix, and a 7-item Open Issues Register.
- A mid-review Product Office addendum ("APPROVED UX INSPIRATION") requested a dedicated Mobile Navigation and Drawer Experience section, incorporated as new §42–§46: mobile header, full-height right-side navigation drawer, a 3-group navigation hierarchy using SlipGuard's own existing labels (not the addendum's generic placeholders), active-state and motion treatment, a 15-component inventory, a 5-state matrix, and explicit tablet/desktop fallback to the existing horizontal nav. The addendum's own boundary was enforced throughout: interaction quality was adapted, the source's brand, colours, labels, and content were not.

### Key Product Office Decisions Applied
Rule Set 2026.1 governs despite its Proposed status (PD-01); Limited Analysis is a distinct, non-alarming, fully-authorized state (PD-02); "Main Contributing Factor" is the sole approved term for the highest-contributing factor, never "weakest selection" (PD-03); Data Quality shows band only, never the numeric score (PD-04); factors are ordered by persisted adjusted contribution with no recalculation (PD-05); Accumulator Tax is explanatory language only (PD-06); the submission transition is truthful, brief, and indeterminate, with an explicit list of prohibited fake-progress copy (PD-07); "Selection" is the customer-facing term, "Leg" stays internal-only (PD-08).

### Findings Worth Flagging
- **A real lifecycle inconsistency**, not previously surfaced in `U-03.1`: a Limited Analysis (like a Full one) also transitions its slip to `Analysed`, meaning "Edit this slip" — proposed as a Limited-Analysis recovery action by the directive itself — is not actually reachable under today's `BettingSlipStatus::allowedTransitions()` rules. Only an Unavailable outcome (which keeps the slip `Ready`) genuinely supports editing. Logged as Open Issue OI-03, escalated rather than silently resolved either way.
- The mobile navigation drawer is a genuinely new interaction pattern relative to what's implemented today (a below-header collapsing panel, not a full-height overlay) — flagged (OI-06) for a follow-up pass formally extending `docs/05-ux/COMPONENT_PRINCIPLES.md`/`MOTION_SYSTEM.md`, per the Gap Rule, rather than treating this milestone document alone as sufficient permanent governance.
- Data Quality's "Insufficient" band was deliberately left without customer copy — per `U-03.1`'s own findings, that band always resolves to an Unavailable outcome (which shows no Data Quality section at all) under Rule Set 2026.1's actual arithmetic, so drafting copy for it would misrepresent the system.

### Not Done (by design — scope)
No wireframes as code, no frontend components, no implementation, no test or route/migration/model changes, no Risk Engine changes, no Engineering instructions issued. U-03 remains not marked complete — only U-03.2. Returned to Product Office; the next milestone does not begin automatically.

## TOOL-UX-001 — Tooling Verification and Invocation Policy

**Status:** Delivered. Documentation, repository hygiene, and tool verification only — no application code, tests, routes, migrations, models, or UX implementation changed.

### Added
- Tool Invocation Policy in `CLAUDE.md`: UI UX Pro Max and 21st.dev MCP approved for design reference/inspiration only, never as a source of committed code or assets; any resulting UX idea still requires `docs/05-ux/` documentation and Product Office/UX Studio approval before implementation, per the existing Frontend Work Rule and Gap Rule.
- "Approved Tooling Policy" section in `PROJECT.md`, cross-referencing the above.
- `API_KEY_21ST` documented (unset, optional) in `.env.example`, with a pointer to `.mcp.json` and the new policy.

### Changed
- `.gitignore`: added `/.claude/settings.local.json` as defense-in-depth — it was previously protected only by a contributor-machine-local global gitignore entry, not by anything tracked in the repository itself.

### Verified (smoke test)
- `.mcp.json` holds no literal secret — only an `${API_KEY_21ST}` env-var reference — and is safe to keep committed.
- `mcp__21st__search` is live and returns real catalog data; every result surfaced a `npx shadcn@latest add ...` React/shadcn install command, confirming 21st.dev is a code-installation tool rather than a passive mood board.
- UI UX Pro Max's guideline database (`search.py`) is live, runs fully offline, and wrote no files; a deliberately obscure query ("risk indicator") correctly returned zero results instead of a fabricated one.

### Recommendation
Because 21st.dev's own output format is an installable package reference, the reference-only constraint in `CLAUDE.md` is load-bearing, not a formality — any future contributor tempted to run a returned `npx shadcn@latest add ...` command directly would introduce a second frontend framework and silently break the Blade+Livewire Locked Decision. No enforcement beyond documentation exists today (e.g. no CI check blocking a `shadcn.config.json` or `components.json` from appearing); acceptable for now given the small team, worth a lint/CI guard if the team grows.

### Not Done (by design — scope)
Figma MCP connectivity noted but not exercised beyond being available. Indeed MCP intentionally excluded from this policy — unrelated to SlipGuard's UI/UX work. No PHP, Blade, Livewire, Tailwind, migration, database, test, Risk Engine, Rule Set, or UX implementation was touched.

## U-03.3 — Analysis Experience Implementation (delivered, all 10 stages)

**Status:** Delivered. Founder-authorized implementation sprint — all 10 stages complete and tested.

### Added
- `docs/00-governance/DECISION_LOG.md` PD-09 and PD-10: PD-09 resolves `U-03.2`'s Open Issue OI-03 (Limited Analysis drops "Edit this slip"); PD-10 corrects a factual error in `U-03.1` §9 discovered during Stage 1 (`AnalyzeBettingSlip::execute()` calls `markAnalysed()` unconditionally for all three availability outcomes — Unavailable does not keep the slip `Ready`, contrary to what `U-03.1` had asserted). Both decisions apply the same resolution: no "Edit this slip" action on any completed analysis, only "Analyse another slip" / "Return to dashboard." No lifecycle or architecture code changed — only customer-facing copy and the governance record, matching `AnalyzeBettingSlip`'s actual, unchanged behaviour.
- Route `analyze/{bettingSlip}/report` and `resources/views/livewire/betting-slips/report.blade.php` — the first customer-facing screen displaying a persisted `SlipAnalysis`, implementing `U-03.2` §20's real 10-section report hierarchy (Analysis state, Overall Structural Risk, Main Contributing Factor, Supporting Contributing Factors, Data Quality, Limitation information [Limited only], Trust statement, Methodology, Report details, Exit actions) across all three outcomes (Full/Limited/Unavailable), reading only persisted data — never invokes the Risk Engine (ADR-007).
- An "Analyze" action and `wire:loading`-scoped transition panel on the slip index (`resources/views/livewire/betting-slips/index.blade.php`), using PD-07's approved transition copy verbatim — no fabricated progress stages or percentages, since the transition is simply the real (synchronous) request in flight.
- `tests/Feature/Analysis/SlipAnalysisReportTest.php` (8 tests): Full/Limited/Unavailable rendering with correct headline copy and score/band where applicable, no "Edit this slip" text on any variant, cross-user 403, unanalysed-slip 404, guest redirect to login, the Analyze action's redirect to the new report, and a determinism check (repeated report views never mutate the persisted analysis or create a duplicate row).

### Changed
- The slip index's existing "View" link for `Analysed` slips previously pointed at a read-only builder view (a stand-in, since no report existed) — now correctly resolves to the new report route.
- `docs/05-ux/U-03/U-03.1-SOURCE-REVIEW-AND-OUTPUT-INVENTORY.md` §9 Lifecycle Inventory and its Retry-support matrix corrected per PD-10.
- `docs/05-ux/U-03/U-03.2-CUSTOMER-ANALYSIS-JOURNEY-AND-STORYBOARD.md`: Frame L-07, Frame U-03, §30, the OI-03 register entry, and the governance traceability matrix all updated to reflect PD-09/PD-10 and correct the superseded "Edit this slip" language.
- `docs/05-ux/EMPTY_STATES.md`'s "Unavailable Analysis" and "Unsupported Sport" entries: primary CTA corrected from "Edit this slip" to "Analyse another slip" per PD-10.

### Verified
- Pest: 325/325 passing (317 prior + 8 new). `pint --test` clean (one new test file auto-fixed for import ordering).
- Live server check (`php artisan serve`): the new route resolves cleanly and correctly redirects unauthenticated access to login. Full interactive/visual browser verification was not completed — this environment's Playwright install fails ("does not support chromium on mac12"); recorded honestly rather than skipped over.

### Added (Stages 6–10)
- `resources/views/livewire/layout/navigation.blade.php`: replaced the below-header collapsing mobile panel with the full-height right-side navigation drawer specified in `U-03.2` §42–§46 — Groups 1–3 (Primary Workspace, Account, Supporting Information) using SlipGuard's own existing labels only, active-state via weight + background + leading accent bar + `aria-current="page"` (never colour alone), a keyboard focus trap, `Escape`-to-close, body scroll lock, safe-area padding, and focus restored to the menu trigger on close. Desktop's existing horizontal nav bar is untouched (§45). 3 new Pest tests (`tests/Feature/MobileNavigationDrawerTest.php`).
- A query-count ceiling test for the report screen (`tests/Feature/Analysis/SlipAnalysisReportTest.php`), mirroring the Dashboard's existing convention (<15 queries regardless of factor/leg count).

### Fixed (Stage 7 — accessibility review)
- `closeDrawer()` was forcing focus back onto the mobile-menu trigger even when the drawer was closed by selecting a navigation destination (about to navigate away regardless). Split into `closeDrawer()` (Escape/close-button/overlay — restores focus to the trigger) and `selectDestination()` (navigation clicks — lets standard browser/Livewire focus handling take over), per `U-03.2` §43's Drawer State Matrix.
- A broken duplicate `class`/`:class`/`x-bind:class` attribute on the Methodology section's chevron icon in `report.blade.php` (leftover from authoring; inconsistent with the correct pattern two lines below it) — corrected to a single `class` with `x-bind:class` layered on top, as Alpine expects.
- `report.blade.php`'s Report Details panel was `grid grid-cols-1 sm:grid-cols-2` — two columns from `sm` upward. `COMPONENT_PRINCIPLES.md`'s Reports rule is single-column at every breakpoint, no exceptions; changed to always single-column.

### Verified (Stages 8–10)
- **Deterministic integrity:** `grep`-confirmed `report.blade.php` never references `CalculateStructuralRisk`, `NormalizeBettingSlip`, or `AnalyzeBettingSlip` — it reads persisted `$bettingSlip->analysis` only. `AnalyzeBettingSlip::execute()` is invoked from exactly one customer surface app-wide (the slip index's `analyzeSlip()` action). ADR-007's boundary intact.
- **Regression:** full suite 329/329 passing (317 baseline + 9 report tests + 3 drawer tests). `pint --test` clean.
- **Responsive:** class-level audit only (no hardcoded pixel widths in any new file). Stated honestly: not a rendered-pixel visual check — this environment's Playwright cannot install its bundled Chromium ("does not support chromium on mac12").

### Not Done (by design — scope)
No Risk Engine, Rule Set, or ADR-007 boundary change at any stage — presentation-only, reading persisted data exclusively. Full interactive/visual browser verification was not completed, for the environment reason stated above, not skipped over silently.

## U-06.1 — Architecture Discovery, Capability Definition & Readiness Assessment

**Status:** Delivered, pending Product Office review — discovery only, no code.

### Added
- `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` — capability map, a proposed Planner bounded context and domain model, proposed planner lifecycle, service catalogue, integration overview, architectural risks register, dependency assessment, sequencing recommendation, and readiness assessment for Programme U-06 (Intelligent Accumulator Planning).
- `docs/adr/ADR-008-PLANNER-ENGINE-BOUNDARY.md` (status Proposed) — extends `ADR-007`'s pure-calculator discipline to a proposed planner evaluation layer. Indexed in `docs/adr/ADR-INDEX.md`.
- `docs/00-governance/DECISION_LOG.md` entry recording a declined directive (`PO-U06-RR-001`) that requested U-06.1–U-06.3 be marked complete in repository documentation ahead of any of that work existing.

### Findings
- Surfaced a previously unstated central scope fork for U-06: bounded planning over customer-supplied candidates (buildable now, no new dependency) vs. generative planning from a live market/fixture/odds universe (blocked — no such data source exists anywhere in the repository, and acquiring one is unauthorized). Recommended the former as U-06's first deliverable.
- Connected `ADR-007`'s already-documented re-analysis/persistence gap to U-06's "Regenerate" requirement — a genuine blocking dependency not previously identified as relevant to U-06.
- Flagged that "Parser Office," referenced in two directives received during this work, is not among the six offices established under `docs/offices/`.

### Not Done (by design — scope)
No PHP, Blade, Livewire, migration, database, test, Risk Engine, Rule Set, or UX implementation touched — architecture discovery and documentation only. `docs/adr/ADR-008-PLANNER-ENGINE-BOUNDARY.md` is Proposed, not self-accepted; U-06.2/U-06.3/U-06.4 not started.

## U-06.2A — Deterministic Weakest-Leg Mathematical Model

**Status:** Delivered, returned to Product Office for review — mathematics design only, no code.

### Added
- `docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md` — the **Marginal Structural Contribution (MSC)** model: `MSC_i = score(S) − score(S₋ᵢ)`, a leave-one-out attribution that re-invokes the unmodified, already-approved `CalculateStructuralRisk` engine once per candidate leg removal. Adds no new factor, weight, threshold, cap, or reason code to Rule Set 2026.1. Includes a full ranking methodology, a five-step tie-break specification, three worked numerical examples (an ordinary mixed four-leg case, an exact three-way symmetric tie, and a closed-form proof that `MSC` can be genuinely negative), four validation test vectors, mathematical invariants, known limitations, and Engineering implementation notes.
- `docs/00-governance/DECISION_LOG.md` entry recording the delivery, status `Proposed, returned to Product Office for review`.

### Findings
- Two candidate alternatives (an additive per-factor decomposition; a bespoke new per-leg weighted formula) were considered and rejected with reasoning — both would have required inventing new, unapproved weights, and neither can principledly attribute RF-001 (leg count) or RF-002 (combined odds), which are irreducibly whole-slip properties.
- Identified a genuine, previously-undocumented edge case: removing a recognized-market leg from a slip already `Limited` under Rule Set 2026.1's Tier 2 gate (unrecognized-market proportion) can push the remainder over the 25% threshold into `Unavailable`, for which no numeric `MSC` exists — proven reachable only via Tier 2, never Tier 1 or Tier 3, by direct analysis of the existing, unmodified gate rules. Recommended such legs be excluded from the numeric ranking and reported separately; proposed (not adopted) one new reason code, `LEG_REMOVAL_BLOCKS_ANALYSIS`, for Product Office/Data Science Lab consideration.
- Proved, via a rigorous closed-form construction (not a search-found coincidence), that a low-odds, simple-market "filler" leg inside a slip where both group caps are saturated both before and after its removal can show a zero or slightly negative `MSC` — the model must report this honestly rather than clamp it to zero.

### Not Done (by design — scope)
No application code, migration, persistence, UI, or Rule Set 2026.1 change — mathematics specification only, per the Product Office handover's explicit scope (`PO-U06.2A-001`) and Data Science Lab's own constitutional prohibition on writing application code. Not self-accepted; Engineering not commissioned.

## Repository Entry-Point Discoverability Fix

**Status:** Delivered — minimal, existing-file correction only, no new documents.

### Changed
- Declined, as literally scoped, a directive (`PO-SGOS-001`) proposing a new parallel "SGOS v1.0" governance structure — SGOS v1.0 already exists (`docs/00-governance/SGOS_VERSION.md`, declared 2026-07-23) and every document it proposed already has a real, more specific equivalent in the repository. See `docs/00-governance/DECISION_LOG.md` for the full reasoning.
- `docs/README.md`: corrected the description of what forms SGOS to include `docs/offices/` and `docs/engineering/` (both added by later sprints and never folded into this sentence); added a short pointer to `TASKS.md`/`DECISION_LOG.md`/`CHANGELOG.md` as where current state actually lives.
- `README.md` (repository root): corrected the stale pre-SD-001 product identity paragraph to match `CLAUDE.md`'s current Product Identity; replaced the stale, E-02/ADR-005-era "Current Milestone" and "Where to Begin" sections with evergreen pointers to `TASKS.md`, `docs/00-governance/REPOSITORY_STATE.md`, and `docs/00-governance/DECISION_LOG.md`, so these sections cannot silently go stale the same way again.

### Not Done (by design — scope)
No new governance document created, no restructuring of `docs/00-governance/`, no new constitutional office or entity introduced — corrections to two already-existing entry-point files only.

## U-06.2A Acceptance

**Status:** Accepted (`PO-U06.2A-AC-001`, 2026-07-26) — governance-state update only, no mathematics changed.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded Product Office's formal acceptance of the Marginal Structural Contribution (MSC) weakest-leg attribution model, as submitted, with no revision to the mathematics.
- `docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md`: status line and Final Report §13.D updated from "Proposed, returned to Product Office for review" to "Accepted" — content of the specification itself untouched.
- `TASKS.md`: U-06.2A task list and the Programme U-06 summary line updated to reflect acceptance.

### Not Done (by design — scope)
Engineering implementation not commissioned by this acceptance — that remains a separate handover, per Data Science Lab's own Handover Rules and `ADR-008`'s still-Proposed planner/engine boundary. No application code, migration, or UI change.

## E-06D.1 — Deterministic Weakest-Leg Ranking Engine

**Status:** Delivered — engine layer only, founder-authorized directly following `PO-U06.2A-AC-001`.

### Added
- `App\Domain\Risk\Engine\RankLegsByStructuralWeakness` — a pure calculator implementing the accepted Marginal Structural Contribution (MSC) model: re-invokes the unmodified `CalculateStructuralRisk` engine once per candidate leg removed and computes `MSC_i = score(S) − score(S₋ᵢ)`. No change to any Rule Set 2026.1 factor, weight, threshold, cap, or reason code.
- `App\Domain\Risk\Results\LegAttribution` and `LegAttributionRanking` — immutable result value objects: per-leg rank, integer and unrounded `MSC`, score/band without the leg, availability without the leg, and a reason-code delta versus the baseline.
- `RiskAnalysisResult::$rescaledScorePrecise` (nullable `BigDecimal`) — the one small, additive change the specification's implementation notes recommended, exposing a value `CalculateStructuralRisk` already computed and previously discarded, needed for the MSC tie-break's second step.
- `tests/Unit/Risk/Engine/RankLegsByStructuralWeaknessTest.php` (8 tests): reproduces the specification's WLA-EX-01 (including its L1/L2 tie-break), WLA-EX-02 (exact symmetric tie), WLA-EX-03 (the closed-form negative-`MSC` proof), and WLA-TV-04 (the gate-dependent edge case) exactly, plus non-applicability, determinism, and reason-code-delta coverage.

### Fixed
- Found and corrected one post-acceptance discrepancy in `docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md`, traced to that document's own Python reference-verification script (not its formula, and not this implementation): the script never rounded each factor's contribution to 4 decimal places before summing, as Rule Set 2026.1 §3 requires, producing a genuine last-digit discrepancy in one figure (§7.1's L4 `mscPrecise`, `37.4727` → `37.4726`). No other figure, formula, tie-break rule, or ranking conclusion was affected — see `docs/00-governance/DECISION_LOG.md` for the full record.

### Verified
- Pest: 337/337 passing (329 baseline + 8 new). `pint --test` clean.
- The gate-dependent-leg edge case (specification §4) confirmed empirically reachable by direct execution, not just theoretically derived.

### Not Done (by design — scope)
No Planner UI, persistence, migration, regeneration workflow, parser change, or new mathematics — engine layer only, per the commissioning handover's explicit scope. Presentation, persistence, and API exposure remain separate, not-yet-started future work.

## U-07.1 — Planner Decision Flow & Orchestration Architecture

**Status:** Delivered, pending Product Office review — discovery/design only, no code.

### Added
- `docs/02-architecture/U-07.1-PLANNER-ORCHESTRATION-ARCHITECTURE.md` — resolves `ADR-008`'s open regeneration dependency for Capability A (planner regeneration never touches `SlipAnalysis`/`Analysed`; export uses only already-legal `BettingSlipStatus` transitions), a refined `PlannerSessionStatus` lifecycle, a full orchestration/decision-pipeline sequence, a risks register, and a 7-item Open Decisions Register (OD-1–OD-7) for Product Office, Compliance Office, and UX Studio.
- `docs/adr/ADR-009-PLANNER-LIFECYCLE-AND-REGENERATION-BOUNDARY.md` (status Proposed) — extends `ADR-008`'s pure-calculator discipline with the concrete lifecycle and call-flow decision it had deferred. Indexed in `docs/adr/ADR-INDEX.md`.

### Findings
- Several of the commissioning handover's "Key Questions" (system-chosen replacement strategy, iterating "until acceptable") presuppose Capability B's autonomous candidate generation, which is not authorized and has no data source — reframed for Capability A, where the customer sources every replacement and the planner only evaluates, ranks, and explains.
- The regeneration/persistence gap `ADR-007` flagged and `ADR-008` explicitly declined to resolve turns out not to block Capability A at all: planning happens entirely in new tables with no unique-per-slip constraint, and export only ever uses the already-legal `Ready → Draft → Ready` cycle, never touching `Analysed`.
- Declined to invent stopping-condition or abandonment-recommendation policy — registered as Open Decisions for Product Office/Compliance Office instead, consistent with SD-001's prohibition on unexplained suggestions.

### Not Done (by design — scope)
No PHP, Blade, Livewire, migration, database, test, Risk Engine, Rule Set, MSC mathematics, or UX implementation touched — architecture discovery and documentation only.

## U-07.2 — Planner Product Decision Register, and PD-007

**Status:** Delivered (analysis only); DR-03 subsequently resolved by `PD-007`.

### Added
- `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md` — consolidated 7 scattered open items from `U-06.1`/`U-07.1` into 5 decisions (DR-01–DR-05), each with options, advantages/trade-offs, product implications, and an optional non-binding recommendation, plus a priority/dependency map. No decision made, no governance touched, at delivery.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded **`PD-007`**, resolving DR-03 — the Planner is seeded only from an existing, customer-supplied `BettingSlip`; the Planner never generates, discovers, or recommends selections itself. The entry-mechanism interpretation is recorded explicitly, with its textual basis stated, since `PD-007`'s own wording most directly restates the already-settled Capability A/B boundary rather than the narrower entry-UI question DR-03 asked.
- `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md` updated to mark DR-03 resolved and DR-05 now directly active (previously conditional on DR-03's outcome); priority/dependency map updated to reflect 4 remaining open decisions (DR-01, DR-02, DR-04, DR-05).

### Not Done (by design — scope)
No code, UX, or architecture change — governance-record and decision-register updates only, per `PD-007`'s own explicit instruction.

## Compliance Assessment of DR-01, and PD-008

**Status:** Delivered; DR-05 resolved by `PD-008`.

### Added
- `docs/09-compliance/DR01-PLANNER-MESSAGING-COMPLIANCE-ASSESSMENT.md` — Compliance Office's assessment of DR-01's four messaging options against `PRODUCT_GUARDRAILS.md`, `VISION_AND_PRINCIPLES.md`, and SD-001's own already-recorded regulatory concerns: per-option risk ratings, three concrete tests for "decision-intelligence, not tipster" language, prohibited/required messaging patterns (categories, not literal copy), identified regulatory-risk categories for external counsel, and a non-binding recommendation. No decision made.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded **`PD-008`**, resolving DR-05 — the original slip is read-only for the duration of an active Planner session; the "working copy" concept confirmed as already designed (`U-07.1`'s `PlannerSelection`), not new architecture. One implementation-convenience question (manual revision re-creation vs. a one-click restore action) flagged for a future Engineering handover, not resolved now.
- `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md` updated: DR-05 marked resolved; DR-04 annotated with the tension between `PD-008`'s revision-retention principle and a periodic-purge policy; priority/dependency map updated to 3 remaining open decisions (DR-01, DR-02, DR-04).

### Not Done (by design — scope)
No code, UX, or architecture change — assessment and governance-record updates only.

## SD-002 — SlipGuard Labs & Product Evolution Platform (governance only)

**Status:** Strategic decision recorded. No Engineering work commissioned; nothing implemented.

### Added
- `docs/00-governance/DECISION_LOG.md`: recorded **SD-002**, establishing SlipGuard Labs (Programme U-13) as an approved strategic capability — a customer-facing product-transparency area, MVP scope deliberately narrow (feature awareness, roadmap status, "Notify Me," "Join Beta" only). Resolves the governance gap a prior directive (`PO-U13.1-001`) was correctly declined over.
- `PROJECT.md`: cross-referenced SD-002/Programme U-13 alongside the existing MVP Non-Goals — confirmed the approved MVP scope does not reopen any of them (bookmaker voting, language voting, and Smart Bet Transfer/bookmaker-integration implementation are explicitly deferred by SD-002 itself, not merely assumed out of scope).
- `TASKS.md`: added Programme U-13 tracking; corrected a stale Programme U-07 reference to the now-superseded `OD-` numbering (consolidated into `U-07.2`'s `DR-01`–`DR-05`, with DR-03/DR-05 already resolved).
- `docs/08-operations/DELIVERY_ROADMAP.md`: added a U-13 roadmap entry, listing the explicitly-deferred items directly from SD-002 rather than restating them generically.

### Not Done (by design — scope)
No code, migration, UI, navigation, or admin-panel work — SD-002 establishes strategic authority only. A separate, explicitly-scoped Engineering handover is required before any implementation.

## U-13.1 — SlipGuard Labs MVP Implementation

**Status:** Delivered — exactly SD-002's approved narrow scope, founder-authorized directly (`PO-U13.2-001`).

### Added
- `labs_features` / `labs_feature_interests` migrations; `App\Models\LabsFeature`/`LabsFeatureInterest`; `App\Domain\Labs\LabsFeatureStatus`/`LabsInterestType` enums.
- Customer-facing Labs page (`resources/views/livewire/labs/index.blade.php`, route `labs`), added to the desktop nav and mobile drawer as the 5th destination. Shows published features only, each with a status badge, summary, optional "why this matters," and only the actions that feature enables. "Notify Me"/"Join Beta" are real, functional, per-user toggle actions — never placeholders, never trust a client-supplied user ID.
- `App\Filament\Resources\LabsFeatures\LabsFeatureResource` — the first Filament resource in this app: create/edit, status, notify/beta/publish toggles, drag-reorder, unique-slug validation. Gated by the existing `is_internal` convention.
- Small UX Constitution extension mirroring U-02's own design-token gap resolution: six Labs-status badge colour tokens (`docs/05-ux/DESIGN_TOKENS.md`, `resources/css/app.css`, none reusing a risk/quality hue), a "Labs Feature Cards" entry in `docs/05-ux/COMPONENT_PRINCIPLES.md`, and a "No Labs Features" empty state in `docs/05-ux/EMPTY_STATES.md` — all reusing existing Badge/Card component shapes, no new pattern invented.
- Pest coverage: `tests/Feature/LabsTest.php` (8 tests), `tests/Feature/LabsFeatureResourceTest.php` (2 tests); `labs` added to `WorkspaceAccessTest`'s existing route list.

### Findings
- No emoji anywhere in shipped copy, despite the commissioning handover's own draft using them (🚀📝🎨🛠🧪) — `docs/05-ux/EMPTY_STATES.md`'s Tone rule ("never emoji-driven") applies here exactly as everywhere else in the product; a heroicon replaces the "✓" glyph in the toggled-button state.
- Deliberately did not seed or publish any Labs feature content (Smart Bet Transfer, multi-language support, etc.) — the platform capability is built and functional, but deciding what to publicly announce, and its exact wording, is a separate editorial/compliance decision, not one this implementation makes unilaterally.

### Verified
- Pest: 349/349 passing (337 baseline + 12 new). `pint --test` clean.
- Live check via `php artisan serve`: guest redirect and health check confirmed. Full in-browser visual verification not performed — this environment's Playwright/Chromium limitation (already noted in this repository's history, U-03.3) applies here too.

### Not Done (by design — scope)
No bookmaker voting, language voting, Smart Bet Transfer implementation, or any bookmaker/API integration — all remain explicitly deferred per SD-002.

## Logo Size Fix and Adjustment

### Fixed
- Found and fixed a real, silent bug via live browser verification (headless Chrome, since Playwright/Chromium remains unsupported in this environment): `public/build/assets/app-*.css` was stale — last compiled 2026-07-25, before the SlipGuard logo/branding work and every change made in this session — and contained no `.h-8` rule at all. Every page's logo (`welcome.blade.php`, `layouts/guest.blade.php`, `layout/navigation.blade.php` ×2) was rendering at the source image's raw intrinsic size (1685×621px, scaled down only by the browser's own image handling) instead of the intended 32px-tall lockup. Ran `npm run build` to recompile current CSS (including this session's new Labs colour tokens, which were sitting unbuilt for the same reason). A different variant of the same class of bug already found once in this repository's history (U-02's stale `public/hot`) — the underlying lesson (compiled frontend assets can silently drift from source) is the same, the specific stale artifact is not.

### Changed
- Logo size increased from `h-8` (32px) to `h-10` (40px) across all four usages (`welcome.blade.php`, `layouts/guest.blade.php`, `layout/navigation.blade.php` desktop nav and mobile drawer header), per direct founder feedback after visual review.

### Verified
- Headless Chrome screenshots of the homepage and login page before and after both the stale-build fix and the size increase. Pest: 349/349 passing, `pint --test` clean (no test asserts on rendered pixel size, so neither change affected the suite).

## U-13.4 — SlipGuard Labs Experience Review & UX Certification

**Status:** Delivered — review only, no code, no design files.

### Added
- `docs/05-ux/U-13/U-13.4-EXPERIENCE-REVIEW-AND-UX-CERTIFICATION.md` — a full assessment against every criterion the commissioning handover named (discoverability, clarity, visual quality, trust, focus, editorial presentation, customer journey, mobile, accessibility), each backed by a genuine rendered-browser screenshot — the first real rendered-browser verification performed in this repository's history, working around this environment's Playwright/Chromium limitation by driving the system's installed Chrome directly and rendering authenticated pages through the same HTTP-test harness the Pest suite already uses.

### Found (critical, blocking, bigger than Labs)
- Every colour-coded badge in the product — the pre-existing Risk Report/Dashboard risk-band and data-quality badges, and the new Labs status badges alike — renders with zero colour (a plain black outline) in an actual browser. Root cause verified directly: Tailwind v4's build-time class scanner cannot resolve the dynamically-interpolated class names (`text-risk-{{ $token }}`, `bg-labs-{{ $token }}/10`, etc.) these components use; confirmed by grepping the compiled CSS directly — none of these classes exist in the shipped stylesheet. A standard, well-understood fix was identified (an explicit literal class listing) but not applied, per this review's own "no code" scope.
- One minor, non-blocking accessibility refinement: per-feature `aria-label`s recommended on the Notify Me/Join Beta buttons.

### Not Done (by design — scope)
No code, migration, or design file produced. All temporary review fixtures (4 `LabsFeature` rows, 1 test user, dumped HTML) created for this review were fully removed — confirmed via `git status` and a direct database check. Recommendation, not a decision: SlipGuard Labs not yet suitable for public release, for the one diagnosed reason above.

## Badge Colour Rendering Fix

**Status:** Delivered, founder-authorized directly following U-13.4's finding.

### Fixed
- `resources/css/app.css`: added explicit `@source inline(...)` directives — Tailwind v4's standard mechanism for classes a codebase constructs dynamically at runtime, which its build-time scanner otherwise can't discover. Covers every risk-band, data-quality-band, and Labs-status colour-token class this codebase builds via `{{ $token }}` interpolation (`text-risk-*`, `bg-risk-*/10`, `border-risk-*/30`, and the `quality-*`/`labs-*` equivalents — the latter safelisted pre-emptively since they're already-defined `DESIGN_TOKENS.md` values with no current dynamic usage yet). No Blade template changed — the fix is entirely in the CSS build configuration.

### Verified
- Rebuilt (`npm run build`); confirmed every previously-missing class now exists in the compiled CSS by direct grep.
- Re-rendered the Risk Report and Labs pages via the same rendered-browser method U-13.4 used: Risk Report's High band now shows its intended orange, Low band its intended green; all six Labs statuses now render in distinct, correct hues (previously all identical, colourless black outlines).
- Pest: 349/349 passing. `pint --test` clean.

## Programme U-13 — Closed, Accepted, Approved for Release

**Status:** `PO-U13-AC-001` — Product Office formal acceptance and constitutional closure.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded formal acceptance of the full U-13 lifecycle (`SD-002` → implementation → UX certification → remediation → acceptance) and constitutional closure of the programme. Scope compliance against SD-002's deferral list reconfirmed at acceptance time, not merely assumed carried over.
- `TASKS.md`: Programme U-13 marked closed; future SlipGuard Labs enhancement requires new Product Office commissioning, not further work under this programme.

### Not Done (by design)
No Labs feature content published — that remains a separate, later editorial decision through the admin panel. Organizational focus returns to Decision Intelligence, Structural Risk Analysis, the Planner, Explainability, and Customer Experience — Programme U-07's open decisions (DR-01, DR-02, DR-04) are the more directly relevant next work.

## U-07.3 — Planner Architectural Readiness Review

**Status:** Delivered — review only, no code, no redesign.

### Added
- `docs/02-architecture/U-07.3-PLANNER-ARCHITECTURAL-READINESS-REVIEW.md` — verified first that no Planner-specific code exists anywhere in this repository and that `ADR-008`/`ADR-009` remain `Proposed`, never formally `Accepted`. Found two genuine architectural gaps independent of `DR-01`/`DR-02`/`DR-04`: an export-mechanism/`PD-008` tension (Gap A) and an unspecified source-slip lock mechanism required by `PD-008`'s own DR-05 selection (Gap B). Answered all five questions the commissioning handover asked. Outcome: Planner architecture not yet constitutionally complete — four specific, non-speculative dependencies identified, none requiring redesign.

### Not Done (by design — scope)
No code, redesign, optimisation, new capability, or new programme — review only, per the handover's explicit constraints.

## Theme-Aware Header Icon Swap

**Status:** Delivered, verified live in a real browser session.

### Added
- `public/brand/slipguard-icon-accent-dark.svg` — founder-supplied white icon variant, for use on dark surfaces.
- `theme-toggle.blade.php` now dispatches a `slipguard-theme-changed` window event on click; the header listens for it and swaps its icon `src` live (no reload) between the light/purple and dark/white variants.
- A static `src` fallback kept on the icon `<img>` to avoid a flash-of-broken-image before Alpine hydrates.

### Fixed (verification, not the asset)
- An initial pixel check misread the supplied white icon as opaque black. Root cause: the PNG is palette-indexed, not truecolor, and needs `imagepalettetotruecolor()` before sampling. Corrected, then confirmed both via corrected pixel analysis and a visual render against a dark background.

### Decisions Made, Stated Plainly
- "Dark surfaces" implemented as a swap keyed to the global light/dark theme toggle — the only dark-surface signal already available site-wide — not a scroll-position-aware swap reacting to specific sections behind the glass header, which would need a materially more complex mechanism and was not built.

### Verified
- 1 new Pest test. Full regression: 497/497 passing (up from 496). `pint --test` clean; `npm run build` clean. Icon swap confirmed live via an actual button click in a real browser session.

## Icon/Wordmark Split + Second Motion Binding Bug Fixed

**Status:** Delivered, screenshot-verified in both themes.

### Added
- `public/brand/slipguard-icon-accent.svg` — a founder-supplied, icon-only (purple/violet) rendering of the existing brand mark, confirmed genuinely icon-only (no embedded wordmark) via a direct Playwright render before use.
- Public header: the flattened single-image lockup replaced with a rotating icon `<img>` plus a genuine static text wordmark (`<span>SlipGuard</span>`, Figtree) — no wordmark image asset needed. The wordmark never receives the rotation transform.

### Fixed
- **A second, independent Signature Motion defect**, found only by verifying actual rotation values rather than listener counts: the prior scroll-listener de-dup fix passed a bare method reference, losing its `this` binding — rotation had been completely inert this whole time. Fixed with a closure wrapper preserving `this`, while keeping the de-dup logic intact (re-verified: still 5 adds/4 removes/net 1 listener across 4 navigations). Rotation re-verified against real values: 0°/4.32°/9°/18°/18° at scrollY 0/100/200/400/600, returning cleanly to 0° at the top.

### Decisions Made, Stated Plainly
- **A pre-existing asset gap disclosed**: `public/brand/slipguard-icon-*-transparent.svg` (navy, matching the full lockup) had existed in the repo the entire session — never discovered because no full directory listing of `public/brand/` had ever been done. Both the existing navy icon and the newly supplied purple one were shown to the founder directly; the founder knowingly chose the new purple/violet icon, acknowledging the resulting mismatch against the still-navy footer/auth-pages/authenticated shell (left unchanged, out of scope for this public-header-only motion).

### Verified
- 1 test updated in `tests/Feature/GlobalShellTest.php`. Full regression: 496/496 passing. `pint --test` clean; `npm run build` clean. Verified visually via screenshot in both themes.

## Real Browser Verification + Founder Direct Styling Instructions

**Status:** Delivered and verified via real screenshots, not self-declared. Playwright installed at the founder's explicit instruction, enabling actual browser verification for the first time in this repository's history.

### Fixed — the actual root cause
- **Alpine.js was never loading on any public page.** Confirmed via a real headless-Chrome session (`window.Alpine` was `undefined`). Root cause: the public layout tree renders zero `<livewire:...>` components, so Livewire v3's auto-injection of its JS bundle (which ships Alpine) never fired. Every `x-data`/`x-show`/`:class` binding across every public page — the theme toggle, the logo motion, section reveals — was inert from the start. Fixed by adding `@livewireStyles`/`@livewireScripts` to all four layouts.
- **A real, confirmed listener leak in the header's scroll handler**: instrumented listener counting across 4 `wire:navigate` transitions confirmed accumulation (5 adds, 0 removes) without a fix, and that the previously-applied `$cleanup()` fix was itself broken (`$cleanup is not defined` — not available in this project's bundled Alpine). Replaced with a single global listener slot; verified empirically resolved (5 adds, 4 removes, net 1 active).

### Added — founder direct instructions, implemented and screenshot-verified
- No shadows anywhere (`--shadow-1/2/3: none`); contrasting borders added everywhere a component relied on shadow alone (Card, Modal, Dropdown, theme-toggle thumb, mobile drawer, hero preview).
- Glassmorphism header: `bg-surface-page/70` + `backdrop-blur-md` at all scroll positions — verified via screenshot showing genuine blur over scrolled content.
- Gradient button backgrounds: new `--gradient-button` token, theme-reactive automatically, applied to the primary button component and public header/hero CTAs.
- Bold accent-gradient CTA sections site-wide, both themes: new `--gradient-cta` token, deliberately constant across themes (dark mode's own accent is too light for white text — verified before choosing the values). Applied to all 5 public CTA sections with inverted white-fill buttons.

### Not Done (by founder's own direction)
- The larger `U-08.1` brand-identity overhaul (smoked graphite theme, "Rich Indigo" accent, atmospheric background shapes, light-sweep effect) — paused to finish this smaller correction cleanly first.
- Independent icon/wordmark motion — blocked on a dedicated icon-only asset the founder will supply; the current logo is one flattened image and cannot be split without cropping the approved artwork.

### Verified
- 4 new Pest tests (`tests/Feature/GlobalShellTest.php`). Full regression: 496/496 passing (up from 492). `pint --test` clean; `npm run build` plus a compiled-CSS check confirmed every new class actually generated.

## `PO-U16.2-CR-001` — Corrective Implementation (Product Office Rejection of U-16.2)

**Status:** Implementation complete, human visual QA pending — not self-declared visually accepted. Product Office rejected U-16.2 against the rendered browser, not automated tests. §2 of this directive explicitly authorised modifying homepage section backgrounds to expose the gradient system, resolving the tension `U-16.2` itself had flagged and left open.

### Fixed
- **Logo — exact 50px visual height, re-measured at full pixel resolution**: content-to-canvas ratio 0.7665; `50 ÷ 0.7665 ≈ 65px` CSS height, implemented as `h-[65px]` expanded / `h-[52px]` condensed. Scoped to the public header only, per the directive's own exception. Header padding retightened (`py-2.5`/`py-2`) to match.
- **Theme toggle rebuilt as a genuine pill/track/thumb switch** (`role="switch"`, `aria-checked`, both icons always present, sliding indicator, 200ms) — the prior single-button icon-swap was exactly the directive's own named anti-pattern.
- **A real, independent Signature Motion defect found while re-verifying the directive's 9-point checklist**: the scroll listener had no teardown and never called `updateFromScroll()` on mount — a plausible concrete cause of "no visible motion demonstrated." Fixed with an immediate call plus Alpine's `$cleanup`.
- **Gradient visibility implemented, using the authority granted in §2**: `DESIGN_TOKENS.md`'s `--gradient-page` strengthened; new `--gradient-cta` token added for the closing CTA. Every homepage section's background changed from fully opaque to translucent (`/80`), so the shell's gradient now shows through all of them; hard section-divider borders removed in favour of the gradient continuity itself.

### Specialist Design Capability Usage
UI UX Pro Max: one applicable item (sticky-nav overlap) checked and confirmed not applicable (`sticky` ≠ `fixed`). 21st.dev/Magic: all 8 results were glassmorphism/plasma/particle/GSAP-magnetic/aurora-glow — rejected outright per this directive's own prohibitions; nothing adopted.

### Verified
- 4 new Pest tests. Full regression: 492/492 passing (up from 488). `pint --test` clean; `npm run build` plus a compiled-CSS grep confirmed every new class actually generated.

### Not Done / Explicitly Deferred
Visual acceptance itself — no browser automation tool exists in this environment (re-confirmed via a fresh tool search before starting). Per the directive's own §13 fallback, this correction is returned with a QA checklist for the founder to verify against the already-running local servers, not claimed as visually complete.

## U-16.2 — Global Shell Final Polish & Premium Experience Audit

**Status:** Delivered by Engineering Office, scoped exclusively to header/footer/theme/shell/gradients/navigation/motion — no page redesign, no new feature. Commissioned by `PO-U16.2-001`. Awaiting Product Office review.

### Fixed
- **`resources/css/app.css`: added the missing `[x-cloak] { display: none !important; }` rule.** Real root cause found for the commission's strongest claim ("Theme Toggle... unacceptable... flashing"): Alpine.js's `x-cloak` requires this rule to actually hide an element pre-hydration — Alpine only removes the attribute, it never hides anything itself. No such rule existed anywhere in the stylesheet, verified directly against four files using `x-cloak` (the theme toggle's moon icon, the mobile nav drawer, Report's expandable panels, Planner's modals) — all four were briefly visible on every page load before Alpine finished booting.
- **Public header logo**: `h-11`/`h-9` → `h-10`/`h-8`. Measured, not guessed — the approved SVG's embedded PNG alpha channel was decoded (PHP GD) and found to carry ~23% transparent padding, and every other logo instance in the app (authenticated nav, guest auth pages, footer) was already `h-10`/`h-8`. `U-15.2`'s own enlargement had made the public header the one outlier. Header padding tightened (`py-6`/`py-4` → `py-5`/`py-3`) to match.

### Verified, Not Changed
- The theme persistence architecture itself (`localStorage` + `data-theme`, one shared partial since `U-16.0`) was re-audited with fresh scrutiny given the strength of the commission's claim, and confirmed sound — every auth transition (register/login/logout) already uses `wire:navigate`, so `<html data-theme>` is never reloaded mid-session. The `x-cloak` bug above is the verified, concrete defect, not this.
- The footer gradient-continuity fix (`U-16.1`) reconfirmed still correct.

### Decisions Made, Stated Plainly
- **The Global Gradient System's "still visually absent" complaint has a concrete, verified root cause, reported rather than silently patched**: every homepage section uses its own fully opaque background with no gap between sections, so the shell's `bg-gradient-page` (already correctly wired) is mathematically invisible on the homepage. Fixing this fully would mean changing the homepage's own section backgrounds — directly against this same commission's "do NOT redesign pages" prohibition and against `HOMEPAGE_STORYBOARD.md` v1.2's own documented alternating-background rhythm rule. This is a genuine tension between two of the commission's own instructions, named for Product Office to resolve, not picked unilaterally.

### Verified
- 2 new Pest tests (`tests/Feature/GlobalShellTest.php`, 8 total). Full regression: 490/490 passing (up from 488). `pint --test` clean; `npm run build` plus a compiled-CSS check confirmed the new `[x-cloak]` rule actually generated.

### Not Done (by design)
Browser verification — no browser automation tool exists in this environment; the commission's own acceptance standard is an emotional, comparative visual judgement this environment cannot render or assess.

## U-16.1 — Global Navigation, Header, Footer & Layout Standardisation

**Status:** Delivered by Engineering Office, scoped exclusively to the shared shell — no page-specific redesign performed. Commissioned by `PO-U16.1-001`. Every finding classified A (Broken)/B (Inconsistent)/C (Intentional). Awaiting Product Office review.

### Fixed (Classification A — Broken)
- Theme toggle: added `aria-pressed` (state was previously only exposed via a changing `aria-label`) and press/active feedback (`active:scale-95`), matching every other interactive control. Icon swap is now a 100ms opacity crossfade.
- Authenticated desktop navigation (`<x-nav-link>`): never set `aria-current="page"` on any of its six call sites, while the mobile drawer already did — fixed once at the component level; `font-semibold` added so weight also distinguishes the active item, not colour/border alone.
- Public header: primary nav links and Sign In/Dashboard links had no focus-visible ring at all — added the same `focus-visible:outline-accent` treatment used everywhere else.

### Fixed (Classification B — Inconsistent)
- Four raw `max-w-7xl` instances (authenticated nav, `layouts/app.blade.php`'s header/footer, `layouts/labs.blade.php`'s footer) — the same 1280px value as `container-marketing`, expressed as a second class — consolidated to one.
- The authenticated footer's markup, previously duplicated verbatim across two layout files, extracted to `resources/views/partials/authenticated-footer.blade.php`.
- Both footers previously overrode the parent layout's `bg-gradient-page` with an opaque surface colour — removed, so the approved gradient now visibly continues into the footer band.
- `responsive-nav-link.blade.php` — a Breeze-scaffold leftover verified completely unreferenced anywhere — deleted.

### Confirmed Intentional, Not Changed (Classification C)
- The public marketing footer and the authenticated footer remain deliberately different components — merging them would put marketing navigation inside every authenticated workspace screen, against the workspace's own restraint principle.
- The header stays opaque/solid (not gradient-bled) — a genuine legibility requirement, not an oversight.
- The primary nav's lack of a literal "Home" link — `U-14.1`'s own prior, already-Locked decision (the logo is the home link).
- The footer's link set already covers every item the commission's own Footer Navigation list names.

### Decisions Made, Stated Plainly
- **Extending gradients to Cards/CTA buttons/the header, as the commission's §12 also asked, was not implemented.** `VISUAL_INSPIRATION.md`'s own Locked amendment states the approved gradient is "never applied... decoratively per-card" — doing so would directly contradict it. Not implemented without an explicit Product Office amendment to that specific rule, the same precedent the gradient system's original approval required.

### Verified
- 6 new Pest tests (`tests/Feature/GlobalShellTest.php`). Full regression: 488/488 passing (up from 482). `pint --test` clean; `npm run build` plus a compiled-CSS grep confirmed every new utility class actually generated. Signature Motion (`U-14.3`) re-verified intact — only size/spacing classes touched, never the Alpine motion logic.

### Not Done (by design)
Browser verification — no browser automation tool exists in this environment; the commission's qualitative asks (logo size "determined visually," a user "should not notice the header/footer") cannot be verified without one.

## U-16.0 — Whole Product Experience Audit, Phase 1 (Theme Coverage) + Header/Footer/Component Consistency

**Status:** Delivered by Engineering Office — Phase 1 of a 14-phase commission; the remainder is explicitly not claimed complete. Commissioned by `PO-U16.0-001`. Awaiting Product Office review.

### Fixed
- `danger-button.blade.php`: raw Tailwind `red-600/500/700` → the existing `alert-error`/`alert-error-strong` tokens — the one button with no dark-mode value.
- `auth-session-status.blade.php`: raw `green-600` → `alert-success-strong` token.
- `modal.blade.php`: backdrop `bg-gray-500` → `bg-neutral-900/50` (now matches the mobile nav drawer's own backdrop exactly); panel `bg-neutral-50 shadow-xl` → `bg-surface-card shadow-elevation-3` (modals are the product's highest elevation layer).
- `dropdown.blade.php`: `bg-neutral-50`/`ring-black ring-opacity-5`/`shadow-lg` → `bg-surface-card`/`ring-neutral-900/5`/`shadow-elevation-2`.
- Mobile nav drawer (`navigation.blade.php`): `bg-neutral-50 shadow-xl` → `bg-surface-page shadow-elevation-3`.
- Public header's condensed-state shadow: raw `shadow-sm` → `shadow-elevation-1`.
- `layouts/public.blade.php` and `public-footer.blade.php`: raw `bg-neutral-50` → `bg-surface-page` (`bg-gradient-page` added to the layout too), matching every other layout/footer.

### Added
- `resources/views/partials/theme-init-script.blade.php`: the pre-paint anti-flash script, previously duplicated verbatim across all four layouts (`app`/`public`/`guest`/`labs`) — now one source of truth, `@include`d everywhere.
- `docs/05-ux/COMPONENT_PRINCIPLES.md`: a new "Dialogs (Modals)" section — Modal had never been documented at all (Gap Rule).

### Audited, Confirmed Clean or Intentional (not changed)
- Gradients: a repository-wide search for gradient usage outside the three approved tokens returned zero matches.
- Public vs. authenticated navigation behaviour differs — verified as `U-14.3`'s own explicit, intentional scoping (signature motion is public-marketing-only), not drift to merge away.
- No tooltip, toast, table, or skeleton-loader component exists anywhere in the customer-facing product — verified directly; auditing "theme coverage" for a component that doesn't exist is a non-finding, stated as such.

### Not Done (by design)
- Consolidating three single-line `prefers-reduced-motion` checks into a shared helper — considered and rejected as premature abstraction for genuinely different one-line concerns.
- A `<meta name="theme-color">` tag — a real but low-severity, cosmetic browser-chrome gap, named rather than fixed here.
- **Phases 4/5/7/8/10/11/12/14 (Typography, Spacing, Interaction, Visual Rhythm, Accessibility, Responsive, Performance, Product Polish) were not exhaustively re-audited screen-by-screen** in this delivery — named as `U-16.1`+, not silently skipped or falsely claimed complete.
- Browser verification — no browser automation tool exists in this environment; the commission's own Acceptance Criteria cannot be verified holistically without one, and this delivery does not claim that verification occurred.

### Verified
- Full regression: 482/482 passing (unchanged — the fixes are token substitutions with no behavioural change; verified via `npm run build` plus a compiled-CSS grep confirming every new utility class actually generated). `pint --test` clean.

## U-15.2 — Premium SaaS Homepage Recomposition

**Status:** Delivered by Engineering Office. Commissioned by `PO-U15.2-001`. Awaiting Product Office review. **Numbering note:** this ID nests under "U-15," but Programme U-15 is "Authenticated Product Excellence" — this commission's subject is the public homepage, unrelated to it; recorded under its own Programme heading, ID used as issued.

### Added
- Complete homepage recomposition (`resources/views/pages/home.blade.php`): Hero → Problem → Solution → Proof → How It Works → Product Capabilities → Trust → CTA, replacing the previous flat Hero/capability-strip/Proof/Explore/CTA structure. Every new section is brief and in different wording than the full explanations that remain on `/analyse` and `/reports` (`U-13.0`'s decision is not reversed).
- `docs/05-ux/HOMEPAGE_STORYBOARD.md` amended to v1.2 (Gap Rule, before implementation) documenting the new flow, the visual-rhythm rule ("no two adjacent sections share the same background treatment"), the progressive-CTA rule, and the explicit no-fabricated-testimonials decision.
- `docs/05-ux/COMPONENT_PRINCIPLES.md`: two new addenda — a "Report-preview treatment" note under Hero Blocks (a slim title-bar strip, `elevation-2`, no browser-chrome cliché) and a "Homepage capability card" note under Cards (icon-badge + hover arrow, composed from the existing `<x-card>` primitive).
- Header rebuilt (`public-nav.blade.php`): larger logo, more generous height, wider nav spacing, a hairline-divided utility area, active-nav-item pill background. Signature logo rotation (`U-14.3`) untouched — size classes only.
- A single, restrained scroll-reveal (opacity/translate-y, 300ms `ease-out`, once, `prefers-reduced-motion` respected) via plain `IntersectionObserver` — no animation library introduced.
- 3 new Pest tests; `tests/Feature/HomepageTest.php` substantially rewritten (9 tests total) for the new structure.

### Changed
- Footer (`public-footer.blade.php`): spacing/typography touch only (`py-12`→`py-16`, consistent tracking/spacing across columns) — architecture retained exactly as instructed, no column/link/structural change.

### Decisions Made, Stated Plainly
- **No OddStorm artifact exists, disclosed again**: as already recorded under `PO-U12.0-CP-002`, no reference file/screenshot/URL has ever existed in this repository or conversation. The commission's own "emotional parity, not visual imitation" instruction made literal inspection unnecessary regardless; reasoning came from `VISUAL_INSPIRATION.md`'s existing characterisation plus fresh specialist consultation.
- **No fabricated social proof.** Proof is the sample report and the Intelligence Credibility Section's verified, constitutional facts — never invented testimonials, which `TRUST_SIGNALS.md` and `docs/09-compliance/PRODUCT_GUARDRAILS.md` would not permit.
- Product Capabilities links only to the three genuinely public pages (Analyse/Reports/Planner); Journal/History/Dashboard are named as plain text, never linked, so a guest is never silently redirected to login from a homepage card.

### Specialist Design Capability Usage
UI UX Pro Max's design-system query returned an "AI-Native UI"/chatbot style with Calistoga/Inter typography — rejected (wrong product shape; would violate the Locked Figtree typeface); its "Minimal Single Column" pattern corroborated existing restraint, nothing new adopted. Its landing-domain "Trust & Authority"/"Hero + Testimonials + CTA" patterns corroborated the requested Problem→Solution→Proof→CTA narrative — adopted, with testimonial/social-proof carousels explicitly rejected in favour of real evidentiary proof instead. 21st.dev/Magic returned only glassmorphic/plasma/mouse-glow/gradient-text hero templates — all rejected as violating `VISUAL_INSPIRATION.md`; one non-code idea (report preview deserving more software presence) informed the title-bar-strip decision.

### Verified
- Full regression: 482/482 passing (up from 479). `pint --test` clean.

### Not Done (by design)
Browser QA across Chrome/Safari/Firefox/Edge/iOS Safari/Android Chrome — no browser automation tool exists in this environment, stated explicitly rather than implied. Responsive behaviour verified only via Tailwind breakpoint review of compiled markup.

## U-15.1 — Authenticated Screen Audit, Print/PDF Readiness & Loading-State Consistency

**Status:** Delivered by Engineering Office — one phase of Programme U-15, remainder explicitly deferred. Commissioned by `PO-U15-001`. Awaiting Product Office review.

### Added
- Risk Report (`betting-slips/report.blade.php`): a "Print / Save as PDF" action using the browser's native `window.print()` — no server-side PDF renderer exists or is justified by this alone. Printing hides site chrome and the exit-action buttons (`print:hidden`) and force-expands the report's three collapsible sections (Other Contributing Factors, Methodology, Report Details) via a `beforeprint`/`afterprint` script using Alpine's public `Alpine.$data()` API, so nothing a customer chose to keep is missing because an accordion was closed.
- `docs/05-ux/COMPONENT_PRINCIPLES.md`'s Reports section: a new "Print & Save as PDF" clause, added before implementation per the Gap Rule (no print/PDF pattern existed anywhere in `docs/05-ux/` before this commission).
- `wire:loading` feedback on the intake shell's four unsupported-method "Continue" buttons (`betting-slips/intake.blade.php`), matching the convention already used everywhere else in the authenticated app.
- 4 new Pest tests (`tests/Feature/BettingSlipIntakeTest.php`, `tests/Feature/Analysis/SlipAnalysisReportTest.php`).

### Audited, Not Changed (already satisfied)
Dashboard, authenticated navigation, and the Risk Report's core structure already reflect multiple prior polish passes (`U-11.1`-`U-11.5`, `U-12.0`) — hover/focus/active states, `min-h-11` touch targets, keyboard focus-trap, safe-area insets, and `bg-surface-card shadow-elevation-*` tokens were already in place and were not rewritten to appear busy.

### Specialist Design Capability Usage
UI UX Pro Max's SaaS-dashboard design-system query returned a teal/orange "Real-Time / Operations Landing" pattern with Plus Jakarta Sans — rejected (wrong pattern, wrong palette/typeface, would violate the Locked SGDS system); its skeleton/hover/active/disabled/empty-state guidelines were cross-checked against actual code and confirmed already satisfied except for the intake loading-state gap above. 21st.dev/Magic returned only multi-tenant sidebar-shell "Workspaces" dashboard templates — rejected, since SlipGuard has a single-tenant top-nav model and adopting a sidebar shell would be an unauthorised architectural change; nothing adopted.

### Decisions Made, Stated Plainly
- **History's sorting/searching/filtering/pagination/comparison and Journal's search/filtering/tagging are substantial net-new product capability, not visual polish, and were not built.** Verified directly: both screens currently call plain `->latest()->get()`, no query params, no pagination. Tagging needs a schema change (no `tags` column/table on `journal_entries`); a comparison view has no prior art in this codebase. Building either without Architecture Office (schema) and Product Office (scope) review would be unilateral scope expansion — named as the next phase's dependency instead of decided here.

### Verified
- Full regression: 479/479 passing (up from 477). `pint --test` clean.

### Not Done (by design)
Planner Workspace polish, Journal polish short of search/tagging, a full mobile/accessibility sweep, and a cross-screen design-consistency audit — phased to `U-15.2`+, not silently skipped. No browser verification — no browser automation tool exists in this environment.

## U-14.3 — Signature Motion System Implementation

**Status:** Delivered by Engineering Office. Commissioned by `PO-U14.3-001`, which itself provided the explicit Product Office confirmation this repository's precedent required before amending a Locked UX Constitution document. Awaiting Product Office review.

### Added
- `docs/05-ux/MOTION_SYSTEM.md`'s Logo Rotation section amended (Locked document, amended only now that explicit confirmation existed): two permitted public-site-only motions — a page-load opacity fade, and a scroll-linked rotation bounded to 15°-20° maximum.
- `<x-public-nav>` (`resources/views/components/public-nav.blade.php`): the logo now fades in on load (300ms, `ease-out`) and rotates as a direct linear function of scroll position, bounded to 18°, using a native `scroll` listener and `transform: rotate()` only — no animation library introduced. `prefers-reduced-motion: reduce` disables the rotation entirely (`window.matchMedia`, checked in JS since the rotation is an inline style).
- 3 new Pest tests (`tests/Feature/PublicPagesTest.php`): scroll-linked/bounded/reduced-motion markup verification, public-only scoping (absence on `/dashboard`), `MOTION_SYSTEM.md` documentation-presence verification.

### Fixed
- The existing Pricing test's blanket `assertDontSee('$')` began false-positive-failing once the rotation's JS template literal `` `rotate(${rotation}deg)` `` rendered on every public page — replaced with a precise `preg_match('/\$\d/', ...)` regex so the currency-fabrication check remains meaningful without being defeated by an unrelated, legitimate string.

### Decisions Made, Stated Plainly
- The whole existing logo lockup rotates as one unit — decomposing it into icon+wordmark was considered and rejected, since `public/brand/README.md` prohibits cropping/redrawing the approved artwork and no separate icon-only asset exists.
- Motion is scoped strictly to the public marketing layout — never rendered on authenticated screens, verified directly by asserting its absence on `/dashboard`.

### Specialist Design Capability Usage
UI UX Pro Max confirmed `prefers-reduced-motion` handling and the "1-2 key animated elements per view" ceiling (the logo is the one animated element site-wide) as already satisfied, and its 150-300ms micro-interaction timing guidance matches the load-fade's 300ms duration. 21st.dev/Magic returned only unrelated partner-logo-carousel/marquee components (highest confidence 0.54, matching only on the word "logo") — nothing adopted.

### Verified
- Full regression: 477/477 passing (up from 474). `pint --test` clean.

### Not Done (by design)
No browser verification — no browser automation tool exists in this environment, stated explicitly. The commission ties declaring the Premium Public SaaS Experience Programme (`U-14.0`-`U-14.3`) "constitutionally complete" to "successful implementation **and verification**" — Engineering does not self-declare that closure here; it is Product Office's determination.

## U-14.2 — SlipGuard Labs & Public Platform Ecosystem

**Status:** Delivered by Engineering Office — one item deliberately deferred. Commissioned by `PO-U14.2-001`. Awaiting Product Office review.

### Added
- `/pricing` rebuilt as an honest tiers page (Free — available now; Professional/Enterprise — coming later/future) — no invented price, discount, or subscription benefit anywhere.
- `/faq` — genuine, accurate answers to real product questions (prediction, fund custody, Planner scope, determinism, cost).
- `/release-notes` — four real entries sourced from this repository's own verified history, rewritten in plain customer language, no internal governance jargon or invented version/date.
- `resources/views/layouts/labs.blade.php` — a new layout branching nav/footer chrome by auth state, letting one Volt component serve both guests and customers correctly.
- `<x-badge>` extended with six `labs-*` tones (dedicated Labs status tokens, never repurposing `quality-*`), consolidating the Labs page's previously-bespoke status pill markup.
- 8 new/updated Pest tests across `LabsTest.php`, `PublicPagesTest.php`, `WorkspaceAccessTest.php`.

### Changed
- Primary public navigation trimmed to Home/Analyse/Planner/Reports/Pricing (+ Dashboard when authenticated) — About and SlipGuard Labs moved to the footer only.
- Footer rebuilt: Product (Analyse/Planner/Reports/Dashboard), Company (About/Pricing/SlipGuard Labs/Release Notes/Contact), Resources (Methodology → `/analyse`, FAQs, Research → SlipGuard Labs). The Community column is omitted entirely, not rendered empty.
- **SlipGuard Labs is now guest-visible** — `/labs` moved outside the `auth` middleware group. Guests see published features read-only; Notify Me/Join Beta render as a "Sign in to register interest" link for guests and remain fully functional for customers.

### Fixed
- Switching between two full-page layouts inside the Labs Volt component directly threw `Livewire\Features\SupportMultipleRootElementDetection\MultipleRootElementsDetectedException` (Livewire requires one root layout per component) — resolved with the new `layouts.labs` view.

### Decisions Made, Stated Plainly
- **The Signature Motion Language (logo rotation) is deliberately deferred, not implemented.** Neither `U-14.0` nor `U-14.1` has a formal Product Office acceptance entry. `U-14.2`'s footer spec ("Community (empty)... no feature requests") is direct evidence `U-14.1`'s findings were reviewed, so footer/nav content is treated as accepted-by-incorporation — but §6/§7 restates the original motion ask rather than confirming `U-14.0`'s specific proposed mechanics, and `MOTION_SYSTEM.md` is a Locked document. Editing it on an ambiguous signal was judged too consequential; it awaits one explicit confirmation.
- The `toggleInterest` action independently rejects unauthenticated calls (401) as defence in depth, since `labs_feature_interests.user_id` is not nullable.

### Specialist Design Capability Usage
UI UX Pro Max confirmed the Hero→tiers→FAQ→CTA pricing structure already built, and explicitly rejected its own "highlight/recommend the mid-tier, show an annual discount" conversion-optimisation guidance as inapplicable — Free is the only real, active tier, and highlighting it is an honesty signal, not an upsell nudge. 21st.dev/Magic returned only decorative, conversion-optimised React/shadcn pricing components (glassmorphism, animated particles) — nothing adopted.

### Verified
- Full regression: 474/474 passing (up from 469). `pint --test` clean.

### Not Done (by design)
Signature logo motion and any `MOTION_SYSTEM.md` amendment — pending explicit confirmation. No browser verification — no browser automation tool exists in this environment.

## U-14.1 — Public Navigation Simplification, Footer IA & SlipGuard Labs Experience: Specification

**Status:** Delivered by UX Studio — design specification only, no implementation. Commissioned by `PO-U14.1-001`. Awaiting Product Office review.

### Added
- `docs/05-ux/U-14/U-14.1-NAVIGATION-FOOTER-AND-LABS-ECOSYSTEM-SPECIFICATION.md` — a conflict-free, Engineering-ready navigation simplification; a revised footer information architecture; a recommendation (not a decision) on making SlipGuard Labs public; a proposal for a real Release Notes page sourced from this repository's own `CHANGELOG.md` history.

### Decisions Made, Stated Plainly
- **The commission's "Community" footer column conflicts with `SD-002`**: "Feature Requests" is the literal capability `SD-002` explicitly defers ("feature-request submission... community discussions... forums"). Resolved by dropping the Community column entirely — Roadmap/Join Beta folded into the Company column as links to the already-accepted SlipGuard Labs page; Feature Requests/Research Updates not built into this specification at all.
- **Making SlipGuard Labs public is a genuine access-boundary change, not a visual one** — `/labs` requires authentication today and `LabsFeatureInterest.user_id` is non-nullable. A public read-only teaser is recommended (Notify Me/Join Beta route through login, mirroring Dashboard's existing guest behaviour), but this specification does not decide it — Architecture Office (access boundary) and Compliance Office (guest-data review, per the `U-10.2` precedent) are named as the required next step.

### Specialist Design Capability Usage
UI UX Pro Max's breadcrumb guidance flagged as a future recommendation only if the Labs ecosystem gains nested sub-pages; its B2B/enterprise-trust product match cross-validated the existing palette. 21st.dev/Magic returned mostly decorative footer templates (rejected) and one structural idea worth keeping — a compact, lean footer rather than a column padded with placeholder pages.

### Not Done (by design)
No code changed. Footer/Labs/Release Notes recommendations are proposals, not accepted decisions — nav simplification alone is recorded as ready for direct Engineering implementation once this specification is accepted. No browser verification — no browser automation tool exists in this environment.

## U-14.0 — Premium SaaS Product Experience: Specification

**Status:** Delivered by UX Studio — design specification only, no implementation. Commissioned by `PO-U14.0-001`. Awaiting Product Office review.

### Added
- `docs/05-ux/U-14/U-14.0-PREMIUM-EXPERIENCE-AND-SIGNATURE-MOTION-SPECIFICATION.md` — a proposed Signature Motion Language (scroll-linked, deterministic logo rotation), a premium visual-rhythm audit, a component-polish review, and Engineering-ready implementation guidance for if/when accepted.

### Decisions Made, Stated Plainly
- **A genuine constitutional conflict was found and flagged, not silently resolved either way**: the commission's Signature Motion Language directly contradicts `MOTION_SYSTEM.md`'s existing Locked Logo Rotation rule ("only permitted logo motion is a subtle opacity fade on page load"). A full amendment is proposed in the specification but **not applied** — it awaits Product Office acceptance, mirroring the precedent `U-12.0`'s gradient-system amendment set for exactly this situation.
- The visual-rhythm and component-polish audits found the existing `U-12.0` token/component foundation already satisfies the commission's criteria — this is recorded as a genuine finding (not padding), with one concrete per-page hero-composition recommendation made rather than a large invented workload.

### Specialist Design Capability Usage
UI UX Pro Max queried on premium/restrained motion and logo/brand-mark conventions — cross-validated `MOTION_SYSTEM.md`'s existing scroll-reveal rule as already matching genuine best practice, and independently flagged elastic-easing/magnetic-hover effects as inappropriate (corroborating the commission's own forbidden list). 21st.dev/Magic queried on scroll-linked logo animation — returned only unrelated partner-logo-carousel components and a glassmorphism/magnetic-button/aurora-glow "Motion Footer"; nothing adopted.

### Not Done (by design)
No code changed — per `docs/offices/UX_STUDIO.md`'s own constitutional boundary against writing application code or introducing a new interaction pattern without prior documentation and Product Office approval. `MOTION_SYSTEM.md` not yet amended (proposal only). No browser verification — no browser automation tool exists in this environment. Engineering implementation of anything in this specification requires a separate commission.

## U-13.0 — Public Website Experience, Information Architecture & Premium SaaS Presence

**Status:** Delivered. Commissioned by `PO-U13.0-001`. Awaiting Product Office review.

### Added
- Genuine multi-page public architecture, replacing the single-page homepage: `/analyse`, `/planner` (route name `planner.public`), `/reports`, `/about`, and honest stubs for `/pricing`, `/contact`, `/privacy`, `/terms`. Every nav destination is a real route — no `#anchor` navigation anywhere.
- Shared public layout: `App\View\Components\PublicLayout` (`layouts/public.blade.php`), `<x-public-nav>` (with active-page indication), `<x-public-footer>` (Product/Company/Legal link columns) — one header/footer for every public page instead of duplicating it.
- 24 new Pest tests: `tests/Feature/PublicPagesTest.php`; `HomepageTest.php` rewritten for Home's new trimmed content.

### Changed
- `/` (Home) is now an introduction only — hero, capability strip, Intelligence Credibility Section, an "Explore SlipGuard" section, CTA. Problem/How-it-works moved to `/analyse`; the full example report moved to `/reports`; "what SlipGuard is not" moved to `/about`.
- `docs/05-ux/HOMEPAGE_STORYBOARD.md` amended (1.0→1.1): its original single-page 7-section flow explicitly superseded by the new multi-page architecture; moved sections' original copy retained as the historical record for their new pages, not silently overridden.

### Fixed
- A raw `<?php ?>` block at the top of a file whose root element is a component tag gets silently stripped by Blade (verified directly) — fixed by switching to `@php`/`@endphp`.
- `coming-soon.blade.php` still had hardcoded `gray-*`/`bg-white` remnants, missed by every prior theme pass.

### Decisions Made, Stated Plainly
- **No pricing page was built with real numbers.** No pricing model, tier, or price point exists anywhere in this repository; `PROJECT.md`'s MVP Non-Goals excludes "advanced subscriptions," and the already-Locked `PO-U11.2-CL-002` rejects "pricing presentation." `/pricing` is an honest stub.
- **No real Privacy/Terms text was written.** `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s own standing note (legal review required before public launch) is unaddressed; both routes are honest stubs.

### Specialist Design Capability Usage
UI UX Pro Max queried on premium multi-page SaaS information architecture — rejected an "Enterprise Gateway" pattern (persona-selection, mega-menu, client logos, none of which fit SlipGuard) and a "Liquid Glass" style (the tool's own listing flags moderate-poor performance and contrast risk; wrong accent colour; wrong typeface). Its navigation "Active State" guideline was **adopted** — `<x-public-nav>` now marks the current page. 21st.dev/Magic queried on hero/nav/footer composition — returned only incompatible decorative React/shadcn results; nothing adopted.

### Verified
- Full regression: 469/469 passing (up from 461). `pint --test` clean.

### Not Done (by design)
Browser verification — including the commission's own request for continuous verification throughout implementation — was not possible. No browser automation tool exists in this environment, unchanged throughout this repository's history. No screenshots supplied.

## ADR-012 — Product Boundaries, Operator Independence & Regulatory Positioning

**Status:** Accepted (Product Office, direct founder ruling). Locked, permanent.

### Added
- `docs/adr/ADR-012-PRODUCT-BOUNDARIES-OPERATOR-INDEPENDENCE-AND-REGULATORY-POSITIONING.md` — nine permanent constitutional principles (no customer-fund custody, SlipGuard never decides for the customer, no outcome prediction reaffirmed, no autonomous betting, operator independence, user control, explainability first, risk-awareness-over-excitement, regulatory-simplicity-by-design), a permitted-future-integrations list (none commissioned by this ADR), a permanent prohibited-directions list, and a Constitutional Research Gate for SlipGuard Labs' future evolution.
- `docs/adr/ADR-INDEX.md`: new `ADR-012` row; `ADR-011` also given a row (status Reserved) — it was already cited inline (`U-10.1`/`U-10.3`) but had never appeared in the index.
- `docs/09-compliance/PRODUCT_GUARDRAILS.md`: new Operator & Custody Boundaries section.

### Changed
- `CLAUDE.md`: five new Locked Decisions (fund-custody prohibition, autonomous-betting prohibition, operator independence, risk-awareness-over-excitement, regulatory-simplicity-by-design). The pre-existing "no outcome prediction"/"no safe-bet claims"/deterministic-explainable/Planner-editability entries are reaffirmed, not replaced.

### Verified & Corrected Before Recording
- The commissioning document self-identified as "ADR-010," colliding with the real, already-Accepted `ADR-010` (Journal Entry Reference Boundary). `ADR-011` was also already reserved. Architecture Office additionally recommended classifying this content as an `SD`-series strategic decision rather than an ADR, given its business-model/product-identity scope. **Product Office ruled directly: record as `ADR-012`**, reusing neither prior number, overriding the `SD`-series recommendation.
- "Safeguard Labs" does not exist anywhere in this repository — confirmed a naming error for **SlipGuard Labs** (`SD-002`, Programme U-13). **`SD-002` is preserved exactly as originally accepted, not broadened or rewritten** — the new Constitutional Research Gate applies to SlipGuard Labs' future evolution within `SD-002`'s existing, unchanged charter.

### Not Done (by design)
None of the five permitted-future-integration categories are commissioned, scoped, or architected by this ADR — each requires its own Architecture Office discovery, Parser Office viability assessment, and Compliance Office review before Engineering begins. No compliance/legal review performed; `PRODUCT_GUARDRAILS.md`'s own standing pre-launch legal-review note remains outstanding.

## PO-GOV-UX-002 — Mandatory Specialist Design Capability Invocation (Consolidates & Extends PO-GOV-UX-001)

**Status:** Locked, permanent. Founder/Product Office decision, given directly.

### Changed
- `CLAUDE.md`: the **Mandatory Specialist Design Capability Usage** section renamed to **Mandatory Specialist Design Capability Invocation** (matching this directive's exact title) and updated in place — not duplicated as a second section. Adds a standing browser-verification requirement: where browser access exists, every qualifying commission concludes with browser verification (desktop/tablet/mobile × light/dark, navigation, a representative form/dashboard, responsiveness, hierarchy, spacing, gradients, accessibility); where it doesn't, that is stated explicitly, never implied.
- `PROJECT.md`: Approved Tooling Policy pointer updated to reference the renamed section and the browser-verification addition.
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-GOV-UX-002`, verified directly that it is substantively the same rule as `PO-GOV-UX-001` (reworded/reorganized) plus the one genuine addition above — not a new policy restated as if it were. `PO-GOV-UX-001`'s own entry stands unedited, per the log's append-only rule.

### Not Done (by design)
No code touched — governance only.

## PO-GOV-UX-001 — Mandatory Specialist Design Capability Invocation (Standing Governance Rule)

**Status:** Locked, permanent. Founder/Product Office decision, given directly, following review of `U-12.0`'s execution methodology.

### Changed
- `CLAUDE.md`: new **Mandatory Specialist Design Capability Usage** section — elevates the existing Tool Invocation Policy (`TOOL-UX-001`, reference/inspiration only) from optional to mandatory, where available, for future qualifying commissions (public website, product experience, design system, general UX/visual work). Defines the required workflow (review repository → invoke both capabilities before finalizing significant design decisions → compare against product identity/Locked Decisions/UX Constitution → adopt/modify/reject explicitly → document) and the completion-report requirement (a dedicated "Specialist Design Capability Usage" section).
- `PROJECT.md`: Approved Tooling Policy updated with a pointer to the new mandatory-usage rule.
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-GOV-UX-001` as a Locked, permanent governance rule.

### Not Done (by design)
No code touched — this is a process/governance amendment only. Neither capability ever overrides a Product Office, architectural, or compliance decision; both remain advisory. "No recommendation adopted, here's why" is explicitly confirmed as a legitimate, sufficient outcome — the rule requires evaluation, not forced adoption.

## PO-U12.0-CP-002 — U-12.0 Completion Pass

**Status:** Delivered. Commissioned by `PO-U12.0-CP-002`. Awaiting Product Office review.

### Added
- Semantic container hierarchy: `container-reading` (720px), `container-standard` (960px), `container-analytics` (1200px), `container-marketing` (1280px) — four Tailwind `@utility` classes (`resources/css/app.css`), documented in `DESIGN_TOKENS.md`. Replaces the ad hoc `max-w-2xl`–`max-w-7xl` mix the original `U-12.0` completion report flagged as an audit gap.
- 6 new Pest tests: `tests/Feature/DesignSystemTest.php` additions (container-utility CSS presence, per-screen container assignment, the Risk Report deviation).

### Changed
- Every screen migrated onto the new container scale: homepage → `container-marketing`; Dashboard/Slip Index/History/Journal/Profile/intake shell → `container-standard`; Builder/Planner Workspace/Planning History → `container-analytics`; Journal Entry → `container-reading`.
- Risk Report and Planner Workspace: all 22 ad hoc `bg-neutral-50 border border-neutral-200` surface panels migrated to `bg-surface-card shadow-elevation-1`; each screen's headline risk-band section upgraded to `shadow-elevation-2`, per `COMPONENT_PRINCIPLES.md`'s Reports rule that the headline sits at the highest elevation on the page. No mathematics, workflow, or component structure altered.

### Fixed
- Two hardcoded `text-gray-900`/`text-gray-600` remnants in the Planner Workspace's abandon-session modal — missed by both `U-11.5` and the original `U-12.0` pass.

### Decisions Made, Stated Plainly
- **Marketing container width was not adopted at 1440px by assumption**, per a mid-pass Product Office instruction requiring the width be determined by analysing "the approved OddStorm reference." Verified directly: no such artifact — file, screenshot, or URL — has ever been supplied anywhere in this repository or conversation; every prior OddStorm description was prose characterization only. Rather than guess and fetch an unverified external URL (fabricating an inspection that didn't happen), the width was reasoned from what's actually available: `VISUAL_INSPIRATION.md`'s own, more authoritative reference set (Stripe, Linear), and a direct UI UX Pro Max query confirming a "minimal single-column, generous whitespace" fit. **1280px** selected on that basis.
- **One flagged deviation from the commission's own migration table**: the Risk Report uses `container-reading` (720px), not the directed `container-analytics` (1200px) — preserving `COMPONENT_PRINCIPLES.md`'s pre-existing "a document to read, not a dashboard" principle for that one screen, which the commission's table didn't address or override.

### Specialist Design Capability Usage
UI UX Pro Max queried on container/layout rhythm and marketing-page conventions (informed the 1280px reasoning above; confirmed `container-reading`'s 720px sits in the standard 65–75ch readable-prose range). 21st.dev/Magic queried on SaaS container/layout composition — returned only React/shadcn catalog components, decorative and stylistically incompatible (glassmorphism, floating particles, glow effects); nothing adopted, a genuine negative result.

### Verified
- Full regression: 461/461 passing (up from 455). `pint --test` clean.

### Not Done (by design)
**Browser verification remains impossible in this environment** — no browser automation tool exists here, unchanged throughout this repository's history. Stated explicitly rather than attempting `php artisan serve`/`npm run dev` and claiming a visual review that could not actually be performed. No screenshot evidence supplied.

## U-12.0 — SlipGuard Design Language System (SGDS)

**Status:** Delivered by UX Studio/Architecture Office/Engineering Office jointly, as one controlled work package. Commissioned by `PO-U12.0-001`. Awaiting Product Office review.

### Added
- `docs/05-ux/SGDS.md` — the one concise, implementation-facing design-system entry point, cross-referencing `DESIGN_TOKENS.md`/`COMPONENT_PRINCIPLES.md` rather than duplicating them.
- A genuine surface hierarchy (`surface-page`/`surface-soft`/`surface-card`/`surface-inverse`) — cards were previously the same tone as the page behind them, differentiated only by a border.
- Real theme-aware elevation shadows (`shadow-elevation-1/2/3`), replacing Tailwind's raw `shadow-sm`/`shadow-md`, which read as a generic dark drop-shadow that visually disappears against a dark surface.
- Three restrained gradient tokens (`gradient-page`, `gradient-hero`, `gradient-inverse`) — applied to the authenticated app shell/guest layout background, the homepage hero, and the Intelligence Section's dark band only; never per-card, never on a risk/quality/status colour.
- Dedicated alert tokens (`alert-success/error/caution/info`), fixing a real pre-existing gap: ad hoc `bg-red-50`/`bg-green-50` flash messages had no dark-mode value at all.
- A Type Roles mapping table (`DESIGN_TOKENS.md`) — no new font size; Figtree kept, refined rather than replaced, per the commission's own Font Decision Rule.
- Confidence tokens (`confidence-high/medium/low`), reserved and unused, for `PO-U11.5A-001`'s not-yet-built OCR confidence display.
- Seven new shared Blade components — `x-card`, `x-badge`, `x-alert`, `x-empty-state`, `x-page-header`, `x-metric-card`, `x-file-drop` — each replacing a pattern with real, multiple existing consumers.
- 7 new Pest tests: `tests/Feature/DesignSystemTest.php`.

### Changed
- `docs/05-ux/DESIGN_TOKENS.md`'s prior "No gradients. Flat colour only" rule superseded by a restrained, theme-aware gradient system — a constitutional amendment, recorded explicitly per the commission's own §7 instruction, not a silent change.
- `docs/05-ux/VISUAL_INSPIRATION.md`'s "Neon gradients" rejection narrowed alongside it, to state the distinction (saturated/decorative rejected; restrained/atmospheric approved) rather than silently contradict the amendment above.
- Applied across the shared layouts/navigation, homepage, Dashboard, Betting Slip Index, Builder, intake shell, History, Journal, Journal Entry, Planning History, and profile forms.

### Fixed
- `layout/navigation.blade.php` and the intake shell (built after `U-11.5`'s own migration pass) both still had hardcoded `bg-neutral-900`/`bg-white`/`gray-*` remnants that pass had missed — corrected here.

### Verified
- Full regression: 455/455 passing (up from 448). `pint --test` clean.

### Not Done (by design — scope)
The Risk Report screen (`report.blade.php`) and Planner Workspace (`planner/session.blade.php`) were not migrated to the new component primitives this pass — both are already fully theme-aware and governed by their own detailed, previously-accepted layout rules; converting their internals is mechanical and named here as a deferred follow-up, not silently left incomplete. No browser-based visual verification was possible in this environment — stated explicitly, per the commission's own §33.3 allowance; every URL/state it names remains outstanding for human QA.

## PO-U11.5A-001 — Intelligent Slip Capture: MVP Non-Goals Amendment (Strategic Authority Only)

**Status:** Constitutional amendment recorded. **Not commissioned to Engineering** — returned to Product Office to sequence through Architecture Office, Parser Office, and Compliance Office first.

### Changed
- `PROJECT.md`: MVP Non-Goals amended — "OCR, bookmaker parsing" narrowed to a precise exclusion list (bet code/share-link retrieval, bookmaker APIs, customer account linking, email/WhatsApp/clipboard import, browser extensions, automatic bet placement). Converting a customer's own uploaded screenshot/PDF into a structured, customer-reviewable slip draft (OCR + parsing + automatic Slip Builder population, always subject to mandatory customer confirmation before analysis) is now an approved MVP capability, Programme U-11.5A.
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U11.5A-001` as a Locked constitutional amendment, strategic authority only. The prior, narrower resolution (honest non-functional intake shell only) remains recorded unedited above it, per the log's append-only rule.
- `TASKS.md`: new Programme U-11.5A section, tracking Architecture/Parser/Compliance Office review as not-yet-commissioned prerequisites to Engineering implementation.

### Not Done (by design — scope)
No code, migration, package, or UI touched. Engineering implementation is explicitly blocked pending Architecture Office (data flow, confidence-score model), Parser Office (OCR/PDF backend viability and validation contract — verified directly that no such capability exists anywhere in this repository today), and Compliance Office (upload handling, retention, third-party data-sharing exposure) review and handover — mirroring how every other genuinely new capability in this repository (Planner, Operations Console) was sequenced.

## U-11.5 — Theme Completion

**Status:** Delivered by Engineering — pure token migration, no new component/layout/interaction/copy. Commissioned by `PO-U11.5-001`. Awaiting Product Office review.

### Fixed
- Migrated the Builder, Betting Slip Index's inner content, History, Journal, Journal Entry, Planning History, the four auth pages (login, register, forgot-password, confirm-password), and all three profile forms from hardcoded `gray-*`/`white`/`indigo-*` Tailwind classes to the existing `neutral-*`/`accent`/`accent-strong` design tokens.
- **Corrected a gap in `U-11.3`'s "shared layout chrome fully theme-aware" claim:** `layout/navigation.blade.php`'s desktop nav bar, notifications dropdown, user-menu trigger, and mobile hamburger button still had hardcoded classes — fixed.
- Migrated every shared Blade component the above screens consume (`action-message`, `dropdown-link`, `dropdown`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`) so the per-screen migration is actually effective, not cosmetic. `danger-button` and `modal`'s dark backdrop scrim deliberately left unchanged (semantically theme-independent regardless of theme).
- **Latent defect found and fixed:** `@tailwindcss/forms`'s hardcoded white input/textarea/select background was never overridden anywhere in the codebase — every text input (including `U-11.3`'s own intake-shell inputs) would have shown a white box in dark mode. Fixed via an explicit `bg-neutral-50` utility class (resolves to the correct light/dark hex through the existing CSS custom property) on `x-text-input` and the few raw form elements not using that component.

### Added
- 7 new Pest tests: `tests/Feature/ThemeCompletionTest.php` — negative assertions confirming no hardcoded class survives on each migrated screen's rendered output.

### Verified
- Full regression: 448/448 passing (up from 441). `pint --test` clean.

### Not Done (by design — scope)
No browser-based visual verification was possible in this environment — stated explicitly, not implied.

## U-11.4 — Post-Hero Intelligence Credibility Section

**Status:** Delivered by Engineering — presentation-layer implementation only. Commissioned by `PO-U11.4-001`. Awaiting Product Office review.

### Added
- A dark, full-width Intelligence Credibility Section on the public homepage, between the hero/capability strip and the Problem section: eyebrow, heading, supporting copy, and a 6-metric grid (Deterministic 100%, Outcome Predictions 0, "Every Finding Explained," Automated Tests, Structural Factors, Main Contributing Factor) driven by a single `$slipguardMetrics` array of verified constitutional/system facts (6 registered `RiskFactor` classes, current Pest suite size) — no fabricated adoption/usage statistic (no slips-analysed count, no success/win rate).
- A once-only, `IntersectionObserver`-triggered count-up animation on numeric metrics, skipped entirely (final value shown immediately) when `prefers-reduced-motion: reduce` is set.
- Tests confirming the section renders only verified facts and never a fabricated adoption statistic (`tests/Feature/HomepageTest.php`).

### Not Done (by design — scope)
The commission's optional 3-tab structure (Analysis/Planner/Reflection) was not built — a fresh beta database has near-zero real customer volume, and the commission's own §9.4 explicitly permits a single non-tabbed grid instead of tabs when there isn't yet meaningful live data to differentiate, which applies here. No browser-based visual verification (count-up animation, reduced-motion behaviour) was possible in this environment — stated explicitly, not implied.

## U-11.3 — Public Experience Alignment, Theme System & Multi-Method Slip Intake

**Status:** Delivered by Engineering — presentation-layer implementation only. Commissioned by `PO-U11.3-001`; the OCR/bookmaker-parsing Non-Goals conflict was flagged before implementation and resolved by direct Product Office decision (`docs/00-governance/DECISION_LOG.md`, 2026-07-27). Awaiting Product Office review.

### Added
- **Phase 1 — Header/hero/marquee/theme:** redesigned sticky public header (restrained two-state scroll response, not a continuous morph), a split two-column hero (proposition + CTAs left, a "Sample Structural Report" demonstration reusing the real report's exact risk-band styling right), and a static, non-animating capability strip (a wrapping list, not an auto-scrolling marquee — an auto-playing loop is one of `MOTION_SYSTEM.md`'s explicit Forbidden Animation Patterns).
- A full light/dark theme system: a three-state manual override (`system` / `light` / `dark`) layered on top of the pre-existing automatic dark-mode CSS, persisted in `localStorage`, applied via a synchronous pre-paint `<script>` in every layout's `<head>` to avoid a flash of the wrong theme. New `<x-theme-toggle>` component (sun/moon, Alpine-driven) added to the guest layout, the authenticated navigation (desktop + mobile), and the public homepage header.
- Migrated the shared layouts (`layouts/app.blade.php`, `layouts/guest.blade.php`, `layouts/navigation.blade.php`) and the homepage from hardcoded `gray-*`/`white` classes to the existing `neutral-*` design tokens, so the automatic and manual theme mechanisms actually take effect on every page's chrome.
- **Phase 2 — Multi-method slip intake shell:** new `analyze/new` route and `betting-slips.intake` Volt component — a method selector (Manual Entry, Upload Screenshot, Paste Slip Text, Upload PDF, Bet Code/Share Link), each with a genuine, interactive input control. Manual Entry links straight to the existing, unchanged builder. Every other method, on submission, shows an honest `<x-betting-slips.intake-unavailable-notice>` ("Automated reading isn't available yet") naming the specific unsupported capability and linking back to Manual Entry — no file, pasted text, or bet code is ever processed, stored, or transmitted, since no extraction capability exists to receive it. The betting-slip index's "New Slip" button now points here instead of straight to the builder.
- 8 new Pest tests: `tests/Feature/BettingSlipIntakeTest.php` (method-selector rendering, each unsupported method's honest-unavailable state, Manual Entry fallback links, method-switching reset, auth gating, index button target); 4 new: `tests/Feature/ThemeSystemTest.php` (pre-paint script and toggle presence on public/authenticated/guest surfaces, CSS override selectors present).

### Verified
- **Phase 3 — Extraction-capability audit:** confirmed by direct grep across the entire repository that zero OCR, PDF-extraction, image-processing, or bookmaker-parsing capability exists anywhere in the codebase, before any intake UI was built.
- Full regression: 441/441 passing (up from 426). `pint --test` clean.

### Determined (Phase 4 — per-processor disposition, none implemented)
OCR (image), PDF text/field extraction, bookmaker-format recognition, and bet-code/share-link parsing all require a Parser Office specification (`docs/offices/PARSER_OFFICE.md`) before any of them can be implemented — none is an Engineering-only decision, and none is implemented in this delivery.

### Not Done (by design — scope)
**Phase 5 (theme completion) is only partly done.** The shared layout chrome and homepage are fully theme-aware; the following screens still use hardcoded `gray-*`/`white` classes and do not yet respond to the manual theme toggle: the Builder, the Betting Slip Index's inner content, History, Journal, Journal Entry, Planning History, the four auth pages (login/register/forgot-password/reset-password), and the three profile forms. This is a bounded, named follow-up, not silently dropped scope. No OCR, PDF parsing, image recognition, bookmaker parsing, or bet-code parsing is implemented anywhere in this work package, per the Product Office's exact decision. No browser-based visual verification (theme toggle's actual client-side repaint, count-up animation, reduced-motion behaviour) was possible in this environment — stated explicitly, not implied.

## U-11.2 — Approved UX Implementation & Customer Experience Enhancement

**Status:** Delivered by Engineering — presentation-layer implementation only. Commissioned by `PO-U11.2-001`. Awaiting Product Office review.

### Added
- All seven `HOMEPAGE_STORYBOARD.md` sections (Problem, How SlipGuard Works, Example Report, Trust, Journal, CTA) — the public homepage previously rendered only the Hero. No new marketing copy invented; Trust/Journal reuse exact existing approved strings verbatim, Example Report reuses the real report screen's own copy with clearly-labelled sample data, styled identically to the real thing.
- A Planner-presence card on the Dashboard, shown only when a non-terminal Planner session genuinely exists.
- 8 new Pest tests: `tests/Feature/HomepageTest.php`, plus additions to `tests/Feature/DashboardTest.php`.

### Fixed
- The Dashboard's Journal section previously always rendered the empty state regardless of real data — now reads the customer's actual latest journal entry.
- `betting-slips/index.blade.php`'s ad hoc amber/blue status badges migrated to the neutral treatment already established for lifecycle-status badges elsewhere (Planning History's own status badge).

### Verified
- Full regression: 426/426 passing (up from 418). `pint --test` clean. Dashboard's existing query-count budget reconfirmed still holds with the new Journal/Planner queries.
- One icon substitution: the homepage's Problem-section illustration was checked against `ICONOGRAPHY.md`'s approved metaphor list and corrected to an approved metaphor.
- One correction made during implementation, not before: `U-11.1`'s own suggestion to migrate status badges to risk-band tokens was found to conflict with `DESIGN_TOKENS.md`'s rule against repurposing risk/quality colours for non-risk meanings — the neutral treatment was used instead.

### Not Done (by design — scope)
`U-11.1`'s proposed homepage Planner section (OQ-1) and scroll-responsive header (OQ-2) — both remain gated on separate Product Office approval, not bundled into this delivery. No Planner logic, deterministic engine, Workspace architecture, Operations Console, or governance touched. No browser-based visual verification was possible in this environment — stated explicitly.

## U-11.1 — Premium Customer Experience Redesign

**Status:** Delivered by UX Studio — design specification only, no implementation. Commissioned by `PO-U11.1-001`, clarified by `PO-U11.1-CL-001`. Awaiting Product Office review.

### Added
- `docs/05-ux/U-11/U-11.1-PREMIUM-CUSTOMER-EXPERIENCE-REDESIGN.md` — full redesign specification across Landing, Dashboard, Analyze Slip, History, Journal, Planning History, Labs, and Navigation, staying entirely within the existing, unamended UX Constitution.

### Findings
- **Corrected two factual errors** in the commissioning handover: `SlipGuard Labs` and `Planning History` are both fully implemented, working features, not placeholders.
- **Found a real, previously unflagged defect:** the Dashboard's Journal section is hardcoded to always show the empty state, regardless of real data — accurate when written (pre-`U-08.2`), false since.
- **Found the highest-leverage recommendation requires no new design work:** `HOMEPAGE_STORYBOARD.md` (Approved) fully specifies a seven-section homepage never implemented — today's page is the Hero section alone.
- Checked the supplied "Appendix A" interaction-language document against `MOTION_SYSTEM.md` directly: excluded a scroll-driven logo animation (forbidden by the Logo Rotation section) and a card hover position shift (forbidden by Hover Behaviour); proposed a two-state scroll-responsive header as a narrow addition requiring approval, not assumed pre-cleared.
- Every intelligence-visibility candidate verified against actually-persisted database columns before being recommended — no invented metric.

### Not Done (by design — scope)
No deterministic mathematics, Planner logic, architecture, or governance touched. No implementation performed. Two proposed additions (a new homepage section, the scroll-responsive header) remain explicitly gated on Product Office approval.

## PO-PD010-001 — Transition to Controlled Beta Execution (with a Correction)

**Status:** Product Office directive. SlipGuard transitions from Foundation Construction to Controlled Beta Execution. Programmes U-07/U-08/U-09/U-10 frozen except for genuine defects or separately-commissioned enhancements. Supersedes no prior architecture, governance, or implementation record.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-PD010-001`'s execution-policy directive, and separately corrected its Executive Summary — `U-09` is listed as "constitutionally complete" alongside `U-07`/`U-08`/`U-10`, but unlike those three (each with an explicit Product Office acceptance record), `U-09` was never formally accepted or closed; its own record ends with Engineering's recommendation, returned for a release determination that never followed.
- `TASKS.md`: added a "Current Phase" note reflecting the beta-execution transition and the same `U-09` status correction, superseding the long-stale "Active Milestone" note (retained below, marked historical).

## PO-U10.3-AC-001 — Operations Console Implementation: Formal Acceptance & Programme U-10 Constitutional Closure

**Status:** Product Office formal acceptance. Work package **U-10.3** is **COMPLETE and closed**. Programme U-10 (`U-10.1`–`U-10.3`) is constitutionally complete.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U10.3-AC-001`. Test evidence (418/418 passing, `pint --test` clean) reverified directly before recording, not merely accepted on report.

## U-10.3 — Operations Console Implementation

**Status:** Delivered by Engineering — internal tooling only, no customer-facing change. Commissioned by `PO-U10.3-001`. Awaiting Product Office review.

### Added
- `can_manage_customer_data` column — a distinct permission from `is_internal` (resolves `U-10.1`'s OQ-1). `SupportNote` model (append-only: no update/delete action or policy ability exists anywhere) and `OperationsAuditLogEntry` model (immutable: no `updated_at` column). `App\Domain\Operations\OperationsAuditAction` enum and `App\Actions\Operations\{CreateSupportNote,RecordAuditEvent}`.
- `CustomerResource` (Filament) — read-only customer search/overview scoped to non-internal users, with authorization defined directly on the Resource class rather than via an Eloquent Policy on `User`, keeping it structurally independent of any customer-facing authorization path. Four relation managers: Betting Slips, Analyses, Planner Sessions (all read-only), and Support Notes (create-only).
- `AuditLogResource` (Filament) — read-only listing of every recorded operations event.
- 15 new Pest tests: `tests/Feature/Operations/{CustomerResourceTest,SupportNoteTest,JournalVisibilityTest,AuditLogResourceTest}.php`.

### Fixed
- A `$navigationGroup` type-declaration mismatch against Filament's base `Resource` class — caught as a fatal error during implementation, before any test was written, not shipped and discovered later.

### Verified
- **Journal entries never appear by default.** The Customer Overview shows only a count; full content requires the explicit "View Journal Entries" action, which logs its own dedicated `journal_viewed` audit event, separate from the `customer_viewed` event already logged when the page opens. A test confirms merely opening a customer's page neither surfaces Journal content nor logs a `journal_viewed` event.
- Session diagnostics (IP, user agent, last activity) are read directly from Laravel's own existing `sessions` table — no new data capture.
- Full regression: 418/418 passing (up from 403). `pint --test` clean.

## PO-U10.2-AC-001 — Operations Console Governance: Formal Acceptance (with a Noted Inconsistency)

**Status:** Product Office formal acceptance. Work package **U-10.2** is **COMPLETE and closed**. Constitutional ownership returns to Product Office.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U10.2-AC-001`'s acceptance, and separately noted an internal inconsistency in that same document — §4 claims Engineering "is authorised to implement," while §6 and §8 both state implementation is not approved by this acceptance and a separate commissioning step remains. Read §6/§8 as controlling; Engineering has not begun implementation and awaits a distinct handover addressed to the Engineering Office.

## U-10.2 — Operations Console Governance, Permissions & Compliance Policy

**Status:** Delivered by Compliance Office — policy definition only, no code. Commissioned by `PO-U10.2-001`. Awaiting Product Office review.

### Added
- `docs/09-compliance/U-10.2-OPERATIONS-CONSOLE-GOVERNANCE-POLICY.md` — resolves all five of `U-10.1`'s Open Questions as policy. Key determinations: a distinct permission (beyond `is_internal`) is required to view customer data; Journal entries are restricted from the default Customer Overview and require their own explicit view action plus a dedicated audit entry, given their more personal nature than structural analysis/Planner data; `SupportNote`s are append-only with no edits or deletion, ever; audit logging is session-event granular (Journal-entry access excepted) and retained indefinitely and immutably; IP/user-agent/session diagnostics are approved as proportionate, application-version/request-ID diagnostics deferred.

### Not Done (by design — scope)
No code, database schema, or Engineering approval issued — policy only, per the handover's explicit constitutional boundary. A separate Engineering commission is required once Product Office reviews this policy alongside `U-10.1`'s architecture.

## U-10.1 — Operations Console & Customer Support Visibility: Architecture

**Status:** Delivered by Architecture Office — architecture definition only, no code. Commissioned by `PO-U10-001`, directly following `U-09`'s admin-visibility finding. Awaiting Product Office review.

### Added
- `docs/02-architecture/U-10.1-OPERATIONS-CONSOLE-ARCHITECTURE.md` — specifies customer search/overview and read-only analysis/Planner/Workspace visibility as pure read models over existing data (no new tables); a new `SupportNote` model (append-only recommended) and a new, first-party `OperationsAuditLogEntry` model (no third-party package); a proposed `ADR-011` requiring Operations Console authorization to stay entirely independent of the existing customer-facing policies.
- Identified that Laravel's own existing `sessions` table (already populated via `SESSION_DRIVER=database`) already carries IP address, user agent, and last-activity per user — closing most of the "Operational Diagnostics" ask with no new data capture required.

### Not Done (by design — scope)
No code, migration, or UI. Five Open Questions raised for Product Office/Compliance Office (permission granularity, note editability, audit-log granularity, a terminology confirmation, whether request/version-level diagnostics should be built now or deferred), none blocking. Compliance Office review explicitly required, per the commission's own instruction, before `SupportNote`/audit logging/diagnostic surfacing are implemented.

## U-09 — Production Readiness & Beta Release Preparation

**Status:** Delivered by Engineering — validation and low-risk fixes only, no new capability. Commissioned by `PO-U09-001`; awaiting Product Office release determination.

### Added
- `docs/engineering/U-09-PRODUCTION-READINESS-REPORT.md` — full review across authentication, customer workspace, Planner, analysis engine, administration, error handling, performance, security, accessibility, and mobile.
- 3 new permanent regression tests: query-count checks for History, Journal, and Planning History (never previously benchmarked).

### Findings
- **Category B, not resolved:** no admin visibility exists for customers, analyses, or Planner sessions — the `/operations` Filament panel contains only `LabsFeatureResource`. Flagged for Product Office scoping; building it is new engineering scope outside this commission's boundary.
- **Category C:** no optimistic locking anywhere in the application — restated as product-wide (not Planner-specific, as first found at `U-07.8`), recorded once.
- **Category B, environmental:** genuine browser/device/accessibility-tool verification remains impossible in this environment, unchanged since `U-07.7`/`U-07.8`.
- Zero Category A (release-blocking) defects found.

### Verified
- Workspace screen query counts measured for the first time: History 2, Journal 4, Planning History 3 — all within budget, no N+1. An inflated first measurement (caused by repeated `actingAs()` calls in a single test loop, not a real defect) was caught and corrected before being recorded.
- No mass-assignment gaps (every model declares `$fillable`/`#[Fillable]`); CSRF unmodified; every policy correctly scopes ownership.
- Full regression: 403/403 passing (up from 400). `pint --test` clean.

### Not Done (by design — scope)
`.env.example`'s local-development defaults (`APP_DEBUG=true`, `LOG_LEVEL=debug`) are Laravel's own correct scaffolding, left unchanged — noted instead as an Operational Readiness Checklist item to verify the actual, separate, non-committed production `.env` before deploying. No deployment, monitoring, or backup infrastructure evaluated — outside what this repository's code can establish.

### Recommendation
**READY FOR CONTROLLED BETA**, with the admin-visibility gap and environmental verification limits noted as the two items most worth attention before real customer exposure.

## PO-U08.3-AC-001 — ADR-010 Formalisation: Formal Acceptance & Programme U-08 Constitutional Closure

**Status:** Product Office formal acceptance. Work package **U-08.3** is **COMPLETE and closed**. Programme U-08 (`U-08.1`–`U-08.3`) is constitutionally complete.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U08.3-AC-001`. Noted precisely, not as a correction: "constitutionally complete" refers to the three work packages, all now closed — `U-04.1`'s OI-02/OI-03 and `U-08.1`'s OQ-1 remain genuinely open and non-blocking, unaffected by this acceptance.

## U-08.3 — Architectural Decision Formalisation (ADR-010)

**Status:** Delivered by Architecture Office — documentation only, no code. Commissioned by `PO-U08.3-001`, closing the citation gap identified during `PO-U08.2-AC-001`'s review.

### Added
- `docs/adr/ADR-010-JOURNAL-ENTRY-REFERENCE-BOUNDARY.md` — a standalone, numbered ADR codifying the `JournalEntry` architecture already accepted (`PO-U08.1-AC-001`) and already implemented (`PO-U08.2-AC-001`): immutable `slip_analysis_id` reference, editable `reflection`, no lifecycle, relationship to `SlipAnalysis` and to the Workspace, rationale for immutability.
- A row for `ADR-010` in `docs/adr/ADR-INDEX.md`.

### Changed
- `docs/02-architecture/U-08.1-WORKSPACE-ARCHITECTURE-AND-CAPABILITY-DEFINITION.md` §9: updated from "Proposed ADR" (describing the decision inline, pending its own document) to point to `ADR-010` as the single authoritative record.

### Not Done (by design — scope)
No implementation, migration, test, or UI change. No prior `DECISION_LOG.md` entry edited or rewritten — governance history is append-only. `PO-U08.1-AC-001`/`PO-U08.2-AC-001` remain fully valid and unreopened.

## PO-U08.2-AC-001 — Workspace Backend: Formal Acceptance (with Corrections)

**Status:** Product Office formal acceptance. Work package **U-08.2** is **COMPLETE and closed**. Programme U-08 continues.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U08.2-AC-001`'s acceptance, and separately corrected two citation errors in that same acceptance's Strategic Authority list — `ADR-007`'s actual title is "Analysis Persistence Boundary," not "Planner Architecture" (`ADR-008`'s domain); and `ADR-010` is cited as an existing authority but no such document exists anywhere in the repository — only a proposal inside `U-08.1`'s architecture document, approved in principle but never drafted as its own formally-accepted ADR. The underlying `JournalEntry` architecture is unaffected and correctly implemented; only the citations are corrected.

## U-08.2 — Workspace Implementation: History, Journal, Planning History

**Status:** Delivered by Engineering — customer-facing implementation, no redesign. Implemented directly from `PO-U08.1-AC-001`'s own authorisation; awaiting Product Office review.

### Added
- `journal_entries` migration, `JournalEntry` model/policy/factory, `App\Actions\Journal\CreateJournalEntry`/`UpdateJournalEntry` — `reflection` is editable, `slip_analysis_id` is immutable after creation (absent from the update action's attributes entirely, not just conventionally protected). No delete ability exists, per Product Office's `PO-U08.1-AC-001` §3 direction.
- History (`history.index`), Journal (`journal.index`, `journal.entry`), and Planning History (`planner.history`) Volt components — replacing both `history` and `journal`'s `coming-soon` placeholders. Reuse existing Analysis Cards/Journal Cards/empty-state definitions verbatim.
- A "Planning History" navigation item (desktop nav + mobile drawer).
- 22 new Pest tests: `tests/Feature/Workspace/HistoryTest.php`, `JournalTest.php`, `PlanningHistoryTest.php`.

### Fixed
- `tests/Feature/WorkspaceAccessTest.php`'s "coming soon" assertion, previously hard-coded to the `history` route, updated to `help` — `history`/`journal` are no longer placeholders.

### Verified
- Full regression: 400/400 passing (up from 381). `pint --test` clean.

### Not Done (by design — scope)
`U-04.1`'s OI-02 (Report-screen journal entry point) and OI-03 (Dashboard card interactivity) remain open, untouched, non-blocking.

## PO-U08.1-AC-001 — Workspace Architecture: Formal Acceptance

**Status:** Product Office formal acceptance. Approves the workspace domain architecture, `JournalEntry` model, Planning History, proposed `ADR-010`, and extension strategy. Resolves OQ-1 (Journal Entry deletability) by directing immutable-for-now treatment. Authorises Engineering to proceed with implementation directly from this acceptance.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U08.1-AC-001`'s acceptance and its resolution of OQ-1.

## U-08.1 — Workspace Architecture & Capability Definition

**Status:** Delivered by Architecture Office — architecture definition only, no code. Commissioned by `PO-U08.1-001`, reconciled with `PO-U04-RR-001`; awaiting Product Office review.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U04-RR-001` — Programme U-08 supersedes and continues Programme U-04 (formerly "E-06 — History and Journal"), not a new capability. `U-04.1`'s already-delivered UX storyboard remains fully valid; `PO-CRD-001` §7's deferrals (search, filtering, tags, collections, bookmarks, favourites, archive management, bulk actions, smart organisation) remain in force.

### Added
- `docs/02-architecture/U-08.1-WORKSPACE-ARCHITECTURE-AND-CAPABILITY-DEFINITION.md` — certifies `U-04.1` as architecturally sound without repeating it; resolves `U-04.1`'s OI-01 with a proposed `JournalEntry` domain model (denormalized `user_id`, immutable `slip_analysis_id` reference, mirroring `SlipAnalysis`'s established pattern) and a proposed `ADR-010`; specifies **Planning History** (a read-only list of past Planner sessions, reusing the existing `User::plannerSessions()` relation and the already-built Planner Workspace screen — the one genuinely new scope item beyond `U-04.1`, since Programme U-07 postdates it). Every `PO-CRD-001` §7 deferred capability given extension-point-only treatment.

### Not Done (by design — scope)
No code, migration, or UI. `U-04.1`'s OI-02/OI-03 remain open, non-blocking. One new open question raised (`JournalEntry` deletability), not decided unilaterally.

## PO-U07.8-AC-001 — Planner Validation: Formal Acceptance & Programme U-07 Constitutional Closure (with a Correction)

**Status:** Product Office formal acceptance. Work package **U-07.8** is **COMPLETE and closed**. Programme U-07 (`U-07.5` through `U-07.8`) is declared constitutionally complete.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07.8-AC-001`'s acceptance, and separately corrected Finding B's misattributed quotation — "Planner exports remain deterministic and structurally identical to direct analysis" does not appear in `ADR-008`; that ADR's actual text concerns the read-only evaluation boundary, a narrower claim. The underlying finding (verified by this sprint's own integration test) is unaffected; only the citation is corrected. Does not affect the acceptance.

## U-07.8 — Planner Integration, Validation & Release Readiness

**Status:** Delivered by Engineering — validation and defect correction only, no new capability. Commissioned by `PO-U07.8-001`; awaiting Product Office review and release determination.

### Fixed
- **Category A defect:** deleting a slip referenced by any Planner session — including one long since Abandoned or Exported, not only an open one — threw an uncaught `QueryException` (the database's `restrictOnDelete()` constraint on `source_betting_slip_id`, since a `PlannerSession` row is never deleted regardless of status). Added `BettingSlip::hasPlannerHistory()` (an any-status check, distinct from `isLockedByPlanner()`'s editing-lock check, which correctly excludes terminal sessions for its own purpose but wrongly implied deletion was safe again once a session ended). Used for both the Delete button's visibility in `betting-slips.index` and a defence-in-depth guard inside `deleteSlip()`.

### Added
- `docs/engineering/U-07.8-PLANNER-VALIDATION-AND-RELEASE-READINESS.md` — full validation report across all twelve commissioned areas, defect register, performance report (5 queries per Planner Workspace load), accessibility/browser verification report, and release readiness assessment.
- 6 new Pest tests: the delete-while-has-history fix and its permanence after session end, an exported-slip/Risk-Engine score-agreement integration check, and a user-deletion cascade-ordering check.

### Verified
- Full regression: 381/381 passing (up from 375). `pint --test` clean.
- Exported slip is a Draft (not auto-Ready) and, once marked Ready, analyses to the exact same score/band the Planner last showed.
- Deleting a user with an open Planner session resolves cleanly despite competing cascade/restrict FK paths.

### Not Done (by design — recorded, not fixed)
- Genuine browser/device/screen-reader/contrast verification could not be performed — no such tooling exists in this environment (unchanged limitation from `U-07.7`, stated directly rather than worked around). This is the basis for a **READY WITH MINOR FIXES** recommendation rather than an unqualified one.
- No optimistic locking added against concurrent Planner edits (Category C, no evidence of real-world occurrence).
- The three `U-07.7`-flagged Category D gaps (historical metadata, reason-code copy, revision restore) remain unchanged future enhancements.

## PO-U07.7-AC-001 — Planner Customer Experience: Formal Acceptance & Closure (with a Correction)

**Status:** Product Office formal acceptance. Work package **U-07.7 — Planner Customer Experience Implementation** is **COMPLETE and closed**. `U-07.5`, `U-07.6`, `U-07.7` all now complete. Constitutional ownership returns to Product Office.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07.7-AC-001`'s acceptance, and separately corrected a wording error in that same acceptance's §2 — "Planner entry point from analysed slips" should read "Ready slips." Verified against `StartPlannerSession` (requires `BettingSlipStatus::Ready`, throws otherwise) and `betting-slips.index` (the entry action only renders for Ready slips). `Analysed` is a distinct, later state the Planner never touches. The correction doesn't affect the acceptance, which stands.

## U-07.7 — Planner Customer Experience Implementation

**Status:** Delivered by Engineering — customer-facing implementation of the accepted `U-06.4` storyboard. Commissioned by `PO-U07.7-001`; awaiting Product Office review and acceptance.

### Added
- `planner/{plannerSession}` Volt route and `resources/views/livewire/planner/session.blade.php` — the Planner Workspace: original-slip reference panel, Primary Finding, ranked selections (lock/remove), add-replacement form, Compare Revisions, Confirm, Export, Abandon — all on one persistent screen, calling only the existing, already-accepted Planner actions.
- "Plan this accumulator" entry point beside "Analyze" on Ready slips in `betting-slips.index`, resuming an already-open session rather than duplicating one.
- 14 new Pest tests: `tests/Feature/Planner/PlannerSessionUiTest.php`.

### Fixed
- `betting-slips.builder`'s `returnToDraft()` previously let `BettingSlipLockedByPlannerException` bubble to an uncaught 500 when a customer tried to edit a slip locked by an open Planner session. Now shows the accepted lock message and redirects into the open session.

### Verified
- Full regression: 375/375 passing (up from 361). `pint --test` clean.
- No browser automation tool is available in this environment; stated explicitly rather than implying a visual check that didn't happen. Verified instead via Feature-level HTTP/Livewire tests against the actual rendered output for every screen state (entry, primary finding, lock/remove/add, confirm/export, two-artifact export display, abandon, cross-user authorization, the builder lock redirect).

### Not Done (by design — three honest, scoped gaps, not a redesign)
- Data Quality, Rule Set Information, and Analysis Metadata are not persisted anywhere on `PlannerRegenerationEvent` (confirmed against its migration and `RankLegsByStructuralWeakness`'s actual output) — omitted from the Planner Workspace rather than invented or recomputed live, which `ADR-008` forbids.
- Reason-code deltas have no customer-facing copy register yet (unlike Risk Factor codes) — omitted rather than shown as raw enum values.
- "Return to an earlier revision" (`PD-008`'s OI-1) remains unimplemented — no restore action exists in the domain layer; Compare Revisions is read-only for this delivery, gracefully labelling a since-removed selection rather than fabricating its name.

## PO-U06-RR-002 — Governance Reconciliation: U-06.3 Correction Confirmed

**Status:** Product Office confirms the U-06.3 correction as definitive. `PO-U06.4-AC-001` remains fully valid, unaffected. No rollback, no acceptance withdrawn.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U06-RR-002` — the definitive programme record (`U-06.1` Complete, `U-06.2` Complete, `U-06.3` Outstanding/not yet commissioned, `U-06.4` Complete), and confirmation that Engineering's correction stands as already recorded, requiring no further change.

### Not Done (by design — scope)
No rollback of any prior entry. No withdrawal of `PO-U06.4-AC-001`. `U-06.3` remains outside Programme U-07's active roadmap until separately commissioned.

## PO-U06.4-AC-001 — Planner Customer Experience: Formal Acceptance & Closure (with a Correction)

**Status:** Product Office formal acceptance. Work package **U-06.4 — Planner Customer Experience & Interaction Design** is **COMPLETE and closed**. Constitutional ownership of Programme U-06 returns to Product Office.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U06.4-AC-001`'s acceptance of U-06.4, and separately corrected a factual error in that same acceptance's §16 — its claim that "U-06.3 — Data Availability & Parser Readiness" is complete. No such document or delivery exists anywhere in this repository; `TASKS.md`'s own standing record confirms U-06.3 remains not started and is relevant only if/when Capability B is later authorized (it has not been). This is the identical claim already made once before and correctly declined (`PO-U06-RR-001`, 2026-07-26) — the correction here does not reject or reopen U-06.4's own acceptance, which stands.

## U-06.4 — Planner Customer Experience & Interaction Design

**Status:** Delivered by UX Studio — design only, no code. Commissioned by `PO-U06.4-002`; awaiting Product Office review and acceptance.

### Added
- `docs/05-ux/U-06/U-06.4-PLANNER-CUSTOMER-EXPERIENCE-STORYBOARD.md` — the complete customer-facing Planner design: journey map, screen inventory, navigation flow, per-screen Frame storyboards, interaction catalogue, component inventory, customer copy system, a Planner-specific Copy Guardrails addendum to `TRUST_SIGNALS.md` (notably: the word "Lock" is never customer-facing — replaced with "Keep this selection," to avoid colliding with that document's own forbidden marketing term), trust/emotional-journey mapping, visual hierarchy and version-labelling system (Original / Revision / Current / Confirmed / Exported), empty/loading/error states, mobile/desktop/tablet specifications, accessibility specification, UX rationale, and a governance traceability matrix.
- `EXPLAINABILITY_SYSTEM.md`'s Explanation Hierarchy populated, for the first time, with the now-implemented weakest-leg ranking (MSC/`E-06D.1`) at its Primary Finding stage.

### Verified
- Every screen checked against `PD-007` (no suggested/generated selections), `PD-008` (original slip lock made customer-visible), `PD-009` (export shown as two independent artifacts), and `ADR-009`'s exact five-state lifecycle (no new states — "Evaluating"/"Approved" treated as descriptive only, per `PO-U07.6-CL-001`).
- Every component mapped to an existing `COMPONENT_PRINCIPLES.md` primitive; no new component category, colour, spacing, or animation pattern introduced.

### Not Done (by design — scope)
No mathematics, architecture, backend, or Product Decision changed. `DR-01`, `DR-02`, `DR-04` remain open; the design's dependency on each is stated precisely (§31 of the delivered document) rather than either silently assumed resolved or used to block the whole deliverable — the Compare Revisions view is deliberately customer-pulled only, which keeps the baseline design fully compliant with DR-01's most conservative option without waiting for it to close.

## PO-U07.6-AC-001 — Planner Application Services: Formal Acceptance & Closure

**Status:** Product Office formal acceptance. Work package **U-07.6 — Planner Application Services** is **COMPLETE and closed**. Constitutional ownership returns to Product Office, which will next commission `U-07.7` — Planner Customer Experience.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07.6-AC-001` — Product Office accepts that the commissioned application-service scope was already fully implemented under `U-07.5`, and explicitly recognises Engineering's pause-and-clarify handling of the lifecycle-wording ambiguity as the expected standard for future programmes.
- `TASKS.md`: `U-07.6` marked with its formal acceptance.

## U-07.6 — Planner Application Services: Verification, Not New Implementation

**Status:** Delivered — verification only. `PO-U07.6-CL-001` clarified the handover's lifecycle wording; Engineering determined the requested scope is already satisfied by `U-07.5`. No new code.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07.6-CL-001` (Product Office clarification: "Evaluating"/"Approved" were descriptive, not a request for new `PlannerSessionStatus` states — no `ADR-009` amendment authorised) and Engineering's resulting determination that every scope item `PO-U07.6-001` named is already implemented under `U-07.5`.
- `TASKS.md`: new `U-07.6` section recording the flagged lifecycle mismatch, its clarification, and the verification outcome.

### Verified
- Before writing any code, checked `PO-U07.6-001`'s stated lifecycle (`Draft → Evaluating → Evaluated → Approved → Exported`) against the accepted, frozen `PlannerSessionStatus` enum (`Draft → Evaluated ⇄ Evaluated → Complete → Exported`, plus `Abandoned`) and found a literal mismatch — flagged rather than silently implemented or silently ignored.
- Every scope item named (session orchestration, lifecycle management, revision/regeneration orchestration, approval workflow, export orchestration, Risk Engine interaction) mapped exactly onto already-delivered `U-07.5` actions (`StartPlannerSession`, `EvaluatePlannerSession`, `AddPlannerSelection`, `RemovePlannerSelection`, `CompletePlannerSession`, `ExportPlannerSession`).
- Full suite re-run: 361/361 passing (unchanged). `pint --test` clean. No source files touched.

### Not Done (by design — scope)
No `ADR-009` or `PlannerSessionStatus` change — confirmed not intended by `PO-U07.6-CL-001`. No customer-facing UI (still gated on UX Studio's U-06.4).

## PO-U07.5-AC-001 — Planner Backend Foundation: Formal Acceptance & Closure

**Status:** Product Office formal acceptance. Work package **U-07.5 — Planner Backend Foundation** is **COMPLETE and closed**. This does not constitute acceptance of the complete Planner capability — Programme U-07 remains active.

### Changed
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07.5-AC-001` (formal acceptance of the backend delivery, scoped precisely to the backend foundation milestone) and its requested repository consistency check.
- `TASKS.md`: U-07.5 marked with its formal acceptance; three future work packages named for later commissioning (not yet started, not commissioned by this entry).

### Verified
- Repository consistency check requested by the acceptance: confirmed `DR-01` (Messaging Philosophy), `DR-02` (Regeneration Cycle Limit), and `DR-04` (Session Retention) remain genuinely open in `docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md` — no `RESOLVED` marker on any of the three, unlike `DR-03`/`DR-05`. No drift found between that register and `DECISION_LOG.md`'s prior entries; no repository reconciliation required.

### Not Done (by design — scope)
No new engineering work performed under this acceptance. `U-07.6` (Application Services), `U-07.7` (Customer Experience), `U-07.8` (Integration & End-to-End Validation) are named as the remaining Planner work packages but are not commissioned here.

## PO-U07-AC-001 — Planner Architectural Acceptance & U-07.5 Backend Implementation

**Status:** Delivered — `ADR-008`/`ADR-009` formally Accepted; Planner architecture constitutionally frozen; Engineering commissioned and has delivered the backend/domain layer. No customer-facing UI.

### Changed
- `docs/adr/ADR-008-PLANNER-ENGINE-BOUNDARY.md`, `docs/adr/ADR-009-PLANNER-LIFECYCLE-AND-REGENERATION-BOUNDARY.md`, `docs/adr/ADR-INDEX.md`: status changed from `Proposed` to `Accepted` (Product Office + Architecture Office, `PO-U07-AC-001`, 2026-07-27).
- `docs/00-governance/DECISION_LOG.md`: recorded `PO-U07-AC-001`, scoped precisely — covers the architecture only; `DR-01`/`DR-02`/`DR-04` remain separately open; customer-facing Planner screens still require UX Studio's U-06.4 design phase per `CLAUDE.md`'s Frontend Work Rule and Gap Rule.

### Added
- `planner_sessions`, `planner_selections`, `planner_regeneration_events` migrations and models (`PlannerSession`, `PlannerSelection`, `PlannerRegenerationEvent`), `PlannerSessionStatus`/`PlannerSelectionLockState` enums, `PlannerSessionPolicy`, factories.
- `App\Domain\Risk\Normalization\NormalizePlannerSelections` — a new sibling to the certified `NormalizeBettingSlip`, not a modification of it (Foundation Freeze), since `PlannerSelection` rows carry no `BettingSlip` lifecycle.
- `BettingSlip::isLockedByPlanner()` — a computed fact extending the existing `analysisEligibility()` pattern (E-03B), never a stored flag, per `U-07.4`'s Gap B closure; `returnToDraft()` now throws `BettingSlipLockedByPlannerException` while locked.
- Actions: `StartPlannerSession`, `EvaluatePlannerSession` (orchestrates `NormalizePlannerSelections` → `CalculateStructuralRisk`/`RankLegsByStructuralWeakness`, read-only per `ADR-008`), `AddPlannerSelection`, `RemovePlannerSelection`, `ToggleSelectionLock` (no re-evaluation — lock state doesn't affect the math), `CompletePlannerSession`, `AbandonPlannerSession`, `ExportPlannerSession` (creates a new `BettingSlip` via the existing `SaveBettingSlip` action, PD-009 Option 2).
- 12 new Pest tests: `tests/Feature/Planner/PlannerSessionLifecycleTest.php`.

### Verified
- Pest: 361/361 passing (up from 349). `pint --test` clean.

### Not Done (by design — scope)
No customer-facing Planner UI (Blade/Livewire) — awaits UX Studio's U-06.4 design phase per the Frontend Work Rule/Gap Rule. `DR-01`, `DR-02`, `DR-04` remain open and untouched by this delivery.

## U-07.4 — ADR Reconciliation & Constitutional Alignment

**Status:** Delivered — reconciliation only, no code, no redesign.

### Added
- `docs/02-architecture/U-07.4-ADR-RECONCILIATION-REPORT.md` — reviewed `ADR-008`/`ADR-009` against `PD-007`/`PD-008`. `ADR-008` fully compatible, unchanged. `ADR-009` amended: the source-slip lock (`PD-008`) specified as a computed fact extending the existing `analysisEligibility()` pattern, never a new stored flag; the `DR-02` cycle-count source clarified as the existing `sequence_number`; the export step's prior single-option description replaced with two fully-specified, mutually exclusive options, explicitly left as a Product Office choice rather than decided unilaterally.

### Changed
- `docs/adr/ADR-009-PLANNER-LIFECYCLE-AND-REGENERATION-BOUNDARY.md`: Export section, a new source-slip-lock subsection, a new regeneration-cycle-count clarification, and the Status Note all updated to reflect the reconciliation above. Ready for Product Office acceptance the moment the one remaining export-option choice is made.

### Not Done (by design — scope)
No new Planner capability, no Product Decision changed, no mathematics or Engineering implementation touched, no new Product Decision invented — the export-target fork is presented as a choice between two already-complete options, not a new open-ended decision.

## PD-009 — Planner Export Behaviour

**Status:** Decided — Export Option 2 selected. `ADR-008`/`ADR-009` returned for final Product Office acceptance.

### Changed
- `docs/adr/ADR-009-PLANNER-LIFECYCLE-AND-REGENERATION-BOUNDARY.md`: Export section updated from a two-option fork to a settled decision — Planner export creates a new `BettingSlip` from the session's final selection; the original customer-provided slip is never transitioned out of `Ready` and remains permanently unchanged. Added a Consequences entry noting the original and exported slips can coexist, each independently analysable. Status line and Status Note updated: no open item remains against this ADR; both `ADR-008` and `ADR-009` are returned for Product Office's own final acceptance act, not self-declared Accepted by Architecture Office.
- `docs/00-governance/DECISION_LOG.md`: recorded `PD-009` with its stated rationale (every approved Planner outcome deserves its own independent slip; customers must be able to distinguish original intent from Planner-guided outcome).

### Not Done (by design — scope)
No redesign — this selects one of the two options `U-07.4` already fully specified. No new Product Decision, no Planner capability expansion, no mathematics or Engineering implementation change.

## `PO-U08.1-AC-001` — Rich Indigo, Smoked Graphite, Fixed Atmospheric Layer, Premium Light Sweep

### Added
- Rich Indigo accent (`--accent`/`--accent-strong`) in both themes, replacing the prior blue; `--alert-info` deliberately left untouched.
- Smoked graphite dark-theme neutral scale, replacing the prior blue-tinted scale; `--surface-card`/`--surface-inverse` updated to match.
- Fixed atmospheric layer (`.atmosphere`, 3-5 blurred indigo shapes, `position: fixed`, `pointer-events: none`) on the public layout and the Labs guest branch.
- Premium light sweep (`.light-sweep`) on the homepage hero preview and all 5 public CTA sections.
- `MOTION_SYSTEM.md`, `DESIGN_TOKENS.md`, `VISUAL_INSPIRATION.md` amended; Logo Rotation section's prior "shield glyph" terminology corrected per the memo's own §2.

### Fixed
- A cross-block CSS synchronisation bug: the two intentionally-duplicated dark-mode token blocks had drifted after an edit matched only one — found via direct `getComputedStyle()` verification, fixed, re-verified byte-identical via `diff`.
- A real layering bug: the atmosphere layer was invisible until the content wrapper's own opaque background was removed and `--gradient-page` moved onto `.atmosphere` itself.

### Changed
- Opacity of the atmosphere shapes raised from an initial `0.05`/`0.12` to `0.16`/`0.28` (light/dark) per founder feedback that the first pass read as too dim.

### Not Done
- A follow-up instruction to add a CTA-specific atmosphere variant was retracted before any markup referenced it; the CSS added for it was fully reverted.

4 new Pest tests. Full regression: 501/501 passing (up from 497). `pint --test` clean; `npm run build` clean.

## Auth Screens — Atmosphere, Theme-Aware Logo, Back-to-Home Link, Google/Apple Placeholders

### Added
- `layouts/guest.blade.php` (login/register/forgot-password) now carries a 3-shape atmosphere variant, overriding a prior explicit exclusion decision — founder direct instruction.
- The guest-layout logo now maps to the active theme (pre-existing, previously-unused `slipguard-logo-{light,dark}-transparent.svg` pair), via the same `isDark` + `slipguard-theme-changed` pattern already built for the public header.
- An explicit "← Back to home" link added beneath the logo.
- Disabled Google/Apple sign-in placeholders (`<x-social-auth-buttons>`) added to `login.blade.php` and `register.blade.php` — genuinely disabled (no `laravel/socialite` package or OAuth credentials exist yet), not a route to a non-existent controller.

4 new Pest tests. Full regression: 503/503 passing (up from 501). `pint --test` clean; `npm run build` clean.

## Fixed Atmospheric Layer & Theme-Aware Logo — Extended to the Entire Authenticated Portal

### Changed
- Founder instruction widened twice: "in the dashboard" (implemented first as a route-scoped exception in `layouts/app.blade.php`) → corrected to "all the pages in the portal/dashboard" — the atmosphere is now unconditional across every authenticated screen (`layouts/app.blade.php`) and the authenticated branch of `layouts/labs.blade.php` (previously guest-only). The internal Filament Operations panel is a separate rendering stack, never in scope.
- `MOTION_SYSTEM.md`, `DESIGN_TOKENS.md`, `VISUAL_INSPIRATION.md` corrected a second time to record the final full-portal scope.

1 Pest test updated. Full regression: 504/504 passing; `pint --test` clean; `npm run build` clean.

## Sitewide Search (Command Palette) & Language Selector

### Added
- `<x-search-trigger-button>`, `<x-language-selector>`, and `<x-command-palette>` — three new shared components. The palette reuses the existing `<x-modal>` shell, adds client-side filtering, arrow-key navigation, Enter-to-open, and a global Cmd/Ctrl+K shortcut, populated with SlipGuard's own real routes (not the OddStorm reference screenshot's betting-market content — only its interaction pattern was reused, per `VISUAL_INSPIRATION.md`'s existing reference-brand rule).
- `<x-modal>` gained an opt-in `centered` prop (default `false`, every other existing modal usage unaffected) for centering the palette on screen.
- Wired into the public header and the authenticated portal header (desktop nav + mobile drawer).

### Fixed
- The public header's `backdrop-blur-md` created a new CSS containing block for the modal's `position: fixed`, confining it to the header's own small box instead of the full viewport — fixed by moving `<x-command-palette>` to a sibling of `<header>` rather than a descendant.
- The modal's backdrop and content panel are DOM siblings with no explicit `z-index` — the backdrop was winning paint order and intercepting every click meant for the panel (confirmed via `elementFromPoint`). Fixed with an explicit `relative z-10` on the panel inside the shared `<x-modal>` component.

### Changed
- Search and language were removed again from `layouts/guest.blade.php` specifically, per a final founder correction — kept on the public site and the authenticated portal only.

### Not Done
- Dedicated Pest coverage for this feature (component presence, palette item correctness, auth-screen absence) is still owed as a follow-up.

Full regression: 504/504 passing throughout; `pint --test` clean; `npm run build` clean at each step.

## Sitewide Fade-In-Up Scroll Reveal

### Added
- `resources/js/app.js` (previously empty) now exports `initScrollReveal()`, bound to Livewire's `livewire:navigated` event (fires on first load and every `wire:navigate` transition) — a single shared implementation of the pre-existing `U-15.2` homepage-only reveal pattern (opacity 0→1, translate-y 8px→0, once, `prefers-reduced-motion`-aware).
- `data-reveal` added to every other public page's non-hero, non-CTA sections (About, Analyse, Planner, Reports, Pricing, FAQ, Release Notes), matching the homepage's own original hero/CTA-exclusion precedent.
- `MOTION_SYSTEM.md` amended to record the sitewide scope and shared-file architecture.

### Removed
- The inline `<script>` reveal implementation previously duplicated only in `home.blade.php`.

1 new Pest test. Full regression: 505/505 passing (up from 504); `pint --test` clean; `npm run build` clean.

## Theme Consistency Investigation — Auth Screens

### Not Done
- Investigated a founder concern that a user might leave a light theme and encounter a dark auth screen. Tested every plausible scenario via Playwright (fresh session, explicit override against an opposing system preference, real `wire:navigate` link clicks) — no mismatch reproduced in any case. No code change made; recorded as investigated-not-reproduced rather than silently dropped.

## Theme Toggle Removed from Auth Screens; Pre-Hydration Icon-Sizing Bug

### Changed
- `<x-theme-toggle />` removed from `layouts/guest.blade.php` — the layout still reflects the globally active theme (unchanged `isDark` logo-swap logic), a customer just can't change it from that screen anymore.

### Fixed
- The public header's rotating icon flashed at its native ~721×848px SVG size for a brief pre-hydration window before snapping down to its intended `h-10`/`h-8` size — the only height constraint was an Alpine `:class` binding, inert until hydration. Fixed with a static `h-10` fallback in the element's own class list. Verified with JavaScript fully disabled.

1 new Pest test; 1 existing test corrected. Full regression: 505/505 passing.

## `PO-U15.3-001` — Homepage "Invisible Risk" & "How SlipGuard Thinks"

### Added
- Replaced the homepage's "Problem" and "Solution" sections with "Invisible Risk" (an illustrated Sample Accumulator, one leg marked "Weakest Leg") and "How SlipGuard Thinks" (a 5-stage pipeline: Your Slip → Normalisation → Deterministic Rules → Explainability → Risk Report, with a staggered scroll reveal).

### Fixed / Corrected
- The commissioning memo's own §6 claimed alignment with a "recently approved U-07 Evidence Acquisition Strategy" and an "Evidence Warehouse" architecture — verified directly against this repository's real ADRs/tasks and found fabricated (`U-07` is Planner Orchestration; no such architecture concept exists). The pipeline instead uses this codebase's own real, implemented concepts.

2 existing Pest tests updated for the new copy/order. Full regression: 506/506 passing.

## Atmosphere-Layer Rhythm Sitewide

### Changed
- Every public page's non-hero, non-CTA sections now alternate between a translucent "atmosphere" beat and a "quiet" (`bg-surface-page/95`) beat, replacing flat `bg-neutral-50` sections and hard `border-t` cuts on 7 pages that had never received the `U-16.2` gradient-continuity treatment.

### Fixed
- A first pass using a fully opaque "quiet" beat caused the fixed, screen-pinned atmosphere blobs to visibly flicker in and out as opaque sections scrolled past their fixed position (founder-reported: "the atmosphere layer isn't stable... it disappears somewhat"). Fixed by making the "quiet" beat translucent too, so both beat types let the fixed layer show through at different intensities rather than one hiding it outright.

Full regression: 506/506 passing.

## Favicon

### Added
- A real favicon rendered from the approved SlipGuard brand icon (16/32/48/180/192px PNGs + a hand-assembled `favicon.ico`), replacing Laravel's generic default. New shared `partials/favicon-links.blade.php`, included in all four layouts.

### Fixed
- The source SVG composites two separately-embedded raster layers via `<use>` — an initial naive extraction of the first embedded image alone produced an incomplete half-shape; corrected by rendering the full SVG in a real browser and screenshotting the composited result.

1 new Pest test. Full regression: 507/507 passing.

## `PO-U17.0-001` — DW-01 Canonical Demo Workspace (First Slice)

### Added
- `is_demo` column on `users`; canonical demo identity `demo@slipguard.local` / "Tunde Adeyemi".
- `php artisan slipguard:demo` (`--fresh`, `--summary`, `--force`) — idempotent, environment-gated, generates-and-prints-once demo credentials.
- 30 betting slips built from real taxonomy-recognised football markets, run through the actual `AnalyzeBettingSlip` action (never a hand-inserted score), spread across roughly four months; 12 realistic, compliant journal entries attached to real analyses.

### Not Done
- Explicitly scoped to a first slice, not the full commission: Planner sessions/planning history, the full 120-180 record volume, public-site showcase integration, profile/preference population beyond defaults, intake-method examples, Compliance Office review, the Mandatory Specialist Design Capability Invocation, and the full structured completion report are all deferred as named follow-up work, not silently dropped.

### Known Limitation
- Every seeded analysis's dominant contributing factor comes back as Combined Odds — found and disclosed directly; an attempted fix was reverted after it produced no measurable improvement.

4 new Pest tests. Full regression: 511/511 passing.

## Sticky Portal Header with Scroll-Linked Icon Rotation

### Added
- The authenticated portal header is now sticky with the same glassmorphism and scroll-linked icon rotation as the public header; its logo was restructured into the same icon/wordmark split (rotating icon + static text).

### Fixed
- A wrapping `<div class="print:hidden">` in `layouts/app.blade.php` broke `position: sticky` on the nav inside it — moved `print:hidden` onto the nav directly and removed the wrapper.

3 existing Pest tests updated. Full regression: 511/511 passing.

## `PO-U15.4-001` — Homepage Hero Reconstruction

### Added
- The Hero's illustrated sample-report card replaced with a real, theme-aware Demo Workspace dashboard screenshot in a full-width showcase row beneath a restructured two-column copy/actions row (Linear-inspired layout).
- `public/images/homepage/slipguard-dashboard-demo-{light,dark}.{png,webp}` — captured directly from the real DW-01 demo workspace.
- Pure-CSS theme-aware image switching (no JS `src` swap, no pre-hydration flash window); a one-time reveal motion on scroll-into-view.

### Removed
- The Hero's illustrated risk-report card and its unique markup/copy (verified not reused elsewhere before deletion).

### Not Done
- Continuous scroll-linked parallax was evaluated (via UI UX Pro Max) and explicitly rejected as inappropriate for real product content, not decorative layers.

3 Pest tests updated/added. Full regression: 511/511 passing.

## `PO-U15.4R-001` — Hero Composition Refinement

### Changed
- Primary CTA enlarged; secondary CTA converted to a bordered outline button. Right column realigned to a top offset near the headline/description transition rather than bottom-of-row.
- Dashboard row widened relative to the copy row (via copy-row padding, not a second container class). Hero vertical spacing tightened throughout.
- A real negative-margin overlap now bleeds the dashboard into the following "Invisible Risk" section (`relative z-10` so it paints above that section rather than being covered).

### Fixed
- Dark-theme dashboard separation: a soft Rich-Indigo glow and lighter border in dark mode only, using the existing dual dark-mode CSS token pattern.

### Not Done
- A supporting-statement line this commission asked to reinstate was left removed, per the founder's own more recent, explicit instruction (confirmed via clarifying question rather than assumed).

Full regression: 511/511 passing.

## `PO-U15.5-001` — Invisible Risk Interactive Demonstration

### Added
- Real weakest-leg contribution and structural-score comparison (53 High → 25 Moderate, ~53% contribution) computed once via the accepted Marginal Structural Contribution model against the section's own existing sample selections — not invented figures.
- A visible customer-control statement ("SlipGuard shows the structural effect. You decide what to do.").

### Changed
- Right column widened (6/6 → 5/7 split) to accommodate the richer demonstration.

1 new Pest test. Full regression: 512/512 passing.

## `PO-U15.6-001` — Product Intelligence Journey & Deterministic Proof Experience

### Added
- `config/slipguard-product-facts.php` — single maintained source of truth for the homepage Proof section's metrics (rule-set version, automated test count, structural factor count, etc.), replacing hardcoded values.
- `metric-card.blade.php`'s new optional `explanation` prop — an always-visible (never hover-only) plain-language explanation under every Proof-section metric and every "How SlipGuard Thinks" pipeline stage.
- An authenticity statement under the pipeline: "This is the same deterministic process used by SlipGuard's analysis engine."
- "How It Works" rebuilt into one continuous card reusing the Invisible Risk section's own real 5-leg example and real computed numbers, replacing three disconnected text columns.

### Fixed
- The homepage's automated-test-count metric was stale (hardcoded `482` against an actual `512`) — now reads from the new config source of truth.

### Not Done
- A fabricated "AO-U07.1-001 — Evidence Acquisition & Intelligence Data Strategy" architecture claim (with an invented "Evidence Warehouse" pipeline) was verified against `docs/adr/ADR-INDEX.md` and rejected — the 2nd occurrence of this exact fabrication pattern this session.

1 new Pest test. Full regression: 512/512 passing.

## Bounded-scope Slip Intake — Paste Text, PDF Upload, Screenshot Upload

### Added
- `App\Domain\BettingSlip\Intake\ParseSlipText` — a deterministic, rule-based (no ML/fuzzy matching) parser that converts pasted or PDF-extracted text into normalised leg data (event name, market, odds), reusing the real `FootballMarketTaxonomyV1` alias lists for market detection. Anything not confidently detected is left blank rather than guessed.
- `App\Domain\BettingSlip\Intake\ExtractPdfText` — real selectable-text extraction via `smalot/pdfparser` (pure-PHP, MIT, no system binary or API key); a scanned/image-only PDF honestly reports "no text could be extracted" instead of failing silently or claiming OCR.
- Screenshot Upload: images are stored on the private `local` disk and served only through a new ownership-checked route (`analyze.screenshot` / `BettingSlipScreenshotController`) — never a public URL.
- The Builder (`analyze.edit`) now conditionally displays the uploaded screenshot beside the manual entry form when a slip has one.
- `betting_slips.source_screenshot_path` (nullable) migration.
- 11 new unit tests (`ParseSlipTextTest`) covering event/market/odds detection, the "2.5 Goals" odds false-positive guard, and the "nothing guessed" contract; `BettingSlipIntakeTest` rewritten with 9 real end-to-end feature tests (paste, PDF success/failure, screenshot upload, screenshot route ownership).

### Changed
- All three new intake methods create a Draft `BettingSlip` via the same `SaveBettingSlip` action manual entry already uses and redirect to the same canonical Builder review screen — no separate analysis pipeline per intake method.
- Bet Code intake is unchanged — still an honest "not yet available" deferred state.

### Fixed
- `x-file-drop`'s `wire:model` binding was applied to the wrapping `<label>` instead of the actual `<input type="file">`, so no file upload through it could ever have worked.
- A CSS token typo, `text-alert-danger` (doesn't exist) → `text-alert-error` (the real token).
- `BettingSlipScreenshotController`'s `$this->authorize()` call had no trait providing it — added `AuthorizesRequests` to the shared base `Controller` (the first plain Controller in the app needing authorization; Livewire components already have it built in).

### Not Done
- Bet-code/share-link retrieval remains deferred, unchanged, per explicit instruction.
- A pre-existing mobile dark-theme gap (the top hero/header gradient band on every authenticated page stays light-themed regardless of theme) was found during browser verification, confirmed independent of this work, and logged as `INC-2026-003` rather than fixed under this bounded scope.

11 new unit tests + 9 new/rewritten feature tests. Full regression: 529/529 passing.

## `PO-U07.X.1-001` — Planner Premium Workspace & Experience Modernisation

### Added
- A premium Planner Workspace summary header (revision, structural score, risk band, selections/kept counts, rule-set version, "Original slip: Preserved", last recalculated) — real persisted values only.
- `config/slipguard-planner-capabilities.php` and a new "Analysis Inputs & Platform Readiness" panel — clearly separates capabilities genuinely active today from named future product direction (fixture/competition/team/historical/market intelligence, provider evidence, the Evidence Warehouse), each explicitly labelled "Planned" and never described with wording that implies it already ran.
- `App\Domain\Planner\Presentation\DerivePlannerTimeline` and a new Session Timeline — a truthful, read-only history derived entirely from persisted revision data (never a new audit subsystem), showing session start, each revision's real added/removed/kept diff, and the current confirmed/exported state.
- A "What SlipGuard just did" milestone disclosure on the Revision card — real backend-mapped labels, no fake per-step delays.
- A customer-control statement on the Primary Finding card: "SlipGuard explains the structural effect. You decide what to keep, review or remove."
- A calmer, non-celebratory Exported completion state (checkmark, "Planning complete", clearer preserved/created language).

### Changed
- Confirm/Export/Add-selection buttons now reuse `<x-primary-button>`/`<x-secondary-button>`, replacing a hand-rolled flat-fill button style that predated the existing gradient-button standard.
- "Your other selections" rows stack label above actions on narrow viewports instead of truncating the selection detail.

### Fixed
- A copy bug ("...would move from Low to Low" when removal wouldn't actually change the band) — a new `bandChangeSentence()` helper suppresses the sentence when the band is unchanged.
- A dark-theme contrast issue on the new Session Timeline — it initially had no opaque card background, so the fixed atmospheric layer washed out its text; wrapped in the same card treatment every other section already uses.

### Not Done
- A separate, pre-existing, sitewide theming bug was precisely diagnosed but not fixed (out of scope for a Planner-only commission): `wire:navigate` transitions don't reliably reapply `data-theme` from `localStorage`. Recorded as a root-cause refinement of `INC-2026-003`.

14 new Pest tests, 1 existing test corrected for intentional copy changes. Full regression: 538/538 passing.

## `U-17` Implementation Phase 1 — Engineering Foundation Package

### Added
- `App\Domain\MarketIntelligence` — a new bounded context for Capability B (market-wide accumulator construction): `EvidenceQuality` enum, `CanonicalFixture`/`CanonicalMarketQuote` value objects, `MapOddsApiMarket` (provider-key-to-taxonomy mapping), `OddsApiProvider` (The Odds API adapter — the first outbound HTTP integration in this codebase), `AcquireMarketEvidence` (bulk-first, per-event-reduced acquisition with a request-credit budget guard).
- Two new tables (`market_intelligence_fixtures`, `market_intelligence_market_quotes`) — a small operational evidence store, no customer data, short-lived cache expiry.
- `config/slipguard-market-intelligence.php`, a `the_odds_api` entry in `config/services.php`.
- `php artisan market-intelligence:validate` — an internal-only console command for manually verifying real provider acquisition; refuses to run unless `MARKET_WIDE_PLANNER_ENABLED` is explicitly true.

### Fixed
- The provider adapter initially issued two bulk requests (fixtures, then quotes) against the same endpoint when one response already contains both — caught by the test suite's own HTTP-request-count assertions, consolidated into a single `bulkAcquire()` call.

### Not Done
- No customer-facing route or UI — deliberately Phase 1 of 3 (provider foundation only). `U-17.6`'s deterministic construction rules and `U-17.7`'s customer experience remain separate, not-yet-started implementation phases.
- `MARKET_WIDE_PLANNER_ENABLED` remains `false` by default — no public exposure, per `U-17.4`'s compliance constraint.

11 new Pest tests. Full regression: 549/549 passing.

## `PO-U02-DASH-002` — Dashboard Decision Workspace Evolution, Phase One

### Added
- **Continue Working** section: surfaces the single most recently touched piece of real unfinished work — an editable betting slip or a non-terminal (Draft/Evaluated) Planner session, whichever is newer. Never offers a slip already locked by an open Planner session.
- **Needs Your Attention** section: surfaces a Planner session in Complete status — a decision made but not yet exported into a slip — with its latest risk band.
- Unnamed slips in Recent Activity (renamed from Recent Analyses) now show their real leg count and recognized market families (e.g. "2 selections · Match Result, Total Goals") instead of a bare "Untitled slip", using `LegAnalysis.market_family` data the Risk Engine already computes.

### Changed
- Progress section dropped "Completed this week" (didn't answer any decision question, per the redesign direction's own Metrics Philosophy test) — now shows Total analyses and Last analysis only.
- The Hero's separate "Continue previous slip" button and the standalone conditional Planner section were folded into the new Continue Working / Needs Your Attention sections above, removing the duplication between them.

### Not Done
- Contextual fixture/competition-based slip naming (e.g. "Premier League Weekend Slip") — the redesign direction's own request, but SlipGuard's data model has no fixture/team data anywhere (Capability A only ever captures market/selection/odds text). Implementing it would mean fabricating information SlipGuard doesn't have; scoped out rather than approximated.
- Interaction polish, hover states, and micro-copy refinement (the redesign direction's own Phase Two/Three) — sequenced after this delivery, not attempted here.
- Browser verification — no browser access in this execution environment; verified via the full Pest suite and Pint instead.

7 new/modified Pest tests. Full regression: 555/555 passing.

## `U-17.8` — Builder Implementation Scoping, `U-17.6A` Outcome Selection Clarification, and Increment 1: Deterministic Construction Engine

### Added
- `ADR-013` (Capability B ↔ Planner integration boundary) flipped from Proposed to Accepted, at the point implementation began depending on it.
- **`U-17.6A` — Deterministic Outcome Selection Clarification**: a genuine gap found while implementing the accepted `U-17.6` construction framework — it never specified which of a market's mutually-exclusive outcomes (Home/Draw/Away, Over/Under, Yes/No) a constructed candidate includes, and every deterministic signal available either reduces to an odds-based tiebreak (prediction in disguise) or can't distinguish between a market's own outcomes. Resolved as **Customer Outcome Sovereignty**: the Builder discovers and ranks eligible fixture+market opportunities; the customer chooses the outcome; the Builder evaluates the completed candidate. Adopted as a permanent constitutional principle (`PO-U17.6A-AC-001`).
- `app/Domain/MarketIntelligence/Construction/` — the deterministic construction engine: eligibility gates (`CBE-001`-`009`), cross-bookmaker quote selection, outcome-to-taxonomy mapping, slot ranking, whole-slip risk/odds-target evaluation with bounded replacement, all built against the `U-17.6A` slot/choice/evaluate sequencing.
- `app/Actions/MarketIntelligence/BuildAccumulatorCandidate` (evidence-aware opportunity discovery) and `CreateCandidateBettingSlip` (the one point Capability B ever touches Programme U-07 — converts an accepted candidate into a real `BettingSlip` + `PlannerSession` via the completely unmodified `SaveBettingSlip`/`StartPlannerSession`).
- Two new model factories for the Phase 1 evidence models, needed for real, DB-backed construction tests.

### Not Done
- No customer-facing UI — this is Increment 1 (engine only) of 2. Increment 2 (the Builder Livewire experience, wired to this engine, including a UX Studio amendment for the per-slot outcome-choice presentation) remains a separate future commission.
- `CBE-003` (Fixture Status) always passes — Phase 1's evidence acquisition never captured fixture status at all; a disclosed limitation, not a guessed check.

51 new Pest tests. Full regression: 595/595 passing.

## `PO-U17-IMP2-001` — Increment Two: Customer-Facing Builder Experience

### Added
- `resources/views/livewire/market-intelligence/builder.blade.php` — the complete Builder: planning-brief form, opportunity review (per-slot outcome radios), evaluation, candidate-ready, no-valid-candidate, replacement-needed, and changed states, all wired to Increment 1's engine.
- An entry-point card on the Analyze Slip index and a new `builder` route, both hidden unless `MARKET_WIDE_PLANNER_ENABLED` is true (still `false` by default).
- `ReplacementNeeded` gained a `reason` field so the UI can distinguish a risk-ceiling breach from a missed odds target.

### Changed
- **Capability B renamed** "Plan an Accumulator" → **"Build an Accumulator"**, and Capability A's existing per-slip action renamed "Plan this accumulator" → **"Improve This Slip"** (or **"Continue Planning"** when a session is already open) — a real naming collision found once both features landed on the same page, resolved by the founder (`PO-U17-NAMING-001`).

### Not Done
- Browser verification — no browser access in this execution environment; verified via a real, seeded, end-to-end component test suite instead (discovery → outcome selection → evaluation → acceptance → real Planner session).

7 new Pest tests. Full regression: 603/603 passing.

## `PO-U21-001` — Repository-Wide Product Content Reconciliation

**Status:** Delivered. Reconciliation only — no UI/UX redesign, per the directive's own explicit non-scope.

### Fixed
- `resources/views/pages/home.blade.php`, `pages/analyse.blade.php`, `pages/pricing.blade.php` — the "manually, for now" / "no statistical knowledge required" intake copy was stale since Paste Text/PDF/Screenshot upload shipped (see "Bounded-scope Slip Intake," above); corrected to name the real intake methods. `home.blade.php`'s "How SlipGuard Thinks" pipeline copy claimed the Risk Report shows "the weakest leg" — the base Risk Report this pipeline describes actually surfaces the Main Contributing Factor (a risk-factor concept, `resources/views/livewire/betting-slips/report.blade.php`), not a per-leg ranking; the separate weakest-leg ranking engine (`RankLegsByStructuralWeakness`, `U-06.2A`/`E-06D.1`) is real but wired into the Planner, not the base Risk Report. Corrected to "the main contributing factor" to match what this specific pipeline actually produces. `pricing.blade.php`'s Free-tier description listed features without Build an Accumulator, which had since shipped under MVP.
- Capability B's post-rename label ("Build an Accumulator," set at `PO-U17-IMP2-001` above) had not propagated everywhere: `resources/views/livewire/layout/sidebar-navigation.blade.php` (command palette entry and primary nav item both still read "Build Accumulator") and `resources/views/partials/authenticated-shell.blade.php` (mobile header title) corrected to match; `home.blade.php`'s unlinked-capabilities list gained the missing "Build an Accumulator" entry.
- `resources/views/pages/release-notes.blade.php` — five shipped milestones had no release-notes entry at all (v2026.5 Bounded-scope Slip Intake, v2026.6 Build an Accumulator, v2026.7 Dashboard/Planner evolution, v2026.8 Manual Intake guided controls, v2026.9 real Terms/Privacy pages) — added, each with a real "why it matters"/"technical" pair checked against the corresponding delivery's own CHANGELOG entry, not invented.

### Changed
- `tests/Feature/MobileNavigationDrawerTest.php`, `tests/Feature/MarketIntelligence/BuilderComponentTest.php` — assertions updated to expect "Build an Accumulator" instead of the stale label.

### Not Done
- Broader `docs/01-product/` staleness beyond what this pass's own scope covered (later named explicitly and partially addressed under `PO-U23-001A`, below) was out of this reconciliation's page-content focus.

2 existing Pest tests updated for the corrected label (`MobileNavigationDrawerTest`, `BuilderComponentTest`). Full regression: 603/603 passing (no net test count change — content/copy corrections only).
## `PO-RC1-007` — Homepage Reconciliation & Limited Experience Polish

**Status:** Delivered. A bounded content/clarity pass, explicitly not a homepage redesign — deferred items named in the directive (further hero iteration, additional sections, new components) were not touched.

### Changed
- `resources/views/pages/home.blade.php` — hero supporting copy tightened (removed a run-on clause, led with the plain-language outcome: "explains what's driving its risk, in plain language"). The three capability cards below the fold rewritten from feature labels to outcome-led headings ("Analyse" → "See what's driving your risk"; "Reports" → "See what you'll actually get"; "Planner" → "Stay in control while you plan"), per the Mandatory Specialist Design Capability Invocation's outcome-over-feature guidance (UI UX Pro Max consulted; 21st.dev's card-copy patterns reviewed and not adopted — none fit a Blade/Livewire, non-React implementation without drifting toward decorative SaaS patterns `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` rejects). A new sentence added to the Trust section stating SlipGuard's operator independence in plain language.
- `docs/05-ux/TRUST_SIGNALS.md` — "Operator independence" added as a named Trust Mechanism (Gap Rule compliance: the mechanism was already real and Locked via `ADR-012`, but had never been documented in this list or stated on the homepage before this directive commissioned it).

### Not Done
- A dashboard-preview caption change was drafted, then reverted after viewing `public/images/homepage/slipguard-dashboard-demo-{light,dark}.png` directly and finding the screenshot itself predates the Dashboard redesign and the Capability B naming fix — changing only the caption would have made it describe the image less accurately, not more. Flagged rather than silently shipped; the screenshot itself needs recapturing, which is outside a copy-only commission.
- No new Pest coverage — this pass changed marketing copy and one documentation list, not behaviour; verified by direct content review against `TRUST_SIGNALS.md`, `ADR-012`, and the real screenshot file rather than automated assertions.

Full regression: 603/603 passing (unchanged — no test-observable behaviour changed).
## `PO-U22-001` — Contact Page Completion

**Status:** Delivered.

### Added
- `resources/views/pages/contact.blade.php` — a full Volt component replacing the honest "Contact is on the way" stub: hero, three contact-option cards (General Enquiries, Technical Support, Billing), a validated contact form, a grouped Office & Hours section with an explicit placeholder notice pending real business details, a styled map placeholder, an FAQ (every answer checked against real current product behaviour before writing it), Support Expectations (response-time ranges), Social Links, and a Security Notice, closing with a footer CTA.
- `app/Models/ContactMessage.php`, `database/migrations/2026_08_07_000001_create_contact_messages_table.php` (`full_name`, `email`, `subject`, `category`, `message`), `database/factories/ContactMessageFactory.php`.
- `App\Domain\Contact\ContactMessageCategory` enum (General Enquiry / Technical Support / Billing / Partnership / Feedback / Bug Report / Other).
- `App\Actions\Contact\SubmitContactMessage` — persists the validated submission. No Mailable exists anywhere in this codebase (`MAIL_MAILER=log`) and provisioning email notification was not part of this directive's scope, so this action persists only — an honest limitation, not a silent gap; closed by `PO-U22-001A`'s Filament triage resource (below).
- Real official brand SVG path data for Instagram, Facebook, TikTok, and Threads added to `resources/views/components/social-icon.blade.php` (Simple Icons project, MIT-licensed, fetched directly rather than hand-drawn, to avoid shipping an inaccurate glyph).
- `resources/views/components/public-footer.blade.php` — all seven social profile URLs populated (`x`, `youtube`, `linkedin`, `instagram`, `facebook`, `tiktok`, `threads`), each confirmed real by Product Office under this directive, username `slipguardhq` on every platform. Previously all three then-existing entries carried `url: null` pending confirmation.
- `tests/Feature/ContactPageTest.php` (6 tests): guest accessibility and placeholder-honesty, FAQ accuracy, all seven social links render live, valid-submission persistence, required-field/invalid-email rejection, category restricted to the documented enum values.

### Fixed
- `Volt::route('contact', 'pages.contact')` 500'd with `ComponentNotFoundException` — `resources/views/pages` is mounted as its own Volt root by `VoltServiceProvider` (the same way `resources/views/livewire` is), so a `pages.` prefix resolved to the nonexistent `pages/pages/contact.blade.php`. Fixed to `Volt::route('contact', 'contact')`.

### Removed
- `resources/views/pages/public-coming-soon.blade.php` — deleted once Contact (this directive), Privacy, and Terms (`PO-CO-002`, tracked separately) all stopped using it, leaving it fully orphaned.

### Changed
- `tests/Feature/PublicPagesTest.php` — the combined stub test ("Pricing honestly communicates availability... Contact, Privacy, and Terms remain honest stubs") was split: Pricing's own assertion stands alone (Pricing itself remains a stub — no pricing model is approved yet); a new "Contact is a real page, not the honest stub" test replaces this directive's share of the removed assertion. (Privacy/Terms' own real-page assertions in the same file belong to the separately-tracked `PO-CO-002`/`CO-MVP-001`, not this directive.)

### Not Done
- Real business details (registered office, phone line, company registration) remain an explicit, visibly-marked placeholder pending Product Office confirmation — not fabricated. Email notification on new submissions was not built (see `SubmitContactMessage`, above) — closed operationally by `PO-U22-001A`'s Filament resource instead of a Mailable.

7 new Pest tests (6 in `ContactPageTest.php`, 1 in `PublicPagesTest.php`) + 1 existing `PublicPagesTest.php` test narrowed to its remaining Pricing-only scope. Full regression: 610/610 passing (up from 603).

## `PO-U22-001A` — Contact Messages Operations Resource

**Status:** Delivered. Closes `PO-U22-001`'s own disclosed gap (staff had no way to see or triage a submitted contact message beyond the database).

### Added
- `App\Domain\Contact\ContactMessageStatus` enum — New / Read / Resolved, the minimal workflow the directive itself specifies (no richer vocabulary invented).
- `status` column on `contact_messages` (default `new`; every pre-existing row defaults to `new`, accurate since no admin resource existed to read one yet).
- Filament `ContactMessages` resource (`app/Filament/Resources/ContactMessages/`) — list and view only, no `EditAction` (staff triage a customer's submission, never rewrite it). Opening a New message marks it Read automatically, mirroring an ordinary inbox; explicit "Mark Resolved" / "Reopen" header actions handle the rest of the workflow.
- Gated on `is_internal` alone, not `CustomerResource`'s narrower `can_manage_customer_data` — a contact message is a general inbound enquiry, not customer analysis data, so any internal staff member may triage it. Explicitly tested as a real authorization boundary decision, not assumed.
- 9 new Pest tests (`tests/Feature/Operations/ContactMessageResourceTest.php`): guest/customer/internal access, submission-to-triage visibility, the auto-Read transition, Mark Resolved/Reopen.

### Fixed
- A real defect found during post-commit verification (`/usr/local/bin/php artisan test`, not caught by the commit's own test run): `ContactMessage::status` was never registered in the model's `casts()` or `$fillable`. Every read hit a `TypeError` against the resource's enum-typed Filament closures (3 failing tests, all genuine 500s — the resource was unusable, not a test-only gap), and every status-change `update()` call was silently discarded by mass-assignment protection. Fixed by adding `status` to both.

### Not Done
- Email/Slack notification on new submissions remains out of scope — this directive closes the "staff can't see it" gap operationally via the Filament resource, not via a Mailable (none exists anywhere in this codebase; `MAIL_MAILER=log`).

Full regression: 760 tests (753 passed, 7 pre-existing self-skipped MySQL-integration tests, 0 failed), verified directly via `/usr/local/bin/php artisan test`.

## `PO-U23-001` — Conversational Entry Layer for Build an Accumulator

**Status:** Delivered.

### Added
- `App\Domain\AccumulatorConversation` — a deterministic (never AI-model-authoritative) intent interpreter: `AccumulatorIntentInterpreter` (interface) / `DeterministicAccumulatorIntentInterpreter` (implementation), `RequestClass`, `ExplicitSelectionDraft`, `InterpretationResult`. Bound behind the interface in `AppServiceProvider` (Decision 1: the rest of the app depends only on the interface, never a provider SDK directly — no live AI provider is integrated anywhere in this codebase; substituting one later is a one-line binding change).
- `App\Actions\AccumulatorConversation\CreateDraftFromExplicitSelection`.
- `resources/views/livewire/accumulator-conversation/composer.blade.php`, mounted on the dashboard as a third, distinct accumulator-building method alongside the existing Guided and Manual Builders — its own module and component, not a variant of the existing intake card.
- Builder hand-off: `market-intelligence/builder.blade.php` gained a `mount()` that applies a session-flashed brief seed (set by the composer, never trusted beyond what the Builder's own existing `planningRules()` validates a moment later) onto the same public properties the manual planning-brief form already binds to, then runs the completely unmodified `findCandidate()` so the customer lands directly on discovered candidates. A normal page visit with nothing flashed behaves exactly as before. A success alert ("Your first draft is ready.") surfaces the conversational summary when present.
- 27 new Pest tests: 14 for the deterministic interpreter, 12 for the composer component, 1 regression test for the bug below.

### Fixed
- A real, pre-existing bug found alongside this commission, not introduced by it: `betting_slip_legs.decimal_odds` was a non-nullable decimal column, but `ParseSlipText`'s own "not detected" value (`''`) was never a valid decimal — every pasted-text slip with undetected odds crashed with a raw SQL error before insert. Fixed by making the column nullable (`2026_08_07_000002_make_decimal_odds_nullable_on_betting_slip_legs_table`) and mapping `''` → `null` in `ParsedLeg::toLegAttributes()`; `BettingSlipValidationRules::legIsComplete()` already treated a missing value as incomplete, unchanged.

### Not Done
- No live AI provider integrated — a deliberate Product Office decision (Decision 1), not an oversight; the interpreter/provider boundary is a swappable interface specifically so that remains a future, separately-authorized decision.

Full regression: 760 tests (753 passed, 7 pre-existing self-skipped, 0 failed), verified directly via `/usr/local/bin/php artisan test`. `pint --test` clean on every file this directive touched (a one-line style fix — importing `BettingSlipStatus` instead of referencing it inline by FQCN in `tests/Feature/BettingSlipIntakeTest.php` — applied after Pint flagged it during verification; cosmetic only, no behaviour change).

## `PO-U23-001A` — Feature Matrix / Gating Reconciliation with Shipped Capability B

**Status:** Delivered. Documentation correction only — no code, migration, or test change.

### Changed
- `docs/01-product/FEATURE_MATRIX.md` — "Safe Accumulator Builder" corrected to its real shipped name, Build an Accumulator (Capability B, MVP-gated behind `MARKET_WIDE_PLANNER_ENABLED`); the OCR/parser row split into what actually shipped (deterministic text/PDF slip parsing via `ParseSlipText`, screenshot upload with manual transcription) versus what remains genuinely Post-MVP (real image-to-text OCR); the conversational entry layer (`PO-U23-001`, above) added as its own MVP row under the same gate.
- `docs/01-product/FEATURE_GATING.md` — Capability B and its conversational entry layer added under Registered User (gated); "Safe Accumulator Builder" removed from Premium Candidates now that it has shipped.
- `docs/08-operations/DELIVERY_ROADMAP.md` — U-06's 2026-07-26 "still open, not yet authorized" note on Capability B corrected in place (retained unedited for historical accuracy of that original decision): re-authorized at `PO-U08.1-001-R1`, built out as Programme U-17, included in MVP v1.0 per `PO-MVP-004`, conversational layer delivered under `PO-U23-001`.

### Not Done
- Broader MVP-scope drift beyond these three documents' Capability B/conversational-layer rows (e.g. Planner/Programme U-07 has no row in `FEATURE_MATRIX.md` at all) was flagged in the correction note itself rather than silently corrected — out of this reconciliation's narrow scope.

No test-observable behaviour changed; full regression unchanged from the `PO-U23-001` figure above (documentation-only commit).

## `PO-U23-002` — MVP Definition / Product Blueprint Language Correction

**Status:** Delivered. Documentation correction only — no code, migration, or test change.

### Changed
- `docs/01-product/MVP_DEFINITION.md` / `docs/01-product/PRODUCT_BLUEPRINT.md` — "weakest-leg explanation"/"weakest-leg detection" corrected to "main contributing factor" in the Primary Journey and MVP Included lists: the base Analyze → Report path these documents scope shows the Main Contributing Factor (a risk-factor concept, `resources/views/livewire/betting-slips/report.blade.php`), not a per-leg ranking. The real weakest-leg ranking engine (`RankLegsByStructuralWeakness`, the Marginal Structural Contribution model, `U-06.2A`/`E-06D.1`) exists and is real — wired into the Planner and Capability B, just outside this document's base-analysis scope.
- "Automated parsing"/"live sports data" removed from `MVP_DEFINITION.md`'s Explicitly Not Required — deterministic text/PDF slip parsing and Capability B (drawing on live fixture/market data) both ship in MVP; per-bookmaker automated parsing remains correctly excluded.
- `PRODUCT_BLUEPRINT.md`'s Customer/Public Navigation corrected to match the real, live navigation components; "Safe Accumulator Builder"/"Screenshot upload"/"Live odds" moved out of Deferred to reflect what has actually shipped (OCR itself correctly remains deferred).

### Not Done
- N/A — narrowly scoped language correction, fully applied.

No test-observable behaviour changed; full regression unchanged from the `PO-U23-001` figure above (documentation-only commit).

## `PO-RC1-009` — Final User-Facing RC1 Closure Pass

**Status:** Delivered, Product Office accepted.

### Fixed
- Real, user-facing Analyse/Report-vs-Planner terminology conflation: the public `/analyse` page and one homepage "How It Works" card both attributed weakest-leg ranking (a Planner-only capability) to the base Analyse → Report path, which only ever shows the Main Contributing Factor (`report.blade.php` §20.3). `analyse.blade.php`'s "Which leg is doing the most damage?" section rewritten to describe Main Contributing Factor; `home.blade.php`'s "Weakest leg: Over 2.5 Goals — contributes 53%" line (directly contradicting the "main contributing factor" sentence eight lines above it) removed; the homepage's Invisible Risk CTA re-pointed from `/analyse` to `/planner`, the page that actually explains weakest-leg analysis.
- Footer "Dashboard" link had no `@auth` guard (unlike the identical header link) — a guest clicking it landed on `/login` unannounced. Now matches the header's Sign In / Dashboard convention.

### Added
- 2 new Pest tests (the footer auth-state regression, plus one updated assertion for the corrected `/analyse` heading).

Full regression: 761 tests, 754 passed, 7 pre-existing self-skipped, 0 failed.

## Settings/Help Scope Decision (`PO-RC1-009` acceptance, 2026-08-08)

**Status:** Recorded — documentation only, no code change.

### Changed
- `docs/product/MVP_SCOPE_LOCK.md` — `PO-MVP-002`'s proposed Settings/Help deferral (§4.19/§4.20, both previously "DEFERRED *proposed*") superseded by direct Product Office instruction: both now INCLUDED for bounded RC1 completion, on the reasoning that both are already real, reachable authenticated destinations (not unbuilt surfaces). Explicitly bounded — real content only, no new product capability. Updated throughout: the §4 summary table, Sections 6/7/9 (Not Included, Premium Boundary, Deferred Roadmap), the release checklist, and the launch-blocker list.
- `docs/00-governance/DECISION_LOG.md` — new row recording this decision alongside `PO-RC1-009`'s acceptance.

### Not Done
- Settings/Help content delivery itself — explicitly scoped as separate, not-yet-started bounded commissions.

## `PO-U24-001` — Homepage Completion & Product Storytelling Uplift

**Status:** Delivered, Product Office accepted. A bounded uplift, explicitly not a redesign — the Hero and dashboard screenshot were preserved exactly; only weak or under-explained sections were touched.

### Added
- Two Feature Highlight sections — Analyse and Build an Accumulator — inserted between Invisible Risk and How SlipGuard Thinks. Each is one real product component plus one explanation, never a screenshot collage: the Analyse highlight is a Risk Report fragment (Structural Score badge + Main Contributing Factor panel) with real, engine-computed sample data (a curated 4-leg slip — three short-odds favourites plus one 8.50 outlier — run once via `AnalyzeBettingSlip` against a disposable local slip: score 67, band High, Main Contributing Factor "Selection Odds" — verified, then the test data discarded, never invented); the Build an Accumulator highlight reuses the real Builder's own existing "Illustrative preview · not a live candidate" honesty convention and fixture-row markup, condensed for the homepage, with one row marked "Ranked by structural contribution" (the weakest-leg capability legitimately belongs here, per `PO-RC1-009`'s corrected terminology boundary). The conversational entry layer (`PO-U23-001`) is named only in one modest supporting-copy phrase ("in your own words") — no dedicated visual, deliberately, since it is a deterministic interpreter for a bounded set of requests, not a general AI assistant.
- A bounded FAQ (6 questions, within the directed 5–7 range), reusing `report.blade.php`'s existing per-item Alpine disclosure pattern verbatim — now documented for the first time in `COMPONENT_PRINCIPLES.md`'s new Disclosure/Accordion entry, closing a real documentation gap found while reusing it (the pattern had shipped in `report.blade.php` before this entry existed). Every answer grounded in already-approved copy (`pages/faq.blade.php`, `pages/planner.blade.php`, `betting-slips/intake.blade.php`), not generic betting-industry assumptions; pricing deliberately omitted (already answered on `/faq`).
- `docs/05-ux/HOMEPAGE_STORYBOARD.md` amended (v1.2 → v1.3) with a third recorded amendment, ahead of implementation per the Frontend Work Rule — the new flow and the reasoning for every insertion/consolidation.

### Changed
- Product Capabilities rebalanced, not replaced: the Analyse card's own description shortened to avoid duplicating the new highlight directly above it; three bare principle-tag pills ("Weakest-Leg Explanation," "Customer-Controlled Decisions," "No Outcome Prediction," which named no distinct capability of their own) replaced with real one-line descriptions for Build an Accumulator, Decision Journal, and Planning History.
- Product Preview caption tightened — leads with workspace breadth ("Your analysis, planning tools and reports in one workspace") while keeping the existing evolving-product honesty note, one sentence instead of two.
- Atmosphere/quiet background alternation (`public-section-atmosphere`/`public-section-quiet`) continues unbroken through both new insertions — verified by an updated Pest assertion, not assumed.

### Design Capability Invocation
- UI UX Pro Max genuinely invoked (`--design-system` and targeted `landing`/`ux` domain queries) — its generic "Enterprise Gateway" pattern and gold/purple palette/typography suggestion rejected in full (SlipGuard's own locked, approved design system used throughout instead); its general "one key message per card" principle and accessibility findings adopted, corroborating decisions already grounded in this repository's own governance.
- 21st.dev: confirmed unavailable in this session (`API_KEY_21ST` unset, no `mcp__21st__*` tool registered) — consistent with this repository's own prior documented finding for this exact tool. Not queried; disclosed rather than fabricated.
- No OddStorm imitation — reviewed as a content-architecture reference only, per the commissioning directive; no layout, colour, typography, or component reproduced.

### Verified
- Real browser verification performed via Playwright against a locally cached, mac12-compatible Chromium (the Claude-in-Chrome extension remains unavailable in this execution environment) — the first genuinely browser-verified pass in this session's homepage work: full-page screenshots at mobile (390px)/tablet (768px)/desktop (1440px), light and dark theme; the FAQ accordion opened and closed via real click in every combination; theme persistence verified through real navigation (Home → Analyse → Home → Planner → Home, dark theme held throughout every hop, zero console errors on any page load).
- 6 new/updated Pest tests: two highlight-content tests, one explicit `PO-RC1-009` terminology-regression test, one FAQ test, one rebalanced-Capabilities test, one updated atmosphere/quiet section-count assertion.
- Full regression: 766 tests, 759 passed, 7 pre-existing self-skipped, 0 failed, 3003 assertions. Pint clean on every file touched. Production build clean. `git diff --check` clean.

### Not Done
- **One responsive defect found and explicitly not fixed here:** a ~28px horizontal overflow at mobile/tablet viewport widths, sourced to the Hero's own pre-existing drag-to-explore dashboard-viewport mechanism. Confirmed via a `git stash` A/B test to be present identically with every `PO-U24-001` change removed — genuinely pre-existing, not introduced by this commission. Left unfixed per this commission's own "preserve the homepage architecture exactly... do not redesign" scope (a fix would mean touching shared Hero/layout mechanics well beyond this bounded uplift) — flagged for a future, separately-scoped commission, matching this repository's own established precedent (`U-20.8`'s footer contrast finding). **Resolved next — see `PO-RC1-010` below.**
- Full U-21.5 "Why Trust SlipGuard" expansion, Risk Watch, External Intelligence, multi-sport expansion, OCR, bookmaker automation, new pricing architecture — none introduced, all explicitly out of scope.

## `PO-RC1-010` — Hero Responsive Horizontal Overflow Correction

**Status:** Delivered, Product Office accepted. Bounded defect correction only — the Hero was not redesigned.

### Fixed
- The ~28px (390px)/121px (768px) horizontal overflow flagged under `PO-U24-001` above. Root-caused via a controlled real-browser experiment, not theorised: four candidate causes were toggled individually (`<html>` given the same `overflow-x: clip` as `<body>`; `<body>`'s `overflow-y` paired to `clip`; the drag-viewport's own negative margin removed; the wrapper's `-mx-3`/`-mx-4` removed) and only the first fully closed the gap, to exactly 0px. The actual defect was never the Hero's own intentional full-bleed dashboard-viewport bleed (`-mx-3`/`-mx-4` wrapper margin, `margin-right: calc(50% - 50vw)`, both deliberate, both untouched) — it was that `<body>` carried `overflow-x-clip` but `<html>`, the real document-scrolling element whose own `scrollWidth` governs page-level horizontal scroll, did not. One-line fix: `overflow-x-clip` added to `<html>` in `layouts/public.blade.php`, matching what `<body>` already had — not a generic `overflow-x-hidden` band-aid applied without evidence.

### Verified
- 0px overflow at 390/430/768/1024/1440px, light and dark theme, confirmed via real Playwright measurement (a locally cached, mac12-compatible Chromium — the Claude-in-Chrome extension remains unavailable in this environment) — was 28/121px before the fix.
- Verified across every public page sharing this layout, not just the homepage: `/`, `/analyse`, `/planner`, `/reports`, `/faq`, `/pricing`, `/about` — all 0px.
- Drag-to-explore interaction confirmed still functional: a real keyboard-driven pan (`ArrowRight` on the focused viewport) moved `scrollLeft` 0 → 189, exactly as before.
- Hero copy, CTAs, dashboard screenshot, and section order all confirmed pixel-identical before/after via real screenshot comparison — nothing else touched.
- 1 new Pest test (asserts the static markup fact a real-browser regression would hinge on, since Pest has no layout engine to assert `scrollWidth` directly).
- Full regression: 767 tests, 760 passed, 7 pre-existing self-skipped, 0 failed, 3006 assertions. Pint clean on both touched files. Production build clean. `git diff --check` clean.

## `PO-U24-002` — Help & Methodology Completion

**Status:** Delivered (commit `47e31fc`).

### Added
- A real, content-truth-matrix-grounded `/help` page (`resources/views/livewire/help/index.blade.php`), replacing the previous coming-soon stub. Every claim verified against the actual RC1 implementation before being written: Getting Started (4-step journey); Analysing a Slip (real intake methods — manual/paste/PDF/screenshot — with an explicit no-OCR/no-bet-code disclosure); Understanding Your Report (Structural Risk Score 0–100 explicitly not a probability, the real `RiskBand::forScore()` bands/thresholds, Main Contributing Factor, all 5 active Structural Factors plus the inactive 6th, a Finding→Evidence→Reasoning→Conclusion illustration explicitly not claimed as a literal report diagram); Build an Accumulator (real flow steps, the Planner's structural-ranking capability explicitly kept distinct from the base report's Main Contributing Factor per `PO-RC1-009`); How SlipGuard Thinks (methodology + the same pipeline diagram already used sitewide); What SlipGuard Does Not Do; a 9-question FAQ (reusing `report.blade.php`'s existing disclosure pattern); a Contact CTA.
- 13 new Pest tests (`tests/Feature/Workspace/HelpTest.php`), including explicit `PO-RC1-009` regression protection so Main Contributing Factor and the Planner's own ranking capability stay distinct.

### Changed
- `routes/web.php` — coming-soon stub swapped for a real Volt component.
- `authenticated-shell.blade.php` — page title corrected to "Help & Methodology" to match the real content now behind it.
- `WorkspaceAccessTest.php` — updated: Help is real now (Settings remained the one coming-soon stub until `PO-U24-003` below).

### Fixed
- One real, pre-existing accessibility defect this page introduced: an invalid `<dl>` child structure and a risk-band badge colour pairing that failed contrast at compact size — replaced with solid text plus a decorative colour dot rather than fighting the pairing.

### Disclosed, Not Fixed
- One real, pre-existing repository contradiction, not silently resolved: the Builder route's own comment claims a 404 when `MARKET_WIDE_PLANNER_ENABLED` is off, but the real `mount()` has no such abort. Help describes the real (honest-preview) behaviour, not the stale comment.
- A second, unrelated dark-theme contrast pattern was investigated, confirmed pre-existing via a control scan against the untouched `/journal` page, and flagged rather than fixed — matching the `PO-RC1-010` precedent.

### Verified
- Real browser verification via Playwright + a locally cached, mac12 Chromium (logged in through the real login form): 0px overflow at 390/430/768/1024/1440, light and dark; FAQ accordion exercised via real click; Contact CTA clicked through to a real 200.
- Full regression: 780 tests, 773 passed, 7 pre-existing self-skipped, 0 failed. Pint clean. Production build clean. `git diff --check` clean.

## `PO-U24-003` — Settings Completion

**Status:** Delivered (commit `aa48d91`).

### Added
- A real, repository-truth-grounded `/settings` page (`resources/views/livewire/settings/index.blade.php`), replacing the previous coming-soon stub. Repository inspection found exactly one setting genuinely exclusive to this destination and safely exposable at RC1: Appearance (Theme) — a client-side-only Light/Dark control sharing the exact sitewide mechanism (`window.SlipGuardTheme`), never a second theme state, never sent to the server. Final information architecture: `Settings → Appearance (Theme: Light/Dark), Account (summary → Profile), Security (summary → Profile)` — three sections, all real, no empty/filler sections.
- 9 new Pest tests (`tests/Feature/Workspace/SettingsTest.php`).

### Changed
- `routes/web.php` — coming-soon stub swapped for a real Volt route.
- `WorkspaceAccessTest.php` — stale coming-soon assertion replaced; Settings added to the "real screens" test.

### Fixed
- One real, page-owned accessibility defect: the theme control's checked-state text failed contrast in dark theme (`text-white` on the dark theme's lighter `accent-strong`, 2.98:1) — fixed by switching to the existing theme-constant `text-surface-inverse` token in dark theme (5.7:1); `text-white` unchanged in light theme (6.3:1, already passing).

### Not Done
- Language and Notifications correctly omitted, not faked — no second locale set exists (`config('app.locale')` is `en`-only) and no real product/marketing email opt-in/opt-out exists anywhere in the codebase to back a toggle. Session management deferred — no device/session listing or revocation is implemented anywhere. Name/email/password/deletion editing intentionally kept on Profile (the page that already fully owns them) rather than duplicating a second, competing editor here.

### Disclosed, Not Fixed
- Two pre-existing dark-theme axe-core findings from the shared `.atmosphere` background layer and the pre-existing `accent-strong`/white contrast pattern already live on Help's CTA — both predate this commission and belong to a separately-scoped shell/token pass, matching the `PO-RC1-010`/`PO-U24-002` precedent.

### Verified
- Real browser verification via Playwright + system Chrome (logged in via the real login form): 0px overflow at 390/430/768/1024/1440, light and dark. Real theme-persistence journey: select Dark → navigate to Dashboard → return to Settings (still Dark, radio correctly checked) → hard refresh (still Dark). Keyboard: focus Light → ArrowRight → focus and selection both move to Dark (native radiogroup behaviour, no custom handler needed).
- axe-core (WCAG 2 A/AA) at 390/1440 × light/dark: 0 violations after the contrast fix above.
- Full regression: 788 tests, 781 passed, 7 pre-existing self-skipped, 0 failed. Pint clean. Production build clean. `git diff --check` clean.

## `PO-U24-004` — Collapsible Desktop Sidebar & Sticky Header Glass Correction

**Status:** Delivered. Founder-commissioned shell UX polish, the desktop/tablet counterpart to the pre-existing mobile drawer, which this pattern does not replace or touch.

### Added
- A collapsible desktop/tablet sidebar: a single shared preference (`data-sidebar="collapsed"` on `<html>`, `window.SlipGuardSidebar` in `resources/js/app.js`) architecturally identical to the existing theme system — localStorage-backed, applied pre-paint via a new `partials/sidebar-init-script.blade.php` (mirroring `theme-init-script.blade.php` exactly, included in both `layouts/app.blade.php` and `layouts/labs.blade.php`), re-synchronized after every `wire:navigate` morph via the same `MutationObserver` pattern theme already uses.
- One CSS custom property (`--sidebar-w`, `18rem` expanded / `5rem` collapsed via `resources/css/app.css`) drives both the rail's own width and the workspace content wrapper's `padding-left` (`authenticated-shell.blade.php`) from two separate files without a cross-component JS binding.
- A dedicated, explicit collapse toggle (never hover-to-expand) in its own row beneath the logo header: `aria-expanded` reflects state, `aria-label` switches between "Collapse navigation"/"Expand navigation", 44×44px touch target, the existing `chevron-double-left` icon rotated 180° on collapse.
- Every nav item's accessible name stays the full label at all times (`sr-only` when collapsed, never `hidden`/removed); a decorative (`aria-hidden`, `role="tooltip"`) hover/focus tooltip restores the visible label for sighted users — the first tooltip pattern this codebase has needed.
- Three new `COMPONENT_PRINCIPLES.md` entries — Collapsible Sidebar, Tooltip, Collapse Control — documented ahead of implementation per the Frontend Work Rule, not invented in code.
- 7 new Pest tests (`tests/Feature/CollapsibleSidebarTest.php`) — server-rendered structural coverage (accessible labels, pre-paint script presence, CSS-variable-driven width/padding, tooltip presence, sticky header treatment, unchanged route destinations); collapse/expand interaction itself is Alpine/CSS-driven and outside what a Pest HTTP test can execute.

### Fixed
- The authenticated shell's sticky header was using heavier blur/opacity (`backdrop-blur-xl`, `bg-surface-page/90`) than the one documented, approved glassmorphism recipe (`VISUAL_INSPIRATION.md`: translucent surface + `backdrop-blur-md` + hairline border, no colour tint) already implemented identically on the public/portal header — brought in line with the existing recipe (`backdrop-blur-md`, `bg-surface-page/70`, `border-neutral-200/70`) rather than inventing a second one.

### Changed
- `tests/Feature/MobileNavigationDrawerTest.php` — updated assertion: the fixed `w-72` desktop rail class is now the collapsible rail's CSS-variable width (`w-[var(--sidebar-w)]`); the mobile drawer itself is untouched.

### Verified
- Full regression: 795 tests, 788 passed, 7 pre-existing self-skipped, 0 failed. Pint clean (one pre-existing, unrelated violation remains in `database/seeders/LabsMobileAppFeatureSeeder.php`, not this commission's to fix). Production build clean. `git diff --check` clean.

## Product Office Amendment — Dark Theme Text Legibility

**Status:** Delivered. Token-level correction, no page-by-page patching.

### Changed
- `resources/css/app.css` — `--neutral-400` (dark) `#55565b` → `#8e8f94`; `--neutral-500` (dark) `#85868b` → `#a3a4a8`. Updated in both dark-theme blocks (`@media (prefers-color-scheme: dark)` and the explicit `:root[data-theme="dark"]` override), which this file's own standing comment requires be kept in sync. `--neutral-600` through `--neutral-950` (dark) and the entire light-theme scale are unchanged — already clearing WCAG AA with margin (7.48:1+), and this amendment is dark-theme-only per the commissioning instruction.
- `docs/05-ux/DESIGN_TOKENS.md` — neutral scale table and provenance notes updated to record the new dark values, the measured before/after contrast ratios, and the reasoning.

### Fixed
- `--neutral-400` was documented as "placeholder text, muted icons" but is in fact used across the product as real metadata/helper-text colour (timestamps, footer disclaimers, `<dt>` labels, hint tags — 23 files) — at the old value it measured 2.33:1/2.12:1 against `surface-page`/`surface-card`, failing WCAG AA outright, not merely "muted."
- `--neutral-500` ("secondary text" — dashboard section headers, journal captions — 36 files) measured 4.69:1/4.27:1, passing `surface-page` but failing `surface-card`'s stricter case.
- New values clear 4.5:1 AA against **both** surfaces with real margin (5.27:1/4.80:1 and 6.84:1/6.23:1 respectively) while preserving a clearly perceptible step down from `neutral-600` and up, so the brightness hierarchy Product Office asked for (primary → body → secondary → metadata → disabled) stays intact rather than collapsing toward flat white.

### Design Capability Invocation
- UI UX Pro Max genuinely invoked (`--domain ux "dark mode text hierarchy contrast"`, `--domain color "dark mode neutral grey scale accessible pairs"`) — its dark-mode "muted foreground" reference pairing independently lands at 6.96:1/6.26:1 for a structurally identical role, corroborating the brightness range chosen here rather than it being picked freehand.
- 21st.dev: verified live-callable this session (`mcp__21st__search`), a change from this repository's own prior documented "unavailable" finding — queried directly, returned generic component-catalog results (no colour/contrast-guidance content relevant to a semantic-token decision), nothing adopted. Disclosed rather than silently skipped, per the standing "nothing adopted is a legitimate outcome" rule.

### Verified
- Real-browser dark-mode verification (Playwright + system Chrome) at 390/768/1440px across Dashboard, Analyse, Report, Planner (mapped to Planning History — no standalone session-less Planner screen exists in this product), Journal, Help, Settings.
- axe-core's automated color-contrast check reported violations against the new values on several of these screens — investigated directly rather than accepted at face value, and confirmed to be false positives: the same pre-existing, already-disclosed `.atmosphere` fixed-decorative-layer limitation this repository's own history already names (`PO-U24-002`, `PO-U24-003`, `PO-RC1-010`) — axe cannot see a `position: fixed` background layer and falls back to assuming a plain white canvas, understating real contrast. Confirmed by direct pixel sampling (not axe's computed-style guess) of the actual rendered screenshots: `neutral-500` against the real Dashboard card background measures 6.03:1 (axe claimed 3.48:1 against a wrong `#4b4b4f`); `neutral-500`/`neutral-400` against the real footer background measure 6.48:1/5.0:1 (axe claimed 2.49:1 against a wrong pure-white `#ffffff`) — both comfortably AA in reality.
- Two unrelated, pre-existing findings surfaced by the same scan, out of this amendment's scope, disclosed not fixed: a risk-badge icon axe sampled as black-on-tinted-background (risk/accent tokens, not the neutral text scale — likely the same atmosphere-sampling artifact, not investigated further) and a pre-existing `hover:bg-accent` white-icon contrast case.
- Full regression: 795 tests, 788 passed, 7 pre-existing self-skipped, 0 failed (no test or application code touched — CSS tokens and documentation only). Pint clean. Production build clean. `git diff --check` clean.

### Not Done
- Light theme — untouched, out of scope per the commissioning instruction.
- The two unrelated pre-existing findings named above under Verified — flagged for a future, separately-scoped pass, matching this repository's established disclose-don't-silently-expand-scope precedent.
