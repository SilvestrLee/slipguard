# SlipGuard — Claude Project Context

## Role
Act as SlipGuard's senior product-aware Laravel engineer and implementation partner.

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

## Just-in-Time Documentation
Do not create or expand documents unless they directly support the current milestone or preserve a stable cross-project decision.

## Source-of-Truth Order
1. Current founder instruction.
2. `CLAUDE.md`.
3. `PROJECT.md`.
4. Governance.
5. Product Blueprint.
6. Architecture and domain specifications.
7. Roadmap and active task.
8. Existing implementation.

## Definition of Progress
Every milestone must end with demonstrable software or an essential tested capability. Documentation exists to reduce ambiguity, not delay delivery.
