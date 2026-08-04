# SlipGuard Production Operations Blueprint

| Field | Value |
|---|---|
| Commission | `OO-RC1-001`, Operations Office, Critical priority, requested by Product Office, direct founder instruction, 2026-08-04 |
| Type | **Blueprint only.** No implementation, no infrastructure changes. Individual operational work packages are commissioned separately, after Product Office approval of this document. |
| Owner | Operations Office (`docs/offices/OPERATIONS_OFFICE.md`, established by this same commission) |
| Grounding | Builds directly on `docs/product/MVP_LAUNCH_READINESS_AUDIT.md`'s Operations, Database, and Security findings (`R-03`–`R-10`, `R-20`) rather than re-discovering them — that audit found no production environment, deployment pipeline, monitoring, backup, or recovery procedure exist anywhere in this repository. This document answers "what should exist," not "does it exist" — that question is already answered. |
| Status | Delivered. Awaiting Product Office approval. |

---

# Executive Objective

SlipGuard has reached engineering maturity — `PO-MVP-005`'s audit confirmed the application layer itself (features, tests, security, determinism) is genuinely strong. This document defines how SlipGuard survives its first real users: not "how do we deploy Laravel," but "how does a single founder run this safely, every day, and recover when something breaks."

**Guiding principle, binding throughout:** design for 1 founder → 10 users → 100 → 1,000, not 1 million. Every recommendation below is sized to that ceiling. Where a larger-scale pattern (Kubernetes, microservices, Redis/Horizon, managed clustering) would ordinarily appear in a generic production blueprint, it is named explicitly as **out of scope now** and located on the Scaling Strategy (§O-10) instead — not silently omitted, not silently included.

**What this repository already has, reused rather than reinvented:**
- A real, working Demo Workspace (`php artisan slipguard:demo`, `SLIPGUARD_DEMO_ENABLED` flag) — serves the Release Pipeline's "Internal" stage directly (§O-07).
- A real, working health-check route (`/up`, Laravel's framework default).
- A real, working Incident and Learning Log (`docs/08-operations/INCIDENT_AND_LEARNING_LOG.md`) — adopted as this office's incident record (§O-06/§O-09), not replaced.
- A real audit trail (`RecordAuditEvent`, `OperationsAuditLogEntry`) already verified genuine and wired, per `PO-MVP-005` §7.6 — no new work needed there.
- Laravel 13 / PHP `^8.3`, MySQL, Filament 4, Livewire — a single deployable modular monolith (`ADR-001`), nothing distributed to coordinate.

---

# O-01 — Production Environment Architecture

## Hosting
**Recommendation: a single, well-specified VPS managed through a Laravel-specific server-management layer** (e.g. Laravel Forge or Ploi, over Hetzner or DigitalOcean) rather than a hand-rolled server or a container orchestration platform. This is the standard, well-understood pattern for exactly this scale — it gives a solo founder a web UI for the things that would otherwise require deep sysadmin expertise (Nginx config, PHP-FPM pools, SSL renewal, deploy scripts, queue worker supervision) without introducing Kubernetes or a distributed system to operate. **This is a real cost/vendor decision Product Office should confirm before any work package executes it** — not made unilaterally here; the alternative (a managed Laravel PaaS such as Laravel Cloud, Fly.io, or Render) is a legitimate second option, trading some cost/control for less operational surface, and is named as the fallback if the founder prefers not to manage even a Forge-layer VPS.

## Web server
Nginx (the default under Forge/Ploi-managed Laravel deployments) — proven, well-documented, zero exotic configuration needed for a Livewire/Blade monolith.

## PHP runtime
PHP 8.3-FPM, matching `composer.json`'s `"php": "^8.3"` requirement exactly. **Binding requirement, directly from `PO-MVP-005` finding R-10:** production `memory_limit` must be set to **at least 512M** (the audit found the platform-default 128M insufficient to even run this application's own test suite — a real, not theoretical, risk). `opcache` enabled with `validate_timestamps=0` in production (standard Forge/Laravel practice; requires an explicit `opcache:clear` or restart step in the deploy playbook, §O-02).

## MySQL
A single, well-resourced MySQL 8.0 instance — self-hosted on the same VPS for the earliest stage (simplest, cheapest, matches 1–100 users easily) or a managed MySQL instance (DigitalOcean Managed Databases, or equivalent) once backup/failover convenience outweighs the added cost — this is a §O-10 scaling decision, not a Day-One requirement. No read replicas, no clustering at this scale.

## Queues
`QUEUE_CONNECTION=database` is the current default and is **adequate as-is** — direct code search found **no queued jobs exist anywhere in this application today** (`app/Jobs` is empty, no `ShouldQueue` usage found). A `queue:work` process under Supervisor is therefore not a Day-One requirement, but the Supervisor configuration should be written and ready (§O-02) since it's a near-certain near-term need — PDF/screenshot parsing or Capability B's candidate-discovery calls are natural first candidates for queuing to keep requests responsive, whenever that work is actually commissioned. Do not introduce Redis/Horizon now — that's a §O-10 upgrade, justified only once real queue volume exists.

## Scheduler
`routes/console.php` currently defines no scheduled commands beyond Laravel's default `inspire` — there is nothing to schedule today beyond what this blueprint itself introduces (the backup command, §O-05). A single cron entry (`* * * * * php artisan schedule:run`) is standard and sufficient; no additional scheduler infrastructure needed at this scale.

## Storage
`FILESYSTEM_DISK=local` today (screenshots/PDFs stored on local disk). **Recommendation: move production file storage to the `s3` disk already configured in `config/filesystems.php`** (S3-compatible object storage — AWS S3, DigitalOcean Spaces, or Cloudflare R2 are all viable; R2 has no egress fees, worth preferring at this scale) — this directly serves the Backup & DR requirement (§O-05): object storage is durable by default, whereas a single VPS's local disk is a single point of failure for every customer's uploaded slip. This is a configuration change (env vars + credentials), not a code change — `config/filesystems.php`'s `s3` disk already exists and needs no new code.

## SSL
Let's Encrypt, auto-renewed by Forge/Ploi (or the PaaS equivalent) — this fully resolves the "SSL expiry" monitoring concern (§O-04) by construction rather than requiring a manual watch.

## DNS
The domain registrar plus Cloudflare (free tier) in front, proxied — gets basic DDoS mitigation and caching of static assets essentially for free, without being a dedicated CDN commitment.

## CDN
**Not required at this scale.** Cloudflare's incidental caching (above) is sufficient for a mostly server-rendered Livewire application with a modest compiled asset bundle. A dedicated CDN is a §O-10 item, not a Day-One one.

## Environment separation
Two real environments: **Production** (the live site) and a lightweight **RC/staging** environment — a second, minimal site on the same VPS (or a second cheap VPS once budget allows) running the release-candidate branch before promotion. **Development** is the founder's local machine (already fully functional per this repository's own `docs/06-engineering/DATABASE_SETUP.md`). **Internal** is the existing Demo Workspace — already built, already gated, reused rather than duplicated (see Executive Objective, above). No third "test" tier beyond these — matches the guiding principle.

---

# O-02 — Deployment Playbook

## Step-by-step deployment
1. Merge to the release branch (per `git-workflow` conventions already in use in this repository — `main` for production).
2. Trigger deploy (Forge/Ploi's own git-push-to-deploy hook, or a manually-triggered deploy button — either is adequate at this scale; a GitHub Actions-triggered deploy is a reasonable §O-07 refinement, not a Day-One requirement).
3. Deploy script, in order: `composer install --no-dev --optimize-autoloader` → `php artisan migrate --force` → `php artisan config:cache` → `php artisan route:cache` → `php artisan view:cache` → `php artisan event:cache` → `npm ci && npm run build` (or pre-built assets uploaded, if build happens in CI) → `php artisan queue:restart` (safe no-op today since no workers run yet; becomes load-bearing the moment queued jobs are introduced) → `sudo service php8.3-fpm reload` (picks up the new opcache).
4. **Release verification** (§"Release verification" below) runs automatically as the final deploy step — a failed verification should be treated as a failed deploy, not silently ignored.

## Rollback
Because this is a single-server, git-based deploy (not a container image), rollback is: re-deploy the previous known-good commit through the same script above. **Binding requirement:** any deploy that includes a database migration must have its rollback path considered *before* deploying — either the migration is safely reversible (`php artisan migrate:rollback` for that batch) or the deploy is treated as one-way and rollback means restoring from backup (§O-05) instead of a code rollback. This decision must be made per-release, not assumed.

## Zero downtime where practical
Full zero-downtime deployment (blue-green, atomic symlink swaps) is more infrastructure than this scale justifies today — Forge/Ploi's standard "release into a new directory, atomic symlink switch" deploy pattern already gets *near*-zero downtime (a few hundred milliseconds at symlink-swap time) without any additional complexity. True zero-downtime (load-balanced blue-green) is a §O-10 item once traffic volume makes a sub-second gap actually matter.

## Environment variables
Managed through the hosting platform's own secrets/env editor (Forge's environment editor, or the PaaS equivalent) — never as a plaintext file committed anywhere, consistent with this repository's own already-verified practice (`PO-MVP-005` §7.4 — no leaked secret found, `.env` never tracked). `.env.example` remains the source of truth for *which* variables exist and their safe local defaults; production values are set once, per environment, through the platform's own secret storage.

## Release verification
A scripted, repeatable smoke test — not a manual click-through — run automatically as the last deploy step: (1) `GET /up` returns 200 (existing health check); (2) a scripted login as a known test account; (3) a scripted create-slip → analyse → view-report cycle, mirroring exactly what `docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md` and this audit's own UX/Intelligence pass already did manually via Playwright — codified as a repeatable script instead of a one-off manual check, run against the real production database read-only wherever possible (or against a disposable test account created and torn down as part of the script). A failed verification should trigger an automatic rollback per the Rollback section above, not merely an alert.

---

# O-03 — Operations Runbook

## Daily
Glance at the monitoring dashboard (§O-04) — error rate, disk usage, health-check status. Nothing else requires daily manual action once §O-04/§O-05 are in place; that's the point of automating them.

## Weekly
Review the error-tracking service's issue list (new/recurring errors); confirm the previous week's automated backups completed (§O-05) — a backup job that silently stopped running is worse than no backup, because it creates false confidence.

## Monthly
Review disk usage trend (is it growing faster than expected — e.g. uploaded screenshots accumulating); review the `failed_jobs` table once queued jobs exist; rotate any credential due for rotation per the schedule in §O-06; confirm SSL/domain auto-renewal actually fired (Let's Encrypt renews ~30 days before expiry — a monthly glance catches a silent renewal failure with a comfortable margin).

## Quarterly (or per major release, whichever is sooner)
A real, scripted **restore drill** (§O-05) — restore the latest backup into a genuinely disposable database and confirm the application boots against it, mirroring exactly this repository's own already-established disposable-database verification method (`docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md`). A backup that has never been restored is unverified, not a real backup.

## Database maintenance
MySQL's own routine maintenance (`ANALYZE TABLE` periodically for the highest-write tables — `slip_analyses`, `betting_slips`, `market_intelligence_market_quotes` — is standard practice, not urgent at this row-count scale). No manual intervention needed beyond that at 1,000-user scale.

## Log review
Weekly, as part of the error-tracking review above — the two are the same activity once §O-04's log aggregation exists, not a separate task.

## Backups / Updates / Certificates / Queue monitoring
See §O-05 (Backups), §O-04 (Monitoring, covers queue/certificate monitoring), and the Deployment Playbook (§O-02, covers dependency/framework updates via the normal deploy process — no separate "update runbook" needed since updates are just ordinary deploys of a `composer.json`/`package.json` change).

---

# O-04 — Monitoring Strategy

| Concern | Recommendation | Rationale |
|---|---|---|
| Health checks | Extend `/up` beyond Laravel's framework default to verify real database connectivity specifically (a custom health-check closure checking `DB::connection()->getPdo()`) — directly resolves `PO-MVP-005` finding **R-20** (the current endpoint is present but shallow). | A "healthy" response that doesn't check the database isn't meaningfully healthy for this application. |
| Error reporting / application monitoring | Integrate a real error-tracking service — **Sentry** (broadly standard, generous free tier at this scale) or **Laravel Flare** (first-party Laravel integration, slightly tighter framework fit). Either directly resolves `PO-MVP-005` **R-05**. | Currently zero error visibility exists — an incident is invisible until a customer reports it (the audit's own finding). |
| Queue monitoring | **Not Horizon** (Redis-only, unjustified complexity while `QUEUE_CONNECTION=database` and zero jobs exist). A simple scheduled command checking `failed_jobs` row count and oldest-pending-job age, alerting via the same channel as error reporting if either crosses a threshold. | Matches the guiding principle — Horizon becomes justified only once real queue volume exists (§O-10), not before. |
| Database monitoring | The hosting platform's own basic metrics (Forge/DO/Hetzner dashboards typically include CPU/memory/disk/connections at no extra cost) are sufficient at this scale — no dedicated APM needed yet. | Avoids paying for/operating a monitoring stack sized for a scale SlipGuard isn't at. |
| Disk monitoring | Same platform-dashboard alerting, plus a simple scheduled `df`-based check if the platform doesn't alert on disk directly. | Local disk usage matters most before the S3 storage migration (§O-01) lands; becomes lower-priority afterward. |
| SSL expiry | Resolved by construction — Let's Encrypt auto-renewal (§O-01) removes this as a manual watch item entirely. | The classic "founder forgot" failure mode is eliminated by automation, not a calendar reminder. |
| Domain expiry | Registrar auto-renewal enabled, plus the monthly runbook glance (§O-03) as a backstop. | Cheap insurance against the other classic "founder forgot" failure mode — a domain-expiry outage is entirely preventable. |
| Alerting | Route error-tracking alerts and the queue/backup checks above to a single channel the founder actually monitors (email is adequate at solo-founder scale; a Slack/Discord webhook is a low-cost upgrade once more than one person needs to see alerts). | One channel, always checked, beats multiple channels that get ignored. |

---

# O-05 — Backup & Disaster Recovery

## Database backup
Automated daily `mysqldump` (or the managed-MySQL provider's own automated backup, if that path is chosen per §O-01/§O-10), shipped to the same S3-compatible object storage recommended for file uploads (§O-01) — never left only on the source server, which would defeat the purpose of backing up at all. Scheduled via `routes/console.php` + the standard Laravel scheduler cron entry (§O-01), directly resolving `PO-MVP-005` finding **R-06**.

## Uploads backup
Substantially resolved by the §O-01 recommendation to move `FILESYSTEM_DISK` to S3-compatible storage in production — object storage is durable by design (typically 99.999999999%-class durability), which is a stronger guarantee than any backup script could add on top of a local disk. If local disk storage is kept for any reason, it must be included in the same backup routine as the database.

## Restore testing
A real, scripted quarterly restore drill (§O-03) — restore the latest backup into a genuinely disposable database (never the production database) and confirm the application boots and a real query returns correct data, using this repository's own already-established disposable-database method. **An untested backup is not a verified backup** — this drill is what turns "we have backups" into a claim that's actually been checked.

## Recovery procedure
A written runbook (to be produced as part of the first Operations work package this blueprint authorizes, not invented ad hoc during a real incident): identify the failure, stop write traffic if the database itself is compromised, restore the most recent clean backup to a fresh instance, verify via the same smoke test used in Release Verification (§O-02), repoint the application, confirm, then write up the incident in `docs/08-operations/INCIDENT_AND_LEARNING_LOG.md` (already exists, adopted rather than duplicated). Directly resolves `PO-MVP-005` finding **R-07**.

## Recovery objectives
- **RPO (Recovery Point Objective): ≤24 hours** — matches the daily backup cadence above. Revisit toward hourly only if/when transaction volume genuinely makes a day of lost data unacceptable (a §O-10 scaling trigger, not a Day-One requirement).
- **RTO (Recovery Time Objective): same business day**, achievable by a single founder manually following the written recovery procedure — not an automated failover, which isn't justified at this scale.

## Retention policy
14 daily backups + 6 monthly backups retained (standard, inexpensive lean-SaaS retention shape) — enough to recover from both a sudden failure and a slower-discovered data-corruption issue, without unbounded storage growth.

---

# O-06 — Security Operations

## Secrets
Never as plaintext files — the hosting platform's own environment/secrets editor (§O-02) is the single source of truth for production credentials. This repository's application-layer secrets hygiene is already independently verified clean (`PO-MVP-005` §7.4 — `.env` never tracked, no leaked credential found anywhere) — this section extends that same discipline to the deployed environment, it doesn't need to fix anything already broken.

## Admin access
The Filament `/operations` panel is already correctly gated (`is_internal` boolean, `PO-MVP-005` §7.3, confirmed genuinely enforced) — Operations Office's addition is process, not code: keep the list of real `is_internal` accounts to the minimum needed (today: the founder, plus any trusted staff added later) and review that list periodically (folded into the Monthly runbook, §O-03).

## SSH
Key-based authentication only, password authentication disabled at the server level — Forge/Ploi's default posture; no code change required, a server-provisioning setting.

## Password rotation
Application customer passwords are already correctly hashed (bcrypt via Laravel's default) — no action needed there. This section covers **infrastructure/service credentials specifically**: the-odds-api key, any AWS/S3 credentials, database passwords — rotate on a light, defined cadence (recommend annually, or immediately on any suspected exposure) rather than never, which is this repository's own prior practice (`I-01.3` already rotated the local MySQL password once for exactly this reason).

## Audit logging
Already real and correctly wired (`RecordAuditEvent`, `OperationsAuditLogEntry`, independently confirmed genuine in `PO-MVP-005` §7.6, not a stub) — **no new work needed here**, named explicitly so a future work package doesn't duplicate something that already exists.

## Incident response
A lightweight, written runbook: who's paged (today: the founder, solo), where the alert lands (§O-04's single channel), first diagnostic steps (check the health check, check the error tracker, check the database), escalation trigger (data-loss or security-breach suspicion → jump straight to the Recovery Procedure, §O-05), and a mandatory post-incident write-up filed to `docs/08-operations/INCIDENT_AND_LEARNING_LOG.md` — this office adopts that existing, already-real log as its permanent incident record rather than creating a competing one.

---

# O-07 — Release Pipeline

```
Development           → the founder's local machine (already fully functional,
                          docs/06-engineering/DATABASE_SETUP.md)
        ↓
Internal               → the existing Demo Workspace
                          (php artisan slipguard:demo, SLIPGUARD_DEMO_ENABLED) —
                          reused, not duplicated
        ↓
RC (Release Candidate)  → a lightweight staging site (§O-01, Environment separation)
        ↓
Production              → the live site
        ↓
Verification             → the scripted smoke test (§O-02, Release verification),
                            run automatically, not manually
        ↓
Monitoring                → §O-04, continuous from this point forward
```

Promotion between stages is a deploy of the same artifact (the deploy script, §O-02) targeting a different environment — no separate build process per stage, which would risk exactly the "works in staging, breaks in production because it was built differently" failure mode this pipeline exists to prevent.

---

# O-08 — Launch Checklist

Consolidates `PO-MVP-005`'s own Launch Blockers (§12 of that document) into the items this blueprint's future work packages must close — **no new items invented here**, this section is a bridge between that audit and the work this blueprint authorizes, not a second, divergent list.

## Owned by Operations Office (this blueprint's own scope)
- [ ] Production environment provisioned (§O-01) — closes `R-03`
- [ ] Deployment pipeline built and exercised at least once (§O-02, §O-07) — closes `R-04`
- [ ] Monitoring/error-reporting integrated (§O-04) — closes `R-05`
- [ ] Automated backup running and restore-tested at least once (§O-05) — closes `R-06`, `R-07`
- [ ] Production log aggregation configured (§O-04) — closes the log-aggregation item from `PO-MVP-005` §9.3
- [ ] `SESSION_SECURE_COOKIE`, `APP_ENV=production`, `APP_DEBUG=false`, and `memory_limit≥512M` set and documented as a production `.env` checklist (§O-01, §O-02) — closes `R-09`, `R-10`
- [ ] `MARKET_WIDE_PLANNER_ENABLED` flip decision executed as part of the first production deploy, per `PO-MVP-004`'s existing authorization — closes `R-12`
- [ ] Health check extended to verify database connectivity — closes `R-20`

## Owned by other offices (named here as dependencies, not resolved by this blueprint)
- [ ] Real Terms of Service and Privacy Policy content — Compliance/Legal, closes `R-01a`/`R-01b`
- [ ] Responsible Gambling signposting determination — Compliance Office, closes `R-02`
- [ ] `MVP_SCOPE_LOCK.md`'s four remaining "Product Office Decision Required" items resolved — Product Office, closes `R-11`

**No production launch should proceed until every item above is checked**, per `PO-MVP-005`'s own NO-GO-today / GO-WITH-CONDITIONS-close finding — this blueprint is the concrete path from that finding to a real GO.

---

# O-09 — Day-One Operations

Builds directly on `PO-MVP-005` §14, now grounded in the concrete infrastructure §O-01–§O-07 define (that audit's version was necessarily abstract, since none of this existed yet).

- **Monitoring:** the error-tracking service (§O-04) live and alerting to the founder's single monitored channel from before launch, not configured on Day One itself.
- **Support:** a real, monitored contact channel exists before launch (even a single monitored inbox is adequate at this scale) — resolves the ambiguity `PO-MVP-005` flagged about `/contact`'s current stub status.
- **Rollback:** the Deployment Playbook's rollback path (§O-02) is rehearsed at least once *before* Day One, not discovered for the first time during a real incident.
- **Incident handling:** the Security Operations incident-response runbook (§O-06) is written and the founder has read it before Day One — not something to improvise.
- **Logging:** production log aggregation (§O-04) is live and the founder has confirmed logs are actually arriving, before real traffic starts.
- **User feedback:** no in-product feedback mechanism currently exists (consistent with `PO-MVP-005`'s finding) — recommend deciding whether Day One needs one or can rely on the support channel above; this is a Product Office scope call, not an Operations one.
- **Metrics:** decide what Day-One success looks like (signups, completed analyses, Capability B usage if its flag is enabled) before launch, and confirm it's actually measurable with what exists today (the Dashboard's own real counts, `PO-MVP-005` §4, are a real starting point) rather than reconstructing this after the fact.

---

# O-10 — Scaling Strategy

**Governing constraint: every step below is a resourcing or configuration change to the existing modular monolith (`ADR-001`) — none require a product, architecture, or database redesign, per this commission's own explicit constraint.**

## 100 → 1,000 users
Vertical scaling of the single VPS (more CPU/RAM) is the first and usually sufficient lever — a modern, modestly-sized VPS comfortably serves a mostly server-rendered Livewire application at this volume. If MySQL contention becomes the bottleneck before the app server does, extract it onto its own managed instance (§O-01 already names this as the natural first extraction, not a new idea introduced here). This range is squarely inside the guiding principle's own stated ceiling — no further changes should be needed to merely *reach* 1,000 users, only to comfortably serve them.

## 1,000 → 10,000 users
- Introduce Redis for cache/session/queue (session driver is already `database`, not `file` — already horizontally-scalable-friendly by accident of its current, correct configuration; Redis is a performance upgrade at this point, not a correctness fix).
- **Now** Laravel Horizon becomes justified for queue monitoring (§O-04's simple scheduled check was explicitly scoped to "until real queue volume exists" — this is that trigger).
- Horizontal scaling of stateless app servers behind a load balancer, once a single (even upgraded) VPS is genuinely the bottleneck — the application already has no server-local state assumption beyond what the session/cache backend already externalizes, so this is an infrastructure addition, not an application rewrite.
- True zero-downtime blue-green deployment (§O-02 named this as deferred) becomes worth the added complexity here.
- A CDN (§O-01 named this as not required earlier) becomes worth adding once static-asset traffic volume justifies it.

## Beyond 10,000 users
Explicitly **not designed here** — out of scope for this blueprint's own guiding principle, and premature to design against a scale SlipGuard has no evidence of approaching. Read replicas, multi-region deployment, and any container-orchestration platform belong at this tier if it's ever reached, not before — naming them here only to be explicit that they were considered and deliberately deferred, not overlooked.

---

*This document is Operations Office's blueprint deliverable. Per `OO-RC1-001`'s own explicit constraint, it authorizes no implementation and no infrastructure change — individual operational work packages (provisioning the environment, building the deploy script, integrating monitoring, etc.) are commissioned separately, only after Product Office approves this blueprint.*
