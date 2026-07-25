# SlipGuard Tasks

## Production Foundation
**Status:** Certified 2026-07-25 — `READY WITH OBSERVATIONS`. See `docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md` for the full engineering certification (governance versions, validation summary, test/performance/architecture/security/documentation summaries, outstanding debt, and the Platform Engineering → Customer Experience Engineering transition). Platform Engineering is now closed; all further work proceeds under Customer Experience Engineering (see below).

Note recorded by the certificate, not resolved by it: Risk Rule Set 2026.1 still awaits formal Product Office / Data Science sign-off (`docs/00-governance/DECISION_LOG.md`) — the engineering foundation implementing it is certified independently of that pending product decision.

### G-01 — Governance Consolidation (documentation only)
- [x] Consolidated governance principles already in consistent use (authority separation, stop/document/escalate, architecture preservation, versioned evolution, one-document-per-concern, refactoring restraint, foundation freeze) into `docs/00-governance/ENGINEERING_CONSTITUTION.md` — no new governance invented, audit trail in `docs/00-governance/GOVERNANCE_CONSOLIDATION_G01.md`.
- [x] Cross-referenced from `CLAUDE.md`'s Source-of-Truth Order, `PROJECT.md`, `docs/adr/ADR-INDEX.md`, and refreshed `docs/00-governance/REPOSITORY_STATE.md` (which was 2026-07-23-stale) and `DECISION_LOG.md`.
- [x] Confirmed no PHP, Blade, Livewire, Tailwind, migration, database, test, Risk Engine, Rule Set, persistence, or UX implementation was touched — documentation only, per scope.

## Active Milestone
E-06C — Analysis Persistence (delivered, pending Product Office / Data Science review). E-06C Validation (engineering validation sprint) and the Production Foundation certification are now also delivered — see above. Next: Customer Experience Engineering (U-02).

**Naming note (E-03B):** a prior sprint was directed as "E-04" but contained no mathematics or risk scoring — tracked as E-03B instead, since canonical E-04 (Deterministic Risk Analysis) is a different, still-blocked thing.

**Naming note (E-05A):** a prior sprint was directed as "E-05A," but its content (taxonomy/normalization) was engine-preparation work that logically precedes E-04's mathematics, not a sub-part of canonical E-05 (Risk Report, the presentation layer). Tracked as directed, flagging that the number didn't reflect actual sequencing.

**Naming note (E-06A):** this sprint is directed as "E-06A" and is, appropriately, exactly E-04's mathematics design — the naming finally lines up with the canonical roadmap's intent (E-04 Deterministic Risk Analysis), just under a different label. No collision to flag this time.

**Naming note (E-06C, superseded same day):** this sprint's own code initially labelled itself "E-06C," colliding with the then-current roadmap's E-06C (weakest-leg/highest-risk-leg ranking) — so it was briefly renamed E-06D pending clarification. Product Office then ruled explicitly: persistence is a genuine prerequisite for U-02 (Dashboard, History, Risk Report retrieval, Journal linkage, and rule-set/version traceability all require it), so it is inserted into the sequence as **E-06C — Analysis Persistence**, and weakest-leg/highest-risk-leg ranking is renumbered **E-06D**. See `docs/00-governance/DECISION_LOG.md` (2026-07-25). Sequence: E-06B (engine) → E-06C (persistence) → E-06D (weakest-leg) → U-02.

## Completed
### E-02 (Sprint E-02A — Customer Identity Foundation) — Closed, Product Office approved
- [x] Run repository and SGOS context audit.
- [x] Configure protected Filament operations access (`is_internal` flag, gated panel).
- [x] Add operational health check (`slipguard:health` command and `/health` endpoint).
- [x] Add `slipguard:make-internal-user` command with a production guard.
- [x] Add Pest coverage for the above (operations access, health check, user promotion, `is_internal` defaults).
- [x] Inspect authentication and frontend setup (no scaffolding present; Blade, Livewire, Vite, and Tailwind confirmed installed).
- [x] SGOS v1.0 stabilization pass — archived duplicate bootstrap documentation to `docs/_legacy-bootstrap/`, declared the SGOS version, rewrote the root README, added a documentation-authority notice, recorded the first incident log entry, and validated SGOS cross-references.
- [x] SGOS v1.0 final stabilization — added Documentation Freeze to `CLAUDE.md`, declared repository maturity and working principles, re-validated cross-references.
- [x] ADR-005 accepted — founder approved Laravel Breeze (Livewire stack) via the Sprint E-02A directive.
- [x] Implement authentication (Breeze, Livewire/Volt stack): registration, login, logout, password reset, session and CSRF protection. Email verification and password-confirmation scaffolding left uninstalled/unused per ADR-005 non-scope.
- [x] Build the permanent customer layout (header, navigation, user menu, notifications placeholder, footer) — extended by every workspace and "coming soon" screen.
- [x] Add compact customer navigation (Dashboard, Analyze Slip, History, Journal; Profile/Settings/Help in the account menu). Non-functional destinations render a "coming soon" screen on the permanent layout rather than a dead link.
- [x] Create the first-use dashboard state (welcome message, primary CTA, explanation, empty states for recent analyses and journal, getting-started tips).
- [x] Add profile basics (view/edit name and email, change password) via restyled Breeze Livewire components.
- [x] Establish the ownership authorization convention: `App\Policies\UserPolicy` (`view`/`update` scoped to `$user->is($model)`), enforced in both profile Livewire components via `$this->authorize()`. Future customer-owned resources (slips, analyses, journal entries) should follow the same shape.
- [x] Add Pest coverage: registration, login/logout, password reset/update/confirmation, guest redirects and authenticated access for every workspace route, dashboard content, coming-soon pages, and the authorization policy (52 tests passing).
- [x] Restore the project's Tailwind v4 + `@tailwindcss/vite` setup after Breeze's installer silently downgraded it to a v3/PostCSS config.
- [x] Replace default Laravel/Breeze branding (homepage, guest layout, logo, `APP_NAME`) with minimal SlipGuard branding.

Pest: 52/52 passing at closeout.

## Delivered — Pending Product Office Review
### E-03 — Manual Slip Capture
- [x] `BettingSlip` and `BettingSlipLeg` models, migrations, and factories.
- [x] `BettingSlipPolicy` extending the ownership convention from `UserPolicy` (`viewAny`/`create` open to any authenticated user, `view`/`update`/`delete` scoped to `user_id`).
- [x] `App\Actions\BettingSlip\SaveBettingSlip` — transactional create/update that replaces a slip's legs in one action, per `ENGINEERING_STANDARDS.md`'s "use transactions for multi-record operations."
- [x] Slip builder (`/analyze/create`, `/analyze/{bettingSlip}/edit`): add/remove/reorder legs, edit sport/competition/event/market/selection/odds, validation with plain-language messages, `config/slipguard.php` for configurable min/max legs (default 1/20).
- [x] Slip index (`/analyze`): lists the current user's own slips only, empty state ("No slips yet."), delete with confirmation modal.
- [x] Pest coverage: create, update (legs fully replaced), delete, zero-leg rejection, max-legs enforcement, per-field validation, guest redirects, cross-user forbidden access, index scoped to owner only (10 new tests).

Pest: 62/62 passing.

### E-03B — Slip Domain Hardening
- [x] Slip lifecycle: `App\Domain\BettingSlip\BettingSlipStatus` enum (Draft/Ready/Analysed/Archived) with explicit `allowedTransitions()`; illegal transitions throw `InvalidBettingSlipTransitionException`.
- [x] Centralized domain validation: `App\Domain\BettingSlip\BettingSlipValidationRules` (single source of rules/messages, used by the builder) plus `App\Rules\NoDuplicateBettingSlipLegs`.
- [x] Analysis eligibility: `BettingSlip::analysisEligibility()` / `isAnalysisEligible()`, backed by `AnalysisEligibility` + `AnalysisIneligibilityReason` value objects (Empty, Incomplete, AlreadyAnalysed, Archived).
- [x] Immutable analysis input: `SaveBettingSlip` now refuses to touch a slip that has left Draft (`BettingSlipNotEditableException`); the builder renders locked/read-only once a slip is Ready, Analysed, or Archived.
- [x] Versioning decision recorded: `ADR-006` — no separate version column/history table; lifecycle status is sufficient.
- [x] Ownership hardening reviewed: `BettingSlipPolicy` unchanged in shape (lifecycle transitions reuse the `update` ability); confirmed guests and non-owners are rejected at the route/mount level for every operation, including the new transition actions.
- [x] Persistence hardening reviewed: soft delete/restore considered and **not** implemented — no current requirement to recover a deleted slip. (Deletion scope was later narrowed in Sprint E-05A — see below — once analysis durability became a real concern.)
- [x] UI polish: status badges and a lifecycle action bar on the builder, per-status filter tabs and four distinct empty states on the index, leg counter, top-level error banner, and `wire:loading` states on save/add-leg. No redesign.
- [x] Pest coverage: valid/illegal transitions, analysis eligibility, immutable-input enforcement (action level and UI level), duplicate-leg rejection, ownership on lifecycle actions, deletion at every lifecycle stage, status filtering (29 new tests).
- [x] Performance reviewed: no N+1 introduced; no new indexes added — `user_id` is already indexed via its foreign key and per-user slip counts are small enough that no additional index is justified yet.

Pest: 91/91 passing.

### E-05A — Football Taxonomy & Deterministic Normalization
- [x] Deletion policy correction (preliminary task): `BettingSlipPolicy::delete()` now permits Draft and Ready only; Analysed/Archived slips must be archived, not deleted. Recorded as an addendum to `ADR-006` plus a `DECISION_LOG.md` row. UI updated (Delete hidden for Analysed/Archived, replaced with "Archive to remove").
- [x] Taxonomy version `1.0` (`FootballMarketTaxonomyV1::VERSION`) — see `docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md`.
- [x] Football-first sport normalizer (`App\Domain\Risk\Normalization\NormalizeSport`): exact alias match only, distinguishes `unsupported` (a real named sport, e.g. Tennis) from `unrecognized` (gibberish).
- [x] Nine football market families with stable codes and fixed complexity (`App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1`): Match Result, Double Chance, Draw No Bet, Total Goals, Both Teams to Score, Team Total Goals, Correct Score, Half-Time Result, Half-Time/Full-Time. Handicap deliberately deferred — alias text can't reliably distinguish Asian/European without guessing.
- [x] Deterministic matching pipeline (`App\Domain\Risk\Normalization\NormalizeFootballMarket`): trim/collapse/lowercase/hyphen-normalize → exact alias lookup → controlled pattern extraction (total-goals line, BTTS yes/no, correct score) → complete/partial/unrecognized. No fuzzy matching anywhere.
- [x] Snapshot-normalization boundary (`App\Domain\Risk\Normalization\NormalizeBettingSlip`): reads a Ready slip's legs, returns normalized results; rejects Draft/Analysed/Archived via `BettingSlipNotReadyException`. Does not calculate risk, persist anything, or change slip state.
- [x] Developer diagnostic: `slipguard:normalize-slip {bettingSlip}` console command (table output; no Filament resource built).
- [x] No database changes — normalization happens entirely at analysis-preparation time; free-text leg columns unchanged.
- [x] Pest coverage: every approved alias, all 8 worked fixtures (NT-001–NT-008), generalized line-detection beyond the literal "2.5" example, determinism, raw-text preservation, lifecycle rejection, max-legs, deletion-policy regression (67 new tests: 60 Unit + 7 Feature).
- [x] Code review (medium effort, 8 finder angles + verification): found and fixed 5 real correctness bugs — a total-goals direction-detection bug where an ambiguous market alias shadowed the actual selection, an eligibility/lifecycle contract inconsistency (`analysisEligibility()` didn't require Ready), uncaught exceptions on `archive()`/`returnToDraft()` double-transitions, a whitespace edge case in duplicate-leg detection, and a silent no-op on stale saves. Plus one test-coverage gap closed (non-owner deletion on Draft/Ready). 5 cleanup/efficiency findings (taxonomy rebuilding on every lookup, business rules duplicated across policy/views, hand-rolled string normalization repeated 4 times) deliberately left unfixed as lower-priority — see CHANGELOG.
- [x] Pre-commit correction: removed the stale "open questions" framing (taxonomy location and unrecognized-market baseline were already resolved by this sprint's own implementation, not still open); fixed the `Pint` style findings the review missed; created 12 genuine project skills under `.claude/skills/` (`laravel-engineering`, `livewire-product-interface`, `filament-operations`, `pest-verification`, `domain-modelling`, `deterministic-risk-mathematics`, `explainability-responsible-betting`, `security-authorization`, `database-migration-safety`, `accessibility-review`, `documentation-stewardship`, `sprint-verification`) — confirmed discovered and loadable in-session.

Pest: 167/167 passing.

### E-06A — Deterministic Risk Factor Mathematics (design only)
- [x] `docs/03-data-science/RISK_RULE_SET_2026_1.md` — the single authoritative rule-set design document, status **READY FOR PRODUCT APPROVAL**: five active factors + one explicitly inactive (relationship), two group interaction caps, a 78-point achievable ceiling **proven exactly reachable** (TV-019 scores exactly 100), risk bands validated against a 20-vector distribution, a three-tier analysis-availability gate (sport hard-gate → market-unrecognized proportion gate → data-quality deduction score), a finalized reason-code catalogue, 13 mathematical invariants, and 20 computed test vectors.
- [x] Fixed the cross-sport normalization defect this sprint identified — see the separate `7d13c27` commit below — rather than leaving it as a documented requirement on E-06B.
- [x] Rejected every logarithm-based candidate formula (leg count, combined odds, concentration) after confirming directly against the installed `brick/math` library that `BigDecimal` has no `ln`/`log` method — replaced with piecewise-linear interpolation and a linear-share Herfindahl index, both fully fixed-precision.
- [x] Corrected the sprint's own "4 decimal place odds" assumption against the actual schema (`decimal_odds` is `DECIMAL(6,2)`, 2 d.p.).
- [x] Resolved all seven previously-open decisions individually (rule-set doc §22.1–22.7): factor tables, group cap values, the analysis-availability gate structure, two proof vectors (single-leg ceiling 54/High, two-leg Very High 77), the RF-005 sport-gate requirement (now implemented, not just required), the reason-code catalogue, and `brick/math`'s dependency timing (deferred to E-06B, zero-risk).
- [x] Redesigned the Limited Analysis policy into three tiers (sport hard-gate, 25% market-unrecognized proportion gate, partial-normalization deduction score) after finding the original single-score design could disagree with a proportion-based reading of the same slip.
- [x] All 20 test vectors (17 retained + 3 new: ceiling proof, single-leg ceiling, two-leg Very High) computed via the same `BigDecimal`-based reference script.
- [x] No production scoring code, no migration, no dependency addition, no persistence, no weakest-leg logic — design and one bug fix only, per scope.

### Cross-Sport Normalization Fix (commit `7d13c27`)
- [x] `NormalizeBettingSlip` now gates market normalization on `sport->sportCode === NormalizeSport::FOOTBALL_CODE`; non-football legs get `NormalizedMarket::notClassifiedForSport()` instead of a (possibly false-positive) football classification.
- [x] 6 new regression tests: football no-regression, three overlapping-phrase unsupported-sport cases, one unrecognized-sport case, one determinism case. 173/173 passing at commit time.

### E-06B — Deterministic Risk Engine Implementation
- [x] `App\Domain\Risk\Engine\CalculateStructuralRisk` — pure entry point, exact §7 calculation order, no persistence/mutation/external calls.
- [x] `RuleSet2026_1` and all six `RiskFactor` implementations (RF-001–RF-005 active, RF-006 explicitly inactive at contribution 0).
- [x] `CalculateDataQuality` and `DetermineAnalysisAvailability` — independent data-quality score and three-tier analysis gate.
- [x] Immutable result objects and the full 18-code `ReasonCode` catalogue.
- [x] `PiecewiseLinearInterpolation` and `ProportionalGroupCap` support classes (BigDecimal throughout, no floats).
- [x] Fixed two real float-truncation defects found during the full regression run: `MarketComplexityFactor`'s average calculation, and `IndividualOddsFactor`/`CombinedOddsFactor`'s anchor-table literals — both were silently coercing floats to ints via `BigDecimal::of()`.
- [x] RF-003A: identified and corrected four canonical vectors (TV-003, TV-004/TV-015, TV-006, TV-009) in `RISK_RULE_SET_2026_1.md` whose original figures were produced by a reference script carrying the identical float-truncation defect — see `DECISION_LOG.md`. RF-003's formula itself required no change.
- [x] 121 new Pest tests: per-factor boundary/monotonicity/reason-code coverage, data quality, analysis gate, all 19 scoreable canonical vectors exact, property-style invariants (order independence, determinism, bounds, monotonicity, symmetry).
- [x] Full regression: 294/294 passing (up from 173). `pint --test` clean.
- [x] Performance verified: 0.615ms per 20-leg slip (target <10ms).
- [x] Confirmed no persistence, no weakest-leg logic, no AI/OCR/parser/UI, no approved math/taxonomy/normalization changed.

### E-06C — Analysis Persistence
- [x] `App\Actions\Analysis\AnalyzeBettingSlip` — the orchestration boundary between the pure Risk Engine and persistence: checks `BettingSlip::analysisEligibility()`, normalizes the slip, runs `CalculateStructuralRisk`, persists the complete immutable result inside one DB transaction, transitions the slip to Analysed. Throws `BettingSlipNotAnalysableException` (carries the specific `AnalysisIneligibilityReason`s) when ineligible; nothing is persisted and the slip stays untouched on rejection.
- [x] `SlipAnalysis` model/migration — one immutable row per completed analysis (`betting_slip_id` unique, `user_id` denormalized from the slip's own owner, never mass-assigned). Stores `availability`, `structural_score`/`risk_band` (nullable — null when Unavailable), `data_quality_score`/`band`, `limited_analysis`, and every engine output (`factor_results`, `interaction_adjustments`, `data_quality_deductions`, `factors_not_evaluated`, `reason_codes`) as plain JSON-safe arrays — every `BigDecimal` and enum already reduced to a string/value by the orchestration action, so the model has no dependency on the engine's value objects. Records `engine_version`, `rule_set_version`, `input_schema_version`, and `market_taxonomy_version` independently (four separate version axes, not one — `engine_version` was added under ADR-007, see below).
- [x] `LegAnalysis` model/migration — one immutable row per leg, the normalized snapshot (sport/market codes, family, complexity, status, decimal odds, raw inputs) as it existed at analysis time, independent of any later taxonomy version.
- [x] `SlipAnalysisPolicy` — `view` only, scoped to `user_id`; a `SlipAnalysis` is never created or edited through a user-facing request, only produced by `AnalyzeBettingSlip` from an already-authorized `BettingSlip`.
- [x] `NormalizedBettingSlipLeg` extended with `decimalOdds` (fixed-precision string, carried through the normalization boundary since the engine needs it and it requires no normalization of its own).
- [x] `BettingSlip::analysis()` (`HasOne`) added for retrieval; `SlipAnalysis::bettingSlip()`/`user()` and `SlipAnalysis::legAnalyses()`/`LegAnalysis::slipAnalysis()`/`bettingSlipLeg()` complete the graph both ways.
- [x] Pest coverage: Full/Unavailable availability persisted correctly (null score/band on Unavailable), per-leg snapshot fidelity and ordering, JSON round-trip fidelity for factor results and interaction adjustments, enum-collection round-trip for reason codes, every ineligibility path (Draft/empty/already-Analysed/Archived) rejected with zero rows written, one-analysis-per-slip enforced at the database level, confirmation the engine itself persists nothing, determinism across two independently-built slips with identical inputs, both-directions relationship retrieval, and the policy (15 new tests: 13 + 2).
- [x] `ADR-007` accepted (Product Office + Architecture Office) — the permanent engine/persistence/presentation layer boundary, forbidden call flow, and four-axis version traceability requirement. See `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`.
- [x] Closed a real gap ADR-007 surfaced: `engine_version` was missing from the persisted record entirely (only `rule_set_version`/`input_schema_version`/`market_taxonomy_version` existed). Added `CalculateStructuralRisk::ENGINE_VERSION` (`1.0`), threaded through `RiskAnalysisResult`, persisted as a new `slip_analyses.engine_version` column, asserted in `AnalyzeBettingSlipTest`.
- [x] Flagged, not resolved (recorded in `ADR-007` and `DECISION_LOG.md`): full re-analysis support (a new record per re-analysis, historical records untouched) is architecturally described by ADR-007 but not yet reachable — blocked by `slip_analyses.betting_slip_id`'s unique constraint and by `Analysed` being a terminal lifecycle state (E-03B, `BettingSlipStatus::allowedTransitions()`) with no path back to `Ready`. Left as-is pending an explicit Product Office decision rather than silently changing a locked lifecycle rule or silently leaving the ADR unimplemented without a note.

Pest: 309/309 passing (unchanged count — `engine_version` added an assertion to an existing test rather than a new one; the required, no-default column meant every factory/action call site had to supply it or the suite would fail outright). `pint --test` clean.

### E-06C Validation — Production-Ready Foundation Validation (engineering-only, no product/code changes)
- [x] Twelve-stage engineering validation sprint executed per the Engineering Office directive: repository audit, ADR-007 architecture boundary check, static analysis, database validation, persistence integrity, performance benchmark, security review, test review, documentation sync, engineering debt register, production readiness assessment. Full reports in `docs/engineering/`.
- [x] Independently re-ran the toolchain: `composer validate` valid, `composer dump-autoload -o` clean (9,682 classes), `pint --test` passed, `pest --compact` 309/309 passing (799 assertions) — confirms `TASKS.md`'s own claimed count.
- [x] Benchmarked the full `AnalyzeBettingSlip` pipeline (normalize → engine → persist) at 1/5/10/20 legs: linear scaling, ~11.5ms median at the 20-leg ceiling, well within any plausible request budget. (Distinct from E-06B's already-recorded pure-engine 0.615ms figure — the two should not be conflated.)
- [x] Zero Category A (release-blocker) findings. Two dormant Category B items and fourteen Category C items logged to `docs/engineering/engineering-debt-register.md`. Three Category D observations logged for Product/Architecture Office (not acted on).
- [x] Final recommendation: **READY WITH OBSERVATIONS** — see `docs/engineering/engineering-validation-report.md`. Customer-facing engineering (U-02) is cleared to proceed.
- [x] Confirmed no product mathematics, taxonomy, UX wording, or feature scope was touched — audit only, per directive.

### U-01 — SlipGuard UX Foundation (documentation only)
- [x] `docs/05-ux/DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `HOMEPAGE_STORYBOARD.md`, `DESIGN_TOKENS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md` — the permanent design language, added alongside the existing `UX_RULES.md`.
- [x] `CLAUDE.md`'s Frontend Work Rule and `PROJECT.md`'s UX Foundation Documents pointer.
- [x] Directory correction: used the existing `docs/05-ux/` rather than creating a colliding `docs/06-ux/` (06 is already `docs/06-engineering/`).
- [x] Confirmed no frontend components, pages, or placeholder screens were built — documentation only, per scope.

### U-01A — UX Foundation Governance Hardening (documentation only)
- [x] `docs/05-ux/EXPLAINABILITY_SYSTEM.md` — how analysis results are communicated (explanation hierarchy reconciled with `UX_RULES.md`, progressive disclosure, language rules, future compatibility notes only, no unbuilt features documented as if real).
- [x] `docs/05-ux/EMPTY_STATES.md` — all 15 required empty/error states, OCR/parser states explicitly marked reserved placeholders (out of MVP scope).
- [x] `docs/05-ux/TRUST_SIGNALS.md` — trust mechanisms, forbidden language, required vocabulary.
- [x] Version/Status/Owner/Related-Documents header added to all 14 `docs/05-ux/` documents; every document cross-references its related documents.
- [x] `CLAUDE.md`'s Frontend Work Rule strengthened with the full 14-document list, the "no new pattern without documentation" rule, and the Gap Rule.
- [x] `PROJECT.md` updated to match.
- [x] Confirmed no frontend code (Blade, Livewire, Filament, Tailwind, CSS, JavaScript) was changed.

## Blocked
- E-06D (weakest-leg / highest-risk-leg ranking, renumbered from E-06C — see the Naming note above) awaits its own sprint — E-06B deliberately implements only per-leg provisional factor contributions, no ranking.
- Risk Rule Set 2026.1 awaits formal Product Office / Data Science sign-off (`docs/00-governance/DECISION_LOG.md`) — does not block Customer Experience Engineering below, since the engineering foundation implementing it is certified independently (see Production Foundation, above).

(E-04 Deterministic Risk Analysis is no longer a blocked/future item — its mathematics were delivered under E-06A (design) / E-06B (implementation); see the Naming notes above. Retained here only as a pointer so this list stays accurate, not re-opened.)

## Customer Experience Engineering
*(formerly "Later" — archived history above is unchanged; this section is renamed and reordered to reflect the Production Foundation certification's transition statement, not new scope.)*
- [ ] U-02 — Customer dashboard, analysis history, risk report presentation, journal linkage (the immediate next milestone; E-06C's persistence layer is its validated prerequisite).
- [ ] E-05 Risk Report — presentation of the already-implemented deterministic result.
- [ ] E-06D — Weakest-leg / highest-risk-leg ranking (see Blocked, above).
- [ ] E-06 History and Journal — remaining scope beyond persistence (already delivered as E-06B/E-06C).
- [ ] E-07 Public Trust Website.
- [ ] E-08 MVP Hardening.
