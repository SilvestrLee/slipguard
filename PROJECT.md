# SlipGuard Project

## Status
- Engineering initialization: complete.
- Framework: Laravel 13.
- Active branch: `develop`.
- Business functionality: not yet implemented.
- Current milestone: E-02 Customer Foundation.
- Repository knowledge system: SGOS v1.0.

## Vision
Build the world’s most trusted betting risk intelligence platform.

## Mission
Help bettors identify unnecessary risk before placing a bet through deterministic, transparent, and understandable analysis.

## MVP Outcome
A user can register, enter a slip manually, receive an explainable risk report, identify the weakest leg, save the analysis, review history, and record a simple journal decision.

## MVP Non-Goals
OCR, bookmaker parsing, outcome prediction, safe accumulator generation, social features, referrals, advanced subscriptions, broad multilingual support, microservices, and native mobile apps.

## SGOS Rule
Only core documents are populated now. Deferred areas remain lightweight until implementation requires them.

## Engineering Constitution
`docs/00-governance/ENGINEERING_CONSTITUTION.md` — active since 2026-07-25, following Production Foundation certification (commit `18d1c4c`). Defines the Authority Matrix (who decides what: Product Office, Data Science Lab, Architecture Office, Engineering Office, UX Studio), the Behaviour Change Policy (stop/document/escalate on ambiguity), Certified Architecture Preservation, and the Foundation Freeze list. Read it before making any change that touches product behaviour, risk mathematics, or architecture — not just before frontend work.

## UX Foundation Documents
`docs/05-ux/` defines SlipGuard's permanent design language — required reading before any frontend implementation: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `HOMEPAGE_STORYBOARD.md`, `DESIGN_TOKENS.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, and `UX_RULES.md`. Every document carries a version/status/owner header and cross-references its related documents. See `CLAUDE.md`'s Required Reading and Frontend Work Rule (including its Gap Rule: extend the documentation and get Product Office approval before inventing UX in code).
