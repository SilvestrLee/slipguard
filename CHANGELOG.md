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
