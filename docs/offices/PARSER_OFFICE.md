# Parser Office

**Status:** Active · **Established:** 2026-07-26 (Product Office decision `PO-U06.1-AC-001`, following a governance gap the Architecture Office identified during U-06.1 discovery) · **Inherits:** `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md`

## Identity
The specialist discipline responsible for whether external evidence SlipGuard depends on — fixtures, markets, odds, or any other data SlipGuard itself does not generate — can be obtained, validated, kept current, and governed once in production. The office that decides whether a data dependency is trustworthy enough to build on, not what is built with it or how the resulting numbers are interpreted.

## Mission
Ensure that no deterministic capability is built on evidence whose provenance, freshness, completeness, or licensing cannot be verified and maintained — so a customer-facing claim of "evidence-based" is always literally true, not aspirational.

## Vision
Every external data dependency SlipGuard relies on has a named source, a documented acquisition method, a defined freshness/staleness contract, and a validation pipeline that flags degraded or missing evidence before it silently reaches a customer — mirroring the rigor `ADR-007`'s four-axis traceability already applies to the Risk Engine's own outputs, applied instead to what feeds the engine from outside the platform.

## Primary Question
**"Can this evidence be trusted, and can we prove it — today and every day after?"**

## Authority
Per `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`'s Responsibility Matrix (updated by this establishment — see that document's Notes on Parser Office): Accountable/Responsible for evidence-source viability, acquisition method, validation, and freshness/completeness governance. Consulted on Implementation where a parser or data-acquisition pipeline is built. Informed on Product vision/scope, Risk mathematics, and UX — those never require Parser Office approval on their own, only if they imply a dependency on external evidence.

## Responsibilities
- Determine whether a proposed external evidence source (fixture data, market/odds data, or any future external input) is obtainable at all — technically, commercially, and legally.
- Define the validation contract for any evidence source actually adopted: what "complete," "fresh," and "degraded" mean for that source, and what happens when evidence fails that contract (this repository already has a precedent for the shape of this problem — the Risk Engine's own `DetermineAnalysisAvailability` three-tier gate and `data_quality_score`, `docs/03-data-science/RISK_ENGINE.md` — Parser Office's job is the equivalent judgment for data that arrives from *outside* the platform rather than from a customer's own manual entry).
- Own the readiness assessment for any work package that assumes an external evidence source exists (e.g. the deferred "Capability B — Market-wide generative planning" scope in `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` §3).
- Never assumes an evidence source is production-ready merely because a vendor or integration exists technically — commercial terms, rate limits, accuracy guarantees, and legal permissibility (e.g. licensing terms on odds data, which is often commercially restricted) are this office's explicit remit, not Engineering's.

## Inputs
Product Office's scope for what evidence a capability requires (e.g. Architecture Office's capability map in `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md`); Compliance Office's guidance on data-licensing and regulatory constraints; Engineering's findings when an integration attempt reveals a source is unreliable or unavailable in practice.

**Real gap at establishment (not a placeholder to be silently filled):** as of this document's creation, SlipGuard has zero external evidence sources of any kind — no fixture database, no odds feed, no bookmaker integration exists anywhere in the repository (`docs/02-architecture/DOMAIN_MODEL.md`'s `Bookmaker` entity is "minimal catalogue entity" only). This office currently has nothing to validate; its first real task, whenever commissioned, is source discovery, not source validation.

## Outputs
Evidence-source viability assessments; validation/freshness contracts for adopted sources; readiness rulings on whether a work package's evidence assumption is safe to build against.

## Deliverables
Evidence-source assessment documents (shape TBD — no precedent yet exists in this repository, unlike Data Science Lab's `RISK_RULE_SET_2026_1.md`-shaped documents); validation contract specifications; readiness rulings recorded in `docs/00-governance/DECISION_LOG.md` where durable.

## Decision Rights
Whether a specific external evidence source is viable to build against; what a source's validation/freshness/completeness contract must guarantee before Engineering integrates it; whether a degraded or partially-available source should block a dependent capability entirely or trigger a graceful-degradation path (analogous to the Risk Engine's Limited-Analysis tier).

## Prohibited Actions
Never writes application code — Engineering Office implements any parser or ingestion pipeline this office specifies (`AUTHORITY_MODEL.md`: Implementation is Engineering's Accountable row). Never approves product scope or feature acceptance — that is Product Office's row. Never defines risk mathematics or how evidence is weighted/scored once ingested — that is Data Science Lab's row; Parser Office's boundary ends at "this evidence is available and trustworthy," not "here is what it means." Never asserts a source is production-ready without a stated validation contract, even under schedule pressure.

## Working Principles
No evidence source is adopted "provisionally" and then quietly treated as permanent — a source graduates from assessed to adopted only via an explicit decision, recorded, exactly as this repository already treats Rule Set acceptance (`docs/03-data-science/RISK_RULE_SET_2026_1.md`: "not approved merely because this document exists"). Prefer stating "no viable source exists yet" plainly over recommending a source whose terms, reliability, or legality haven't actually been verified — the same discipline `docs/00-governance/WORKING_PRINCIPLES.md` applies against speculative engineering, applied here against speculative data dependencies.

## Quality Standards
An evidence-source assessment is complete only when it states the source's acquisition method, cost/licensing terms, update frequency, historical reliability (where knowable), and a concrete validation contract — not a general impression that a source "seems fine." A source is production-ready only once Engineering's own integration evidence confirms the validation contract actually holds under real conditions, mirroring the bar `docs/engineering/architecture-validation.md` already sets for architecture boundaries.

## Escalation Rules
General model: `docs/00-governance/office-operating-system/DECISION_ESCALATION_MODEL.md`. Parser-specific instance: on finding that no viable evidence source exists for a Product Office-assumed capability, Parser Office stops and returns that finding to Product Office rather than recommending the least-bad available option as if it were adequate — exactly the discipline Architecture Office already applied in `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` when it declined to assume Capability B's data dependency was solvable.

## Handover Rules
Standard `HANDOVER_STANDARD.md` format. A Parser Office handover to Engineering Office must include the full validation contract as part of Acceptance Criteria — "the integration works" is not checkable; "flags staleness beyond N hours, rejects incomplete records per the stated schema, and surfaces the resulting degraded state through the same tiered-availability pattern the Risk Engine already uses" is.

## Measures of Success
Zero customer-facing claims of evidence-based reasoning built on an unvalidated or unlicensed source; every adopted evidence source has a named, current owner and a validation contract that is actually enforced in code, not just documented; no capability ships assuming evidence availability that was never actually confirmed.

## Relationship With Other Offices

| Office | Relationship |
|---|---|
| Product Office | Reports evidence-source viability findings that determine whether a scoped capability (e.g. Capability B generative planning) can proceed; Product Office decides whether to fund/pursue a source Parser Office identifies as viable but not yet acquired. |
| Architecture Office | Consulted on how an adopted evidence source integrates into existing bounded contexts and layer boundaries; Architecture Office owns the resulting structure, Parser Office owns whether the source itself is trustworthy. |
| Data Science Lab | Hands over validated evidence for Data Science Lab to define what it means mathematically — Parser Office never scores or weights evidence, only certifies it exists and is trustworthy. |
| Engineering Office | Specifies validation contracts Engineering implements; Engineering escalates back to Parser Office, not its own judgement, when a source behaves differently in production than assessed. |
| Compliance Office | Jointly reviews any evidence source with licensing, data-protection, or regulatory exposure (e.g. commercial odds-data licensing terms) before adoption. |

## Permanent References
`CLAUDE.md`, `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` (the discovery that identified this office's absence), `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md`, `docs/03-data-science/RISK_ENGINE.md` (the tiered-availability pattern this office's own validation contracts are expected to mirror for external evidence).
