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
SlipGuard is a betting risk intelligence platform. It evaluates betting slips and exposes unnecessary risk. It does not predict match winners, provide tips, promise safe bets, or encourage more betting.

## Locked Decisions
- No outcome prediction.
- No “safe bet” or guaranteed-win claims.
- Risk calculations are deterministic and explainable.
- AI may explain verified findings only.
- Customer UI uses Blade and Livewire.
- Internal operations use Filament.
- One Laravel application and one primary database.
- No microservices without a proven need.
- Build progressively and avoid speculative complexity.

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
Before implementing Blade, Livewire, Filament, Tailwind, CSS, icons, illustrations, animations, typography, spacing, interactions, or responsive behaviour — review the applicable `docs/05-ux/` UX Constitution documents: `DESIGN_LANGUAGE.md`, `VISUAL_INSPIRATION.md`, `MOTION_SYSTEM.md`, `COMPONENT_PRINCIPLES.md`, `DESIGN_TOKENS.md`, `HOMEPAGE_STORYBOARD.md`, `EXPLAINABILITY_SYSTEM.md`, `EMPTY_STATES.md`, `TRUST_SIGNALS.md`, `ACCESSIBILITY.md`, `ICONOGRAPHY.md`, `IMAGE_GUIDELINES.md`, `RESPONSIVE_RULES.md`, and `UX_RULES.md`. Implementation follows documentation; documentation does not follow implementation. If an implementation choice conflicts with these documents, the documents take precedence.

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

## Just-in-Time Documentation
Do not create or expand documents unless they directly support the current milestone or preserve a stable cross-project decision.

## Source-of-Truth Order
1. Current founder instruction.
2. `CLAUDE.md`.
3. `PROJECT.md`.
4. Governance — including `docs/00-governance/ENGINEERING_CONSTITUTION.md` for authority boundaries and behaviour-change policy.
5. Product Blueprint.
6. Architecture and domain specifications.
7. Roadmap and active task.
8. Existing implementation.

## Definition of Progress
Every milestone must end with demonstrable software or an essential tested capability. Documentation exists to reduce ambiguity, not delay delivery.
