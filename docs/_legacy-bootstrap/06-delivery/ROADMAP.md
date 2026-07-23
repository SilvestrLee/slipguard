# SlipGuard Release Roadmap

**Principle:** Every milestone ends with demonstrable software or an essential tested platform capability.

## E-01 — Engineering Initialization

**Status:** Complete

Delivered:

- Laravel 13 repository.
- Local environment.
- Git and GitHub.
- Pest.
- Vite.
- Claude Code.
- `develop` branch workflow.

## E-02 — Customer Foundation

**Outcome:** A user can register, sign in, access a branded workspace, and see a useful empty dashboard.

Scope:

- Authentication review and branding.
- Customer layout.
- Dashboard shell.
- Navigation.
- Profile basics.
- Authorization foundation.
- Initial Filament operations access.
- Tests.

Not included:

- Slip analysis logic.
- OCR.
- Payments.

## E-03 — Manual Slip Capture

**Outcome:** A user can create and validate a multi-leg betting slip manually.

Scope:

- Slip and leg models.
- Migrations.
- Create/edit interface.
- Odds validation and normalization.
- Draft state.
- Ownership policies.
- Tests.

## E-04 — Deterministic Risk Analysis

**Outcome:** A valid slip produces a versioned explainable risk result.

Dependency:

- Data Science-approved formula and test vectors.

Scope:

- Risk engine.
- Rule result structure.
- Overall score and band.
- Weakest-leg detection.
- Per-leg contributions.
- Persistence.
- Tests.

## E-05 — Risk Report

**Outcome:** A user can understand the result and decide what to review.

Scope:

- Report UI.
- Plain-language deterministic explanations.
- Risk drivers.
- Methodology disclosure.
- Save/revisit.
- Printable or shareable output only if low complexity.
- Tests.

## E-06 — History and Journal

**Outcome:** A user can revisit analyses and record a decision or reflection.

Scope:

- History list.
- Filters kept minimal.
- Journal entry.
- Decision state.
- Optional result and reflection.
- Tests.

## E-07 — Public Trust Website

**Outcome:** A prospective user understands SlipGuard and can register.

Scope:

- Homepage.
- How It Works.
- Why SlipGuard.
- Responsible Betting.
- Help.
- Essential SEO.
- Legal placeholders reviewed by Compliance.

## E-08 — MVP Hardening

**Outcome:** The complete journey is reliable enough for controlled beta use.

Scope:

- UX refinement.
- Accessibility.
- Security review.
- Performance.
- Error states.
- Logging and health check.
- Seeded demo data.
- Beta feedback mechanism.
- Deployment readiness.
- Regression tests.

## Release 1.0 — Controlled MVP Launch

Entry conditions:

- Full primary journey works.
- Critical tests pass.
- Formula approved.
- No prohibited claims.
- Operations can inspect users, slips, analyses, rules, and incidents.
- Product and Compliance sign-off.