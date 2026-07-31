# Delivery Roadmap

## E-01 — Engineering Initialization
**Status:** Complete

Laravel 13, Git/GitHub, Pest, Vite, Claude Code, local environment, and `develop` branch.

## E-02 — Customer Foundation
**Outcome:** A user can register, sign in, access a branded workspace, and see a useful empty dashboard.

Scope: authentication review, customer layout, compact navigation, dashboard empty state, profile basics, authorization conventions, minimal Filament access, and tests.

## E-03 — Manual Slip Capture
**Outcome:** A user can create and validate a multi-leg slip manually.

## E-04 — Deterministic Risk Analysis
**Outcome:** A valid slip produces a versioned explainable risk result.

Blocked until formulas and test vectors are approved.

## E-05 — Risk Report
**Outcome:** A user understands the result and what to review.

## U-04 — Customer Workspace & Analysis Management
**Outcome:** A user revisits analyses and records decisions or reflections, through a coherent customer workspace.

**Naming note:** formerly "E-06 — History and Journal" (`docs/00-governance/DECISION_LOG.md`, 2026-07-26). History and Journal remain the milestone's core capabilities — the rename reflects the Product Office's decision to frame them as components of a broader customer workspace, not a scope change. Search, filtering, tags, collections, archive management, bulk actions, favourites, smart organisation, AI-generated summaries, AI-assisted journaling, social sharing, collaboration, notifications, and workspace automation are explicitly deferred, pending separate Product Office approval.

## U-06 — Intelligent Accumulator Planning
**Outcome:** A customer can get deterministic, fully explainable, editable help constructing an accumulator — never an unexplained tip, never a guarantee.

**Status:** Strategic decision recorded (SD-001, 2026-07-26). U-06.1 — Architecture Discovery — delivered and **Product Office accepted** (`PO-U06.1-AC-001`, 2026-07-26; `docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md`), formally scoping the programme to **Capability A** (deterministic customer-assisted planning) only. **U-06.2A — the Data Science Lab's weakest-leg mathematics (Marginal Structural Contribution model) — delivered and accepted** (`PO-U06.2A-AC-001`), and **its engine-layer implementation (E-06D.1, `App\Domain\Risk\Engine\RankLegsByStructuralWeakness`) delivered** — see `TASKS.md`. **Programme U-07 (Planner Orchestration) — U-07.1 Architecture delivered**, pending Product Office review (`docs/02-architecture/U-07.1-PLANNER-ORCHESTRATION-ARCHITECTURE.md`; proposed `docs/adr/ADR-009-PLANNER-LIFECYCLE-AND-REGENERATION-BOUNDARY.md`), resolving the regeneration/persistence question below for Capability A. U-06.3 (data availability — relevant only if/when Capability B is later authorized) and U-06.4 (UX) have not begun; U-06.4 should not begin until U-07.1's Open Decisions OD-1–OD-3 (stopping/abandonment messaging policy) are resolved.

**Resolved since this section was last written:** (1) the Capability A/B scope fork — resolved to Capability A only, Capability B's data-acquisition question deferred to a future, separately-scoped programme; (2) "Regenerate" being blocked by `ADR-007`'s re-analysis gap — resolved for Capability A (`ADR-009`): planner regeneration never touches `SlipAnalysis`/`Analysed`, so that gap does not apply to it; (3) the "Parser Office" naming gap — resolved, established as a seventh standing office (`docs/offices/PARSER_OFFICE.md`, `PO-U06.1-AC-001`).

**Still open, not yet authorized:** Capability B (generative planning from a live fixture/odds/market universe) remains unauthorized and out of scope — no data source exists, and none of the above resolves that. Product/compliance policy on planner stopping, suitability, and abandonment messaging (`docs/02-architecture/U-07.2-PLANNER-PRODUCT-DECISION-REGISTER.md`'s DR-01, Compliance assessment delivered, not yet decided) remains undecided.

## U-13 — SlipGuard Labs & Product Evolution Platform
**Outcome:** A customer can discover selected future SlipGuard capabilities, understand why they matter, and register interest ("Notify Me" / "Join Beta") to inform future roadmap prioritisation.

**Status:** **Delivered, Product Office accepted, and constitutionally Closed** (`docs/00-governance/DECISION_LOG.md`, `PO-U13-AC-001`, 2026-07-26). Strategic decision `SD-002` → Engineering implementation → UX Studio certification (found and Engineering remediated one critical, cross-cutting badge-rendering defect) → Product Office acceptance, the full lifecycle completed. No Labs feature content has been published yet — that is a separate, later editorial decision through the delivered admin panel. Any future enhancement to SlipGuard Labs requires new Product Office commissioning; no further work proceeds under this programme.

**Explicitly deferred, not approved for MVP:** bookmaker voting, language voting, feature-request submission, community discussions, roadmap comments, email campaigns, notification automation, roadmap analytics, forums, referral programmes, bookmaker promotion, and any Smart Bet Transfer or bookmaker-integration implementation — all recorded in SD-002 itself, not merely assumed. A separate, explicitly-scoped Engineering handover referencing SD-002 is required before any of the approved MVP scope may be implemented.

## E-07 — Public Trust Website
**Outcome:** A prospective user understands SlipGuard and can register.

## E-08 — MVP Hardening
**Outcome:** The complete journey is secure, reliable, accessible, observable, and beta-ready.

## Release 1.0
Controlled MVP launch.
