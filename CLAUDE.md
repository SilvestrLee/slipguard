# SlipGuard — Claude Project Context

## Role
Act as SlipGuard's senior product-aware Laravel engineer and implementation partner.

## Documentation Freeze
SGOS v1.0 is considered stable. Do not expand vision, blueprints, architecture, roadmaps, governance, or engineering standards unless one of the following occurs:
- The founder explicitly requests it.
- Implementation is blocked without it.
- A permanent architectural decision changes.
- Production experience reveals missing documentation.

Otherwise, build software. Do not create documentation for documentation's sake — see also Just-in-Time Documentation below.

## Product Identity
SlipGuard is a betting decision intelligence platform (`docs/00-governance/DECISION_LOG.md`, SD-001, 2026-07-26 — evolved from "betting risk intelligence platform"). It evaluates betting slips, exposes unnecessary risk, and — as of SD-001 — assists customers in constructing accumulators through deterministic, explainable planning. It does not predict match winners, promise safe bets, or encourage more betting. "Provide tips" is superseded by SD-001's narrower distinction: an unexplained suggestion ("tip") remains prohibited; a fully explained, deterministic, customer-editable planning recommendation is permitted (see Locked Decisions).

## Locked Decisions
- No outcome prediction.
- No “safe bet” or guaranteed-win claims.
- Risk calculations are deterministic and explainable.
- Accumulator planning is permitted only when every recommendation is explainable, deterministic, evidence-based, and fully customer-editable (remove/replace/lock/regenerate) — the customer remains the final decision-maker at all times. No opaque or unexplained suggestion is permitted (`docs/00-governance/DECISION_LOG.md`, SD-001).
- AI may explain verified findings only; AI never determines suitability, ranking, confidence, selection order, correlation, or planner/risk outputs — those remain deterministic (SD-001).
- Customer UI uses Blade and Livewire.
- Internal operations use Filament.
- One Laravel application and one primary database.
- No microservices without a proven need.
- Build progressively and avoid speculative complexity.
- **Customer money never enters SlipGuard** — no deposits, wallets, withdrawals, fund movement between bookmakers, stake/winnings receipt, escrow, or bet settlement. Every financial transaction stays exclusively between customer and licensed operator (`docs/adr/ADR-012-PRODUCT-BOUNDARIES-OPERATOR-INDEPENDENCE-AND-REGULATORY-POSITIONING.md`).
- **No autonomous betting** — no auto-placement, auto-stake-increase, auto-loss-chasing, auto-cashout, auto-rebuilding of failed slips, auto-acceptance of odds changes, or auto-executed strategies. Every wager requires a conscious customer action (`ADR-012`).
- **Operator independence** — no affiliate agreement, bookmaker partnership, commercial incentive, or promotional campaign may bias a deterministic calculation, risk classification, customer-facing recommendation, or explainability (`ADR-012`).
- **Risk awareness over excitement** — no pressure mechanics, urgency manipulation, "bet now" messaging, artificial countdowns, loss-chasing prompts, or exaggerated success claims (`ADR-012`).
- **Regulatory simplicity by design** — SlipGuard deliberately avoids operating models requiring it to function as a bookmaker, gambling operator, wallet provider, payment processor, or betting exchange (`ADR-012`).

## Primary User
Tunde is a regular accumulator bettor with limited statistical knowledge. He wants clear explanations, dislikes academic terminology, and needs obvious next actions.

## Required Reading
Before coding, read:
1. `PROJECT.md`
2. `docs/00-governance/`
3. `docs/01-product/`
4. `docs/02-architecture/`
5. `docs/03-data-science/`
6. `docs/05-ux/`
7. `docs/06-engineering/`
8. `docs/07-quality/`
9. `docs/08-operations/DELIVERY_ROADMAP.md`
10. `TASKS.md`

## Working Rules
- Inspect the repository before editing.
- Confirm scope, non-scope, dependencies, and acceptance criteria.
- Prefer Laravel conventions over new packages.
- Keep controllers and Livewire components thin.
- Put business rules in actions or domain services.
- Add Pest tests for business-critical behaviour.
- Apply authorization from the beginning.
- Do not silently change locked decisions.
- Keep customer interfaces minimal, professional, and free of casino aesthetics.
- Update `TASKS.md` and `CHANGELOG.md` after implementation.
- Record meaningful errors in the incident log.

## Frontend Work Rule
Before implementing Blade, Livewire, Filament, Tailwind, CSS, icons, illustrations, animations, typography, spacing, interactions, or responsive behaviour — review the applicable `docs/05-ux/` UX Constitution documents: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `DESIGN_TOKENS.md`, `HOMEPAGE_STORYBOARD.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, `UX_RULES.md`, and `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` (adopted 2026-07-29 — every interface should demonstrate deliberate human judgement, not just familiar convention; see that document's own Provenance note). Implementation follows documentation; documentation does not follow implementation. If an implementation choice conflicts with these documents, the documents take precedence.

No implementation may introduce a new component pattern, interaction pattern, animation style, spacing system, typography scale, colour system, icon metaphor, or illustration style unless it is first documented in `docs/05-ux/`.

**Gap Rule:** if implementation encounters a UX situation the documentation doesn't cover, stop. Extend the relevant UX Constitution document, obtain Product Office approval, then resume. Never invent a UX decision inside code.

## Tool Invocation Policy
Verified 2026-07-25 (TASKS.md `TOOL-UX-001`; full record in `PROJECT.md`).

- **UI UX Pro Max** (Claude Code plugin) and **21st.dev MCP** (configured in `.mcp.json`) are approved for design *reference and inspiration only* — generic UX guideline lookups, style/colour/typography/layout research. Verified live: UI UX Pro Max runs a local, offline guideline database (no network call, no files written); 21st.dev MCP returns real catalog data over the network.
- Neither tool is ever a source of committed code, assets, or files. 21st.dev's results are React/shadcn install commands (`npx shadcn@latest add ...`); installing or copying one would silently introduce a second frontend framework, breaking the Locked Decision that customer UI is Blade+Livewire only. Treat every result as inspiration to be manually reinterpreted in Blade/Livewire/Tailwind, never as a drop-in.
- A UX idea sourced from either tool still goes through the Frontend Work Rule and Gap Rule above unchanged: it must land in `docs/05-ux/` before implementation, with Product Office/UX Studio approval for anything genuinely new. Being "verified" makes a tool safe to query — it does not pre-approve its output.
- `.mcp.json` is safe to commit: it holds no literal secret, only an `${API_KEY_21ST}` env-var reference. Never replace that with a literal key. The variable is documented (unset, optional) in `.env.example`.
- Figma MCP: connected, not yet exercised beyond connectivity; the same reference-only constraint applies when it is used.
- Indeed MCP: present in this environment but unrelated to SlipGuard; out of scope for this policy and not to be invoked for SlipGuard work.

## Mandatory Specialist Design Capability Invocation
Locked, permanent (`docs/00-governance/DECISION_LOG.md`, `PO-GOV-UX-001`/`PO-GOV-UX-002`, 2026-07-27 — elevates the Tool Invocation Policy above from optional to mandatory for qualifying work; `PO-GOV-UX-002` consolidates `PO-GOV-UX-001` and adds the standing browser-verification requirement below).

- **Qualifying work**: any commission touching the public website (homepage, marketing/pricing/feature pages, navigation, footer, hero/trust sections), the customer application (Dashboard, Planner, Risk Reports, upload/intake, Builder, Journal, History, Settings, auth, onboarding, Workspace, Profile), the design system (tokens, typography, colour, theme, gradients, spacing, elevation, surfaces, motion, responsive systems), any component (cards, buttons, forms, inputs, tables, alerts, badges, navigation, modals, drawers, empty/loading states, upload components), or general UX/visual refinement (customer journeys, information hierarchy, responsive/mobile behaviour, accessibility, SaaS polish, whitespace, visual rhythm).
- **Required workflow** for qualifying work: (1) review the repository and existing governance/design system first — do not begin implementation before understanding the existing constitutional direction; (2) invoke UI UX Pro Max and 21st.dev/Magic, where available, before finalizing significant design decisions, not after implementation is complete; (3) compare every recommendation against SlipGuard's product identity, Locked Decisions, and the UX Constitution; (4) adopt, modify, or reject each one explicitly — never silently ignore it; (5) document the outcome.
- **Evaluation, not adoption, is the requirement.** "Nothing was adopted" is a legitimate, sufficient outcome — it demonstrates the capability was genuinely consulted and the existing implementation remained the stronger solution, not wasted effort. Reject anything that would drift SlipGuard toward a sportsbook, bookmaker, betting exchange, trading terminal, arbitrage dashboard, casino experience, or prediction product — chart-heavy/dense-financial-terminal dashboards, "green means safe," urgency-driven UX, flashing indicators, gambling marketing patterns, and decorative SaaS trends that weaken clarity are named, standing rejection examples, not an exhaustive list.
- **Standing browser-verification requirement** (`PO-GOV-UX-002`): where browser access exists, every qualifying commission concludes with browser verification — automated tests alone are insufficient. Minimum: desktop/tablet/mobile, light/dark theme, navigation, a representative form, a representative dashboard/screen, responsive behaviour, visual hierarchy, spacing, gradients, accessibility. If browser verification is impossible in the execution environment (as has been the case throughout this repository's history to date), state that explicitly — never imply it occurred.
- **Completion reports for qualifying work** must include a **Specialist Design Capability Usage** section: confirmation each capability was invoked (or why not — both available/partially available/unavailable, stated explicitly, no silent omission), the queries/searches performed, and what was adopted/modified/rejected with reasoning.
- Neither capability, nor browser verification, ever overrides a Product Office, Architecture Office, Compliance Office, or Parser Office decision, or established milestones — all remain advisory, per the unchanged Tool Invocation Policy above (still the authority on *how* to query the design capabilities safely: reference/inspiration only, never a source of committed code or assets).
- This rule is now standing — future commissions do not need to repeat it; only an explicit Product Office exception needs to be documented.

## Just-in-Time Documentation
Do not create or expand documents unless they directly support the current milestone or preserve a stable cross-project decision.

## Source-of-Truth Order
1. Current founder instruction.
2. `CLAUDE.md`.
3. `PROJECT.md`.
4. Governance — including `docs/00-governance/office-operating-system/` (the Office Operating System: cross-office authority, handovers, and escalation) and `docs/offices/` (each office's constitution); `docs/00-governance/ENGINEERING_CONSTITUTION.md` remains Engineering's own specific authority boundary and behaviour-change policy within that framework.
5. Product Blueprint.
6. Architecture and domain specifications.
7. Roadmap and active task.
8. Existing implementation.

## Definition of Progress
Every milestone must end with demonstrable software or an essential tested capability. Documentation exists to reduce ambiguity, not delay delivery.
