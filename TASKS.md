# SlipGuard Tasks

## Active Milestone
E-03 — Manual Slip Capture

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

## Blocked
- E-04 is blocked until Data Science approves formulas, thresholds, and test vectors.

## Later
- [ ] E-04 Deterministic Risk Analysis.
- [ ] E-05 Risk Report.
- [ ] E-06 History and Journal.
- [ ] E-07 Public Trust Website.
- [ ] E-08 MVP Hardening.
