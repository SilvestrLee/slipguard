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
