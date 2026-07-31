# Incident and Learning Log

Record meaningful errors, failed approaches, deployment incidents, and product missteps. Do not record trivial typos.

## Template

### INC-YYYY-NNN — Short title
- Date:
- Area:
- Environment:
- Severity:
- Status:
- What happened:
- Impact:
- Root cause:
- Fix:
- Verification:
- Prevention:
- Related commit or task:

## Log

### INC-2026-001 — Duplicate SGOS documentation from a concurrent bootstrap session
- Date: 2026-07-23
- Area: Repository documentation (`docs/`)
- Environment: Local repository, `develop` branch
- Severity: Low (no data loss, no code impact; documentation-only)
- Status: Resolved
- What happened: During SGOS v1.0 initialization, an earlier Claude Code session that had been started before SGOS existed continued running against a pre-SGOS milestone and wrote a full parallel, uncommitted documentation tree (`docs/01-foundation/`, `02-product/`, `03-architecture/`, `04-intelligence/`, `06-delivery/`, `07-decisions/`) covering the same ground as the intended SGOS structure under a different numbering scheme.
- Impact: No committed content was overwritten — the stray tree remained untracked by git. Risk was confusion for a future reader who might treat the untracked tree as authoritative, and one identified content contradiction (a risk-engine blocker attributed to the wrong milestone).
- Root cause: Two independent write operations targeting the same working tree at the same time: an intentional SGOS build-out and a stale session unaware SGOS had superseded it.
- Fix: Repository audit (2026-07-23) confirmed `docs/00-governance/` through `docs/09-compliance/` plus `docs/adr/` as the canonical, git-tracked structure matching `CLAUDE.md`'s Required Reading list. The stray tree was archived, unmodified, to `docs/_legacy-bootstrap/` with an explanatory README rather than deleted or merged.
- Verification: Cross-reference validation confirmed all paths referenced by `CLAUDE.md`, `docs/README.md`, and `docs/adr/ADR-INDEX.md` resolve to the canonical tree; `docs/00-governance/SGOS_VERSION.md` and a precedence notice in `docs/README.md` now make the authoritative set explicit.
- Prevention: Avoid running multiple concurrent Claude Code sessions against the same repository working tree during structural documentation changes; a paused/conflicting session should be reconciled before further writes, as happened here.
- Related commit or task: SGOS v1.0 stabilization pass, TASKS.md (E-02 milestone).

### INC-2026-002 — Breeze installer overwrote custom routes and downgraded the Tailwind pipeline
- Date: 2026-07-23
- Area: `routes/web.php`, `vite.config.js`, `package.json`, `resources/css/app.css`
- Environment: Local repository, `develop` branch, Sprint E-02A
- Severity: Low (caught immediately, no deployed impact)
- Status: Resolved
- What happened: Running `php artisan breeze:install livewire` regenerated `routes/web.php` wholesale, silently dropping the existing `/health` endpoint, and its Tailwind stub replaced the project's existing Tailwind v4 (`@tailwindcss/vite`) setup with a Tailwind v3/PostCSS configuration (`tailwind.config.js`, `postcss.config.js`, `@tailwind` directives).
- Impact: `/health` returned 404 until restored; the frontend build pipeline would have shipped on an older Tailwind major version than the project was already using, without anyone deciding to downgrade.
- Root cause: Breeze's installer stubs assume they own certain files outright (`routes/web.php`, the Tailwind config) and overwrite them rather than merging, regardless of pre-existing project state.
- Fix: Restored the `/health` route (grouped remaining workspace routes under `auth` middleware), and reverted to the project's Tailwind v4 setup (`@tailwindcss/vite` in `vite.config.js`, `@import "tailwindcss"` in `app.css`, removed the generated `tailwind.config.js`/`postcss.config.js`).
- Verification: Full Pest suite green (52/52) including the pre-existing health endpoint test; `npm run build` succeeds under the restored v4 pipeline.
- Prevention: Before running any Laravel installer/generator that scaffolds routes or frontend config (Breeze, Jetstream, or similar), diff `routes/web.php` and the Vite/Tailwind config immediately after, before assuming nothing else changed. Future Laravel/Breeze upgrades must preserve custom routes and the existing asset configuration — check this explicitly rather than trusting the installer.
- Related commit or task: Sprint E-02A, TASKS.md (E-02 milestone).

### INC-2026-003 — Dark theme doesn't reach the top header/hero band (mobile) or the page footer (desktop) on authenticated pages
- Date: 2026-07-29
- Area: `layouts.app`'s shared header/hero and footer partials — affects every authenticated page, not specific to any single screen
- Environment: Local repository, `develop` branch, browser verification during (1) the bounded-scope intake commission, (2) the Planner UX audit
- Severity: Low (visual/theme consistency only; no functional or data impact)
- Status: Open — found, not fixed, out of scope for both commissions during which it was observed
- What happened: Two related sightings, now recorded together since they likely share a root cause. (a) While capturing mobile/dark browser evidence for the new intake flows (`analyze/{bettingSlip}/edit` at 390×844, dark theme), the top ~380px band of the page (header nav, page title, status banners) rendered with the light-theme lavender gradient and dark text, while the content below correctly rendered in dark theme — reproduced independently on an unrelated existing page (`/analyze` index, same viewport/theme). (b) While auditing the Planner Workspace (`/planner/{session}` and `/dashboard`, desktop 1280×900, dark theme), the page **footer** (copyright bar + "SlipGuard evaluates decision risk" line) rendered fully light-themed against an otherwise correctly dark page — reproduced on both the Planner Workspace and the plain Dashboard, confirming it is unrelated to the Planner specifically.
- Impact: Cosmetic inconsistency for any customer using dark theme — the top of every authenticated page (mobile) and the bottom footer (desktop and likely mobile) read as light theme while the middle content is correctly dark.
- Root cause: **Narrowed during the `PO-U07.X.1-001` Planner premium workspace browser verification (2026-07-29)**, empirically, not by inspection alone: `document.documentElement.getAttribute('data-theme')` reads correctly as `"dark"` immediately after the theme-init inline script runs on a genuine full document load, but reads back `null` — despite `localStorage.getItem('slipguard-theme')` still correctly holding `"dark"` — immediately after a `wire:navigate` client-side transition (e.g. the redirect Livewire performs after login, or "Plan this accumulator"'s own `redirect(..., navigate: true)`). `theme-init-script.blade.php`'s inline `<script>` only runs once, on initial document parse; Livewire's `wire:navigate` swaps page content without a full reload, so the attribute is never reapplied on that path, and nothing currently listens for `livewire:navigated` to reapply it. A subsequent genuine full reload (e.g. a manual refresh, or any `page.goto` in this session's own verification scripts) always self-corrects, which is exactly why some earlier screenshots this session looked correctly themed and others didn't, depending on how the page was reached rather than which page it was — this reframes both (a) and (b) above as symptoms of the same navigation-path-dependent cause, not two independent partial-CSS-scope bugs.
- Fix: Not applied. Deliberately left alone under this commission's bounded scope (Planner premium workspace presentation only) to avoid unrequested scope creep into the sitewide theme-init mechanism. The likely fix shape (for whoever picks this up): bind `initScrollReveal`'s own existing `livewire:navigated` listener pattern (`resources/js/app.js`) to also reapply `data-theme` from `localStorage` on every `wire:navigate` transition, not only on first load.
- Verification: N/A — not fixed yet. This session's own further Planner browser verification used a genuine `page.goto` (not a clicked `wire:navigate` link) immediately before any dark-theme screenshot specifically to route around this bug and get a trustworthy read, rather than risk misreporting a rendering defect that was actually this navigation issue.
- Prevention: Raise with Product/Design Office as a standalone, explicitly-scoped fix — now with a concrete, verified root cause and a suggested fix location, rather than the open-ended investigation this incident originally called for.
- Related commit or task: Bounded-scope intake commission (Paste Text/PDF Upload/Screenshot Upload), 2026-07-28/29; Planner UX audit, 2026-07-29; Planner premium workspace implementation, `PO-U07.X.1-001`, 2026-07-29.
