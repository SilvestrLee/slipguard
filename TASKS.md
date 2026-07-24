# SlipGuard Tasks

## Active Milestone
E-05A — Football Taxonomy & Deterministic Normalization

**Naming note (E-03B):** a prior sprint was directed as "E-04" but contained no mathematics or risk scoring — tracked as E-03B instead, since canonical E-04 (Deterministic Risk Analysis) is a different, still-blocked thing.

**Naming note (E-05A):** this sprint is directed as "E-05A," but its content (taxonomy/normalization) is engine-preparation work that logically precedes E-04's mathematics, not a sub-part of canonical E-05 (Risk Report, the presentation layer). Tracked verbatim as E-05A per instruction, flagging that the number doesn't reflect actual sequencing — it lands chronologically before E-04's math, not after E-05's report UI.

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

## Blocked
- E-04 is blocked until Data Science approves formulas, thresholds, and test vectors.

## Later
- [ ] E-04 Deterministic Risk Analysis.
- [ ] E-05 Risk Report.
- [ ] E-06 History and Journal.
- [ ] E-07 Public Trust Website.
- [ ] E-08 MVP Hardening.
