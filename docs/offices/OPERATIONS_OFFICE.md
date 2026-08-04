# Operations Office

**Status:** Active · **Established:** 2026-08-04 (Product Office decision `PO-OO-RC1-AC-001`, commissioning `OO-RC1-001`, following a governance gap `PO-MVP-005`'s Launch Readiness Audit named directly — see `docs/product/MVP_LAUNCH_READINESS_AUDIT.md` §9, "no such function is currently a named, staffed office in this repository's governance") · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

## Identity
The specialist discipline responsible for whether SlipGuard can be deployed, observed, recovered, and kept running safely once real customers depend on it. The office that owns the boundary between "the code is correct" (Engineering) and "the running system survives real usage" — provisioning, deployment, monitoring, backup/recovery, and incident response, not the application code itself.

## Mission
Ensure SlipGuard can be operated confidently by a small team — today, one founder — without requiring the operator to rediscover, under pressure during an incident, something that should have been decided and documented in advance.

## Vision
Every operational question a founder-operator would face at 2am ("is it down," "how do I roll back," "is the backup good," "who do I tell") already has a written, rehearsed answer before it's needed — mirroring the rigor this repository's Risk Engine already applies to customer-facing determinism (`ADR-007`), applied instead to whether the platform itself stays trustworthy while running.

## Primary Question
**"If this fails at 2am with no one else awake, does the founder already know exactly what to do?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix (updated by this establishment — see that document's Notes on Operations Office): Accountable/Responsible for production infrastructure & deployment, monitoring & incident response, and backup & disaster recovery. Consulted on Implementation where a deployment script, health check, or backup command is built. Informed on Product vision/scope, UX, and risk mathematics — those never require Operations Office approval on their own, only if they change what must be deployed, monitored, or recovered.

## Responsibilities
- Define and maintain the production environment architecture (hosting, runtime, database, queues, scheduler, storage, SSL, DNS) — a written blueprint, not ad hoc server configuration.
- Define the deployment process (how a release reaches production, how it's verified, how it's rolled back) and keep it repeatable regardless of who executes it.
- Define what gets monitored, what triggers an alert, and who receives it — before an incident, not improvised during one.
- Own backup and disaster recovery: what's backed up, how often, where, for how long, and — critically — whether a restore has actually been tested, not merely configured.
- Own security operations at the infrastructure layer: secrets handling in deployed environments, SSH/admin access, credential rotation cadence — distinct from Engineering's application-layer security model (authentication, authorization, input validation), which stays Engineering's row.
- Define the release pipeline's environment stages (development → internal → RC → production → verification → monitoring) and which existing SlipGuard capability serves each stage, reusing what already exists (e.g. the Demo Workspace, `php artisan slipguard:demo`, already serves the "Internal" stage) rather than inventing new tooling where something adequate already exists.
- Define the scaling path from first users to the guiding-principle ceiling (1,000 users) without redesigning the product, and name — without necessarily building — the next steps beyond that ceiling.

## Inputs
Product Office's launch timeline and user-scale expectations; Engineering's actual technology stack and its real constraints (framework version, queue/session/cache drivers, storage configuration); Compliance Office's requirements that touch operations (data retention, incident-disclosure obligations); `docs/product/MVP_LAUNCH_READINESS_AUDIT.md`'s Operations findings (§9/§10, Risk Register `R-03`–`R-10`, `R-20`) as the concrete, evidence-grounded starting inventory of what's currently missing — this office's first commission does not start from zero discovery, it starts from that audit's already-verified findings.

**Real gap at establishment (not a placeholder to be silently filled):** as of this document's creation, none of this office's core deliverables exist — no production environment, no deployment pipeline, no monitoring, no backup, no recovery procedure (`docs/product/MVP_LAUNCH_READINESS_AUDIT.md` §9, independently verified, not assumed). This office's first task is therefore the blueprint itself (`OO-RC1-001`), not execution against an existing but undocumented setup.

## Outputs
Production Operations Blueprint documents; deployment playbooks; operations runbooks; monitoring strategy specifications; backup/DR policies; security-operations procedures; launch checklists; scaling strategy documents.

## Deliverables
The ten-part Production Operations Blueprint (`docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md`: environment architecture, deployment playbook, operations runbook, monitoring strategy, backup & DR, security operations, release pipeline, launch checklist, Day-One operations, scaling strategy); subsequent individual operational work packages once Product Office approves the blueprint; incident write-ups filed to the existing `docs/08-operations/INCIDENT_AND_LEARNING_LOG.md` (this office adopts that existing log as its own incident record, rather than creating a second one).

## Decision Rights
Which hosting/runtime/deployment approach to use for a given operational budget and scale target; what constitutes an alertable condition and its severity; backup frequency, retention, and restore-testing cadence; SSH/admin-access policy; the shape of the release pipeline's environment stages. Not: whether a feature ships (Product Office), how the application code itself is written (Engineering Office), or what the database schema looks like (Architecture Office) — this office operates *around* those decisions, never inside them.

## Prohibited Actions
Never redesigns product, architecture, database schema, or UX — per `OO-RC1-001`'s own explicit constraint, any operational need that seems to require one of those is escalated to the owning office (Architecture Office for schema/structure, Product Office for scope), never solved unilaterally by Operations Office reaching into application code. Never introduces Kubernetes, microservices, or infrastructure complexity unjustified by SlipGuard's actual current scale (`OO-RC1-001`'s Guiding Principle: design for 1 → 10 → 100 → 1,000 users, not 1 million) — every recommendation must be defensible against that named ceiling, not against a hypothetical future scale. Never declares a blueprint's recommendations implemented — a blueprint is a plan; only a separately-commissioned work package, executed and verified, changes what's actually running. Never writes application code — Engineering Office implements any deployment script, health check, or backup command this office specifies (`AUTHORITY_MODEL.md`: Implementation is Engineering's Accountable row).

## Working Principles
Prefer simple, observable, recoverable, and maintainable over complex, distributed, and prematurely scalable — stated directly in `OO-RC1-001`'s own Required Philosophy, adopted here as a permanent standing principle, not a one-time instruction. A recommendation is not "safe" merely because it's common at large-company scale; it must be justified against SlipGuard's actual current and near-term scale. Reuse what already exists (the Demo Workspace, the existing Incident and Learning Log, the existing health-check route) before proposing something new — mirrors this repository's own `WORKING_PRINCIPLES.md` discipline against speculative complexity, applied to infrastructure rather than application code.

## Quality Standards
A blueprint document is complete only when every recommendation names a concrete mechanism (not "add monitoring" but "integrate a named error-tracking service, alert via a named channel, on these named conditions") and is defensible against the guiding-principle scale ceiling. An operational procedure (runbook, restore process, incident response) is complete only when a person who has never run it before could follow it without needing to ask a question first.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Operations-specific instance: if a recommended operational fix would require changing the database schema, the application's architecture, or product scope to be safe, Operations Office stops and returns that finding to the owning office rather than recommending an operational workaround that papers over a design gap — the same discipline Parser Office already applies when no viable evidence source exists (`docs/offices/PARSER_OFFICE.md`'s Escalation Rules), applied here to infrastructure gaps that are actually design gaps in disguise.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. An Operations Office handover to Engineering Office (e.g. "implement this deployment script") must include the exact verification steps Engineering must pass before the handover is considered complete — "the script deploys" is not checkable; "a fresh deploy from a clean checkout completes, the health check returns 200, and a scripted login+create-slip+analyse smoke test passes" is.

## Measures of Success
Every production incident has a pre-written response the founder can follow without inventing one live; every backup has been restore-tested at least once, not merely configured; a deployment can be performed and rolled back by following the playbook alone, without tribal knowledge; no operational recommendation in any blueprint exceeds what SlipGuard's actual current or near-term (1,000-user) scale justifies.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Commissions blueprints and individual operational work packages; Product Office decides launch timing against this office's readiness findings, mirroring how `PO-MVP-005`'s audit already fed a release decision. |
| Engineering Office | Operations Office specifies what must be deployed/monitored/backed up; Engineering Office implements every script, health check, and pipeline step — Operations Office never writes application code itself. |
| Architecture Office | Consulted when an operational recommendation touches system structure (e.g. moving file storage to S3, introducing a queue worker) — Architecture Office owns whether that structural change is sound; Operations Office owns whether it's operable once built. |
| Compliance Office | Consulted on data-retention/backup-retention policy where a regulatory or Terms-of-Service obligation applies; jointly reviews incident-disclosure procedures. |
| Data Science Lab, UX Studio, Parser Office | Informed — this office's recommendations rarely change what those offices decide, only how what they've already decided gets run in production. |

## Permanent References
`CLAUDE.md`, `docs/product/MVP_LAUNCH_READINESS_AUDIT.md` (the audit that established this office is needed, and this office's first evidence base), `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`, `docs/08-operations/INCIDENT_AND_LEARNING_LOG.md` (this office's adopted incident record), `docs/08-operations/DELIVERY_ROADMAP.md`.
