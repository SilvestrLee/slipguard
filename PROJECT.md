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

## Office Operating System
`docs/00-governance/office-operating-system/` — active since 2026-07-25 (Governance Programme G-02). The permanent cross-office governance framework: who decides what (`AUTHORITY_MODEL.md`'s Responsibility Matrix), when an office stops and escalates (`DECISION_ESCALATION_MODEL.md`), and the standard shape every office handover follows (`HANDOVER_STANDARD.md`). Every office's own standing constitution lives under `docs/offices/` (`PRODUCT_OFFICE.md`, `ARCHITECTURE_OFFICE.md`, `ENGINEERING_OFFICE.md`, `UX_STUDIO.md`, `DATA_SCIENCE_LAB.md`, `COMPLIANCE_OFFICE.md`).

## Engineering Constitution
`docs/00-governance/ENGINEERING_CONSTITUTION.md` — active since 2026-07-25, following Production Foundation certification (commit `18d1c4c`). Engineering Office's own specific authority boundary and behaviour-change policy — the Engineering-scoped instance of the Office Operating System above, unchanged and still in force. Defines the Behaviour Change Policy (stop/document/escalate on ambiguity), Certified Architecture Preservation, and the Foundation Freeze list. Read it before making any change that touches product behaviour, risk mathematics, or architecture — not just before frontend work.

## UX Foundation Documents
`docs/05-ux/` defines SlipGuard's permanent design language — required reading before any frontend implementation: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `HOMEPAGE_STORYBOARD.md`, `DESIGN_TOKENS.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, and `UX_RULES.md`. Every document carries a version/status/owner header and cross-references its related documents. See `CLAUDE.md`'s Required Reading and Frontend Work Rule (including its Gap Rule: extend the documentation and get Product Office approval before inventing UX in code).

## Approved Tooling Policy
`TOOL-UX-001` (2026-07-25, see `TASKS.md`) verified and scoped two design-research tools: **UI UX Pro Max** (Claude Code plugin) and **21st.dev MCP** (`.mcp.json`). Both are approved for design reference/inspiration only — never as a source of committed code or assets, since 21st.dev serves React/shadcn install commands and SlipGuard's customer UI is locked to Blade+Livewire (`CLAUDE.md` Locked Decisions). Full policy: `CLAUDE.md`'s Tool Invocation Policy. Figma MCP is connected under the same reference-only constraint; Indeed MCP is present in the environment but out of scope for SlipGuard and not approved for use here.
