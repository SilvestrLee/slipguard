# RC1 Operations Programme — Execution Report & Production Readiness Certificate

| Field | Value |
|---|---|
| Commission | `PO-OPS-001`, Product Office, direct founder instruction, 2026-08-04 |
| Owner | Operations Office (`docs/offices/OPERATIONS_OFFICE.md`) |
| Type | Implementation planning and execution, per the commission's own explicit framing — **not** discovery. Scope agreed with the founder before starting (see §0): prepare every artifact this environment can genuinely produce and verify; do not fabricate evidence for anything requiring real cloud/hosting infrastructure this environment does not have access to. |
| Predecessor | `docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md` (`OO-RC1-001`) — this programme executes against that blueprint's recommendations, does not redesign them. |
| Status | Delivered. **Production Readiness Certificate (§OP-07): NO GO for a real production launch today** — no infrastructure exists to launch *to*. Six of seven work packages otherwise complete and verified within this environment's real limits. |

---

# 0. Capability Boundary — Stated Before Any Work Package

This environment has no cloud provider account, no payment method, no domain registrar, and no SSH access to any real remote server — confirmed directly, not assumed (attempting `CREATE DATABASE` with the application's own MySQL credentials was refused, matching the same wall `PO-MVP-005`'s Database Audit already hit; no `nginx` binary exists locally to test-validate config syntax; no cloud CLI tools are installed). This is a fact about the sandboxed execution environment, not a policy choice.

Agreed with the founder before starting: this programme produces every artifact **genuinely achievable** without that access — real code, real scripts, real config templates, real local rehearsals wherever a local rehearsal can honestly stand in for the real thing — and states plainly, per item, whether something was **executed and verified** or is **prepared, pending real infrastructure**. No work package's evidence implies more than what actually happened.

---

# OP-01 — Production Infrastructure

**Status: Prepared, not provisioned. No server exists.**

| Deliverable | State | Evidence |
|---|---|---|
| VPS specification | Prepared | `ops/provision-server.sh` — Ubuntu 22.04/24.04 LTS, sized to the blueprint's guiding principle (1-1,000 users, single VPS, no containers) |
| Operating system + PHP runtime | Prepared | Same script: PHP 8.3-FPM + every extension `composer.json` requires (mysql, mbstring, xml, curl, zip, bcmath, intl, gd), `memory_limit=512M` set directly (closes `PO-MVP-005` finding R-10 at the production layer, not just the dev/test layer `phpunit.xml` already closes) |
| MySQL | Prepared | `mysql-client` installed by the script; the blueprint's own recommendation (self-hosted first, managed later) is unchanged, not re-decided here |
| Nginx | Prepared | `ops/nginx-slipguard.conf` — standard Laravel server block, `client_max_body_size` sized to the real 10MB upload limit (`PO-MVP-005` §7.8) plus headroom. **Not syntax-validated** — no `nginx` binary exists in this environment to run `nginx -t` against; validate before first use |
| SSL | Prepared | `ops/provision-server.sh`'s own documented follow-up step (`certbot --nginx`) — deliberately not hand-written as a static config block, since certbot's generated config is the real source of truth |
| Firewall | Prepared | `ufw` rules in the same script — SSH, HTTP, HTTPS only |
| Least-privilege users | Prepared | Script creates a dedicated non-root `slipguard` deploy user; app never runs as root |
| Production directory structure | Prepared | Atomic-release layout (`releases/`, `shared/`, `current` symlink) — the same structure `ops/deploy.sh` assumes, so the two are consistent with each other, not independently invented |

**Real, disclosed limitation:** none of this has been run against an actual server. `bash -n` (syntax-only) passed on every shell script; that is the strongest verification possible without a real target.

---

# OP-02 — Deployment Pipeline

**Status: Prepared, not executed against a real server. CI workflow syntax-validated.**

| Deliverable | State | Evidence |
|---|---|---|
| Deployment workflow | Prepared | `ops/deploy.sh` — clone → link shared storage/.env → `composer install --no-dev` → asset build → `migrate --force` → cache config/routes/views/events → **runs OP-06's functional smoke test before going live** → atomic symlink swap → reload PHP-FPM → restart queue workers → **runs OP-06's HTTP smoke test against the now-live site** → auto-rollback on failure → prune old releases |
| Rollback procedure | Prepared | `ops/rollback.sh` — points `current` at the previous timestamped release; `ops/deploy.sh` calls it automatically if the post-swap HTTP check fails |
| Zero-downtime strategy | Prepared | Atomic symlink swap (near-zero downtime, per the blueprint's own §O-02 scoping — true blue-green is explicitly a later scaling-tier item, not re-decided here) |
| Release checklist | See §OP-08 in `RC1_LAUNCH_BLOCKER_CLOSURE_PLAN.md` (unchanged, cross-referenced not duplicated) | — |
| Deployment verification | **Executed and verified** | See OP-06 below — this is the one piece of the pipeline genuinely exercised end-to-end in this environment |
| CI/CD pipeline | Prepared, syntax-validated | `.github/workflows/deploy.yml` — test job (Pint + full suite + `git diff --check`) gates a deploy job (SSH into production, run `ops/deploy.sh`). Validated with `npx js-yaml` (real YAML parse, not eyeballed) — passed. Cannot run for real: no GitHub Actions runner in this environment, and none of the required secrets (`SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`, etc.) exist anywhere |

---

# OP-03 — Environment Configuration

**Status: Prepared.**

`.env.production.example` — every variable that must differ from local dev, with the reason stated inline: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` (closes `R-09` for real at the template layer — the previous fix only documented it in the dev-facing `.env.example`), `FILESYSTEM_DISK=s3` (blueprint's durable-storage recommendation, made concrete), `LOG_LEVEL=warning`, a real `MAIL_MAILER=smtp` block (currently `log` locally), and a placeholder Sentry DSN line for OP-04. Queue configuration deliberately **unchanged** (`QUEUE_CONNECTION=database`) — restated from the blueprint, not re-litigated, since zero queued jobs exist anywhere in the application (verified directly, `app/Jobs` is empty). `ops/supervisor-slipguard-worker.conf` prepared alongside it regardless, so the first real queued job doesn't also require a same-day Operations change.

Secrets handling: unchanged from the blueprint's recommendation (platform's own secret editor, never a committed plaintext file) — nothing to add here until a real hosting platform is chosen.

---

# OP-04 — Monitoring & Health

**Status: Health check executed and verified for real. Uptime/error/queue/DB/storage monitoring prepared, pending a real monitoring account.**

| Deliverable | State | Evidence |
|---|---|---|
| Health endpoint | **Executed and verified** | `app/Listeners/CheckDatabaseHealth.php` — extends Laravel's own `/up` via the officially-supported `DiagnosingHealth` event (verified by reading the framework source, not assumed), so a failed DB connection now returns `/up` → 500 with a real error message instead of a false-positive 200. **3/3 tests passing** (`tests/Feature/Listeners/CheckDatabaseHealthTest.php`): the happy path, a mocked DB failure correctly throwing, and a real `GET /up` returning 200 against the live local database. Closes `PO-MVP-005` finding R-20. |
| Uptime monitoring | Prepared, not subscribed | Blueprint's own recommendation (an external uptime pinger hitting `/up`) unchanged — requires a real account with an external service, none exists |
| Application/error monitoring | Prepared, not subscribed | `.env.production.example`'s placeholder `SENTRY_LARAVEL_DSN` line — the blueprint's own Sentry/Flare recommendation, not yet installed as a Composer package or wired, since doing so productively requires a real DSN to test against; installing an inert SDK would not be genuine, verified work |
| Database / storage monitoring | Prepared, not subscribed | Blueprint's own recommendation (hosting platform's basic dashboard metrics) unchanged — no hosting platform exists yet to have a dashboard |
| Alert thresholds | Prepared | Same channel/threshold design already specified in the blueprint §O-04 — not re-decided here, since nothing new was learned that would change it |

---

# OP-05 — Backup & Disaster Recovery

**Status: Backup mechanism executed and verified for real, against the real local database. Restore drill prepared but not executed end-to-end — blocked on a real credential gap, disclosed rather than worked around.**

| Deliverable | State | Evidence |
|---|---|---|
| Database backup schedule | **Executed and verified** | `app/Console/Commands/BackupDatabase.php` — a real `mysqldump` wrapper (not a stub), with 14-daily/6-monthly retention pruning matching the blueprint's policy. **Run for real** against the local `slipguard` database: produced `slipguard-2026-08-04_024148.sql.gz` (21.6 KB). **All 23 tables' row counts cross-checked against the live database with zero mismatches**, using an unambiguous per-row `--skip-extended-insert` count (a first, cruder verification method produced a false alarm on one table — caught and corrected before trusting it, see the command's own git history/commit message). Wired into `routes/console.php` via `Schedule::command(...)->dailyAt('02:00')` — confirmed registered via `php artisan schedule:list`. Refuses to run against a non-MySQL connection (tested: 1/1 passing). |
| File backup strategy | Substantially resolved by OP-03's `FILESYSTEM_DISK=s3` recommendation (unchanged from the blueprint) | — |
| Restore procedure | **Prepared, not executed end-to-end** | `ops/restore-database.sh` — real, syntax-checked script following the exact disposable-database pattern `I-01.2` already established and verified once before. **Cannot be run to completion in this environment**: the application's `slipguard` MySQL user does not have `CREATE DATABASE` privilege (confirmed directly — `ERROR 1044 (42000): Access denied`), and no root/admin credential is available here. This is the same wall `PO-MVP-005`'s own Database Audit already disclosed, not a new gap. |
| Recovery verification | **Not performed** — depends on the restore procedure above | A real restore drill requires a database admin account (real production root/admin access, or local root credentials this session was not given) to run `ops/restore-database.sh` to completion. **This is the one OP-05 acceptance criterion ("successful documented restore test") this programme did not close.** |

---

# OP-06 — Production Validation

**Status: Executed and verified, within what this environment can genuinely exercise.**

| Deliverable | State | Evidence |
|---|---|---|
| Deployment rehearsal | Not performed against a real server (none exists) | `ops/deploy.sh` is real and complete but untested end-to-end — see OP-02 |
| Smoke testing (functional) | **Executed and verified** | `app/Console/Commands/VerifyDeployment.php` — codifies the manual login→create-slip→analyse→view-report check `I-01.2` and `PO-MVP-005`'s UX audit each did by hand into a repeatable, safe, transaction-rolled-back command. **Run for real**: first attempt genuinely failed (`availability: unavailable`) because the test data used the betting-slip factory's random `sport` field, which can produce Basketball/Tennis — but the deterministic engine's taxonomy is football-only (the same fact `U-18.4.2`'s architecture proposal already found). Fixed to use the same known-good football fixture data `tests/Feature/Analysis/AnalyzeBettingSlipTest.php` already establishes. Second run passed: real slip created → analysed (`availability: full`, `structural_score: 22`) → **verified zero residue** by querying the live database directly afterward (0 matching test users, unchanged `betting_slips`/`slip_analyses` counts). 1/1 automated test passing. |
| Smoke testing (HTTP layer) | **Executed and verified** | `ops/smoke-test.sh` — run for real against a live local `php artisan serve` instance: `/up`, `/`, `/login`, `/analyse` all returned 200. |
| Rollback rehearsal | Not performed against a real server (none exists) | `ops/rollback.sh` is real and syntax-checked, not exercised end-to-end |
| Backup restore rehearsal | Not performed | Same credential gap as OP-05 |

**Real finding worth keeping, not just a footnote:** `VerifyDeployment`'s first failure is a genuine, useful signal — it independently reconfirms, from a completely different angle, exactly what `U-18.4.2`'s Reference Data Model proposal already found about the football-only taxonomy. Two unrelated pieces of work landed on the same real constraint.

---

# OP-07 — Production Readiness Certificate

## Completed checklist

| Item | Status |
|---|---|
| Production server provisioning script | ✅ Prepared |
| Nginx / PHP-FPM / firewall configuration | ✅ Prepared (Nginx config not locally syntax-validated) |
| Deployment pipeline (script + CI/CD) | ✅ Prepared, YAML-validated |
| Rollback procedure | ✅ Prepared |
| Production environment configuration template | ✅ Prepared |
| Extended health check (DB connectivity) | ✅ **Executed and verified** |
| Uptime/error/queue/storage monitoring | ⬜ Prepared only — no real account exists |
| Database backup mechanism | ✅ **Executed and verified** |
| Backup retention policy | ✅ **Executed and verified** (pruning logic real, tested against real files) |
| Restore procedure (script) | ✅ Prepared |
| Restore procedure (actually executed) | ❌ **Not done** — blocked on admin DB credentials |
| Functional deployment verification | ✅ **Executed and verified** |
| HTTP-layer deployment verification | ✅ **Executed and verified** |
| Real production server | ❌ **Does not exist** |
| Real domain / DNS / SSL | ❌ **Does not exist** |

## Remaining operational risks

1. **No production environment exists.** Every artifact above is ready to run the moment one does; none of it substitutes for actually having one. **Critical, blocking.**
2. **No real restore drill has been completed.** The mechanism is proven (backup ↔ real data, verified); the recovery half is not. **Critical, blocking** — a backup nobody has ever restored is not a verified backup, per the blueprint's own standard.
3. **No real monitoring account exists.** A production incident would still be invisible until reported, exactly as `PO-MVP-005` found. **Critical, blocking.**
4. **Nginx config is unvalidated syntax.** Low likelihood of a real error (it's a standard, unexceptional block), but genuinely unverified. **Minor**, resolve with `nginx -t` before first real use.
5. **CI/CD secrets don't exist anywhere.** The pipeline is real code with no way to run yet. **Major**, blocks the deployment pipeline specifically, not the rest of this checklist.

## Production recommendation

**NO GO for a real production launch today.** This is not a verdict on the quality of what was prepared — six of seven OP work packages produced real, tested, or carefully-validated artifacts, and OP-06's functional/HTTP smoke tests are genuinely proven, working tools, not aspirational descriptions. The verdict is simple and unavoidable: **there is no server to launch to.** Every remaining blocker is "provision the real thing and run the real thing against it" — none require more design, more planning, or another blueprint.

**Path to GO WITH CONDITIONS**, in order: (1) provision a real server using `ops/provision-server.sh` as the starting point — a real hosting/vendor decision Product Office confirms first, per the original blueprint's own note; (2) run `ops/deploy.sh` for a real first deploy, which exercises OP-06's smoke tests against a real target for the first time; (3) subscribe to a real monitoring service and wire the prepared `SENTRY_LARAVEL_DSN` placeholder; (4) obtain a real database admin credential and run `ops/restore-database.sh` to completion at least once; (5) configure the CI/CD secrets so `.github/workflows/deploy.yml` can actually run.

None of these five steps require returning to Operations Office for further planning — everything needed to execute each one already exists in this repository.

---

*Verification summary for this programme as a whole: full Pest suite green (687/694 executed passing, 7 self-skip by design, 0 failures) after every change; `git diff --check` clean; production frontend build clean; Pint clean on every touched file; every shell script passes `bash -n`; the GitHub Actions workflow passes real YAML parsing. Where this programme says "executed and verified," that means a real command was actually run in this environment and its real output is quoted above — not implied, not assumed.*
