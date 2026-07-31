# SlipGuard Project

## Status
- Engineering initialization: complete.
- Framework: Laravel 13.
- Active branch: `develop`.
- Business functionality: implemented and evolving — see `TASKS.md`'s "Current Phase" section for the present milestone and `docs/00-governance/REPOSITORY_STATE.md`/`DECISION_LOG.md` for governance history. This line intentionally points elsewhere rather than naming a milestone directly, after `G-03.1` found the previous static snapshot ("Current milestone: E-02 Customer Foundation") had gone stale by roughly forty delivered work packages.
- Repository knowledge system: SGOS v1.0.

## Vision
Build the world's most trusted betting decision intelligence platform (`SD-001`, 2026-07-26 — corrected here per `G-03.1`'s finding that this line still read the pre-`SD-001` "betting risk intelligence platform" phrasing `CLAUDE.md`'s own Product Identity section had already been updated to replace).

## Mission
Help bettors identify unnecessary risk before placing a bet through deterministic, transparent, and understandable analysis.

## MVP Outcome
A user can register, enter a slip manually, receive an explainable risk report, identify the weakest leg, save the analysis, review history, and record a simple journal decision.

## MVP Non-Goals
Bet code/share-link retrieval, bookmaker APIs, customer account linking, email/WhatsApp/clipboard import, browser extensions, automatic bet placement, outcome prediction, social features, referrals, advanced subscriptions, broad multilingual support, microservices, and native mobile apps.

**"OCR, bookmaker parsing" narrowed 2026-07-27** (`docs/00-governance/DECISION_LOG.md`, `PO-U11.5A-001`, SD-001) — converting a customer's own uploaded screenshot or PDF of their own slip into a structured, customer-reviewable draft (via OCR text extraction and parsing into the existing Manual Slip Builder) is now an approved MVP capability, Programme U-11.5A, always subject to mandatory customer review and confirmation before analysis — no field is ever submitted for analysis without the customer's explicit sign-off, and no value is ever fabricated or guessed. This is narrower than the phrase it replaces: it approves reading the customer's own already-possessed artefact, not any form of bookmaker-side integration — bet code/share-link retrieval, bookmaker APIs, and account linking remain excluded, as listed above. **Strategic authority only — this entry does not itself commission Engineering implementation.** Architecture Office, Parser Office (OCR/PDF backend viability and validation contract), and Compliance Office (upload handling, retention, third-party data-sharing exposure) must each complete their own review and hand over to Engineering, per the Office Operating System — mirroring how every other genuinely new capability in this repository (Planner, Operations Console) was sequenced.

**"Safe accumulator generation" removed 2026-07-26** (`docs/00-governance/DECISION_LOG.md`, SD-001) — deterministic, explainable, fully customer-editable accumulator planning is now an approved capability (Programme U-06). This is narrower than the phrase it replaces: outcome prediction and "safe bet" claims remain permanently excluded (`CLAUDE.md`'s Locked Decisions); what's newly approved is planning *assistance*, not a guarantee of safety.

**SlipGuard Labs approved 2026-07-26** (`docs/00-governance/DECISION_LOG.md`, SD-002, Programme U-13) — a customer-facing product-transparency area (feature awareness, roadmap status, "Notify Me," "Join Beta"), strategically approved and included in MVP scope in this narrow form only. It does not reopen the non-goals above: bookmaker voting, language voting, and any Smart Bet Transfer or bookmaker-integration implementation are explicitly deferred, not approved, by the same decision. Strategic authority only — no Engineering work is commissioned by SD-002 itself.

## SGOS Rule
Only core documents are populated now. Deferred areas remain lightweight until implementation requires them.

## Office Operating System
`docs/00-governance/office-operating-system/` — active since 2026-07-25 (Governance Programme G-02). The permanent cross-office governance framework: who decides what (`AUTHORITY_MODEL.md`'s Responsibility Matrix), when an office stops and escalates (`DECISION_ESCALATION_MODEL.md`), and the standard shape every office handover follows (`HANDOVER_STANDARD.md`). Every office's own standing constitution lives under `docs/offices/` (`PRODUCT_OFFICE.md`, `ARCHITECTURE_OFFICE.md`, `ENGINEERING_OFFICE.md`, `UX_STUDIO.md`, `DATA_SCIENCE_LAB.md`, `COMPLIANCE_OFFICE.md`, `PARSER_OFFICE.md`). `PARSER_OFFICE.md` was added 2026-07-26 (`docs/00-governance/DECISION_LOG.md`, `PO-U06.1-AC-001`) — owns external evidence-source viability and validation, distinct from Architecture/Data Science/Engineering.

## Engineering Constitution
`docs/00-governance/ENGINEERING_CONSTITUTION.md` — active since 2026-07-25, following Production Foundation certification (commit `18d1c4c`). Engineering Office's own specific authority boundary and behaviour-change policy — the Engineering-scoped instance of the Office Operating System above, unchanged and still in force. Defines the Behaviour Change Policy (stop/document/escalate on ambiguity), Certified Architecture Preservation, and the Foundation Freeze list. Read it before making any change that touches product behaviour, risk mathematics, or architecture — not just before frontend work.

## UX Foundation Documents
`docs/05-ux/` defines SlipGuard's permanent design language — required reading before any frontend implementation: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `HOMEPAGE_STORYBOARD.md`, `DESIGN_TOKENS.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, and `UX_RULES.md`. Every document carries a version/status/owner header and cross-references its related documents. See `CLAUDE.md`'s Required Reading and Frontend Work Rule (including its Gap Rule: extend the documentation and get Product Office approval before inventing UX in code).

## Approved Tooling Policy
`TOOL-UX-001` (2026-07-25, see `TASKS.md`) verified and scoped two design-research tools: **UI UX Pro Max** (Claude Code plugin) and **21st.dev MCP** (`.mcp.json`). Both are approved for design reference/inspiration only — never as a source of committed code or assets, since 21st.dev serves React/shadcn install commands and SlipGuard's customer UI is locked to Blade+Livewire (`CLAUDE.md` Locked Decisions). Full policy: `CLAUDE.md`'s Tool Invocation Policy. Figma MCP is connected under the same reference-only constraint; Indeed MCP is present in the environment but out of scope for SlipGuard and not approved for use here.

**Mandatory for qualifying customer-facing design work, 2026-07-27** (`docs/00-governance/DECISION_LOG.md`, `PO-GOV-UX-001`, consolidated and extended by `PO-GOV-UX-002`) — invoking these two tools is no longer merely permitted but required, where available, before finalizing significant design decisions on the public website, customer application, design system, components, or general UX/visual work; qualifying commissions also conclude with browser verification where a browser is available. Full workflow and reporting requirement: `CLAUDE.md`'s Mandatory Specialist Design Capability Invocation section.
