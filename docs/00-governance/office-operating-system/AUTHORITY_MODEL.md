# Authority Model

**Status:** Active · **Effective:** 2026-07-25 · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

Defines who decides, who approves, who implements, who reviews, and who releases — across every SlipGuard office — and how conflicts between offices resolve. Individual office constitutions (`docs/offices/`) reference this document for cross-office questions rather than each defining their own version.

## The Five Ownership Types

| Type | Meaning |
|---|---|
| **Decision ownership** | Which office may originate/change the substance of a decision within its domain (e.g. a risk-factor weight, a layer boundary, a piece of copy). |
| **Approval ownership** | Which office must sign off before a decision takes effect — may differ from decision ownership (e.g. Data Science Lab decides a formula; Product Office jointly approves it before it's Accepted, per the existing Rule Set 2026.1 precedent). |
| **Implementation ownership** | Which office turns an approved decision into working software. Always Engineering Office, without exception — no other office writes or modifies application code. |
| **Review ownership** | Which office checks a delivered piece of work against its own domain's standard (e.g. Architecture Office reviews boundary integrity; Compliance Office reviews regulatory defensibility) — distinct from Approval, which is the final go/no-go. |
| **Release ownership** | Which office(s) must agree before customer-facing work ships. |

## Decision Precedence

When two sources of authority conflict, resolve using `CLAUDE.md`'s existing Source-of-Truth Order — this model does not redefine it, only makes explicit that office authority sits *inside* it:

1. Current founder instruction.
2. `CLAUDE.md`.
3. `PROJECT.md`.
4. Governance (including this Authority Model and every office constitution).
5. Product Blueprint.
6. Architecture and domain specifications.
7. Roadmap and active task.
8. Existing implementation.

A lower-precedence source (e.g. an office constitution) can never override a higher one (e.g. a direct founder instruction, or a Locked entry in `docs/00-governance/DECISION_LOG.md`).

## Conflict Resolution

1. The two (or more) offices attempt to resolve the disagreement directly, citing their own constitutions' Decision Rights.
2. If unresolved, either office escalates to the Accountable office for that decision type (Responsibility Matrix, below) — usually, but not always, Product Office.
3. The Accountable office decides. The decision is recorded (`docs/00-governance/DECISION_LOG.md` for anything durable) and both offices proceed under it.
4. Neither office may act unilaterally while a conflict is open — this is the organisational equivalent of Engineering's existing "stop, document, escalate, await decision" discipline (`DECISION_ESCALATION_MODEL.md`).

## Cross-Office Dependencies (typical chains)

These are descriptive — the common paths work already follows — not new process:

- **New risk mathematics:** Data Science Lab proposes → Product Office + Data Science Lab jointly approve → Engineering Office implements → Engineering Office's own tests validate determinism → Architecture Office confirms the boundary (engine stays pure) → Product Office approves release.
- **New customer-facing screen:** Product Office defines the objective → UX Studio executes the experience against the UX Constitution (extending it first if a pattern is missing, per the Gap Rule) → Engineering Office implements against persisted data only → Compliance Office reviews any claims/copy touching guardrails → Product Office approves.
- **Architecture change:** Architecture Office proposes (or Engineering Office identifies a need and raises it to Architecture Office) → Architecture Office decides and records an ADR → Product Office is informed (and approves if it affects product-facing behaviour or cost) → Engineering Office implements.

## Responsibility Matrix (RACI)

**R**esponsible (does the work) · **A**ccountable (owns the decision, answers for it — exactly one per row) · **C**onsulted (input sought before deciding) · **I**nformed (told after deciding)

| Decision Type | Product Office | Architecture Office | Engineering Office | UX Studio | Data Science Lab | Compliance Office | Parser Office | Operations Office |
|---|---|---|---|---|---|---|---|---|
| Product vision & scope | **A/R** | C | I | C | I | C | I | I |
| Feature acceptance | **A/R** | C | C | C | C | C | C | I |
| Risk mathematics / formulas / weights / thresholds | A | I | I | I | **A/R** | I | I | I |
| Rule Set approval (formal acceptance) | **A** | I | I | I | **A** | I | I | I |
| System architecture / layer boundaries | I | **A/R** | C | I | I | I | I | C |
| External evidence-source viability, acquisition & validation contracts | I | C | C | I | C | C | **A/R** | I |
| Implementation (all application code) | I | C | **A/R** | C | C | I | C | C |
| UX strategy (what the experience should achieve) | **A** | I | I | R | I | I | I | I |
| UX execution (how a screen/flow is built) | C | I | C | **A/R** | I | I | I | I |
| Testing & quality assurance | I | C | **A/R** | I | C | I | C | C |
| Security & authorization model (application layer) | I | C | **A/R** | I | I | C | I | C |
| Regulatory / responsible-gambling claims & copy | A | I | I | C | I | **A/R** | I | I |
| Release approval | **A** | C | **R** | I | I | C | I | **R** |
| Governance documentation (this framework, ADRs, decision log) | **A** | R (ADRs) | R (engineering docs) | I | I | R (compliance docs) | R (evidence-source docs) | R (operations docs) |
| Production infrastructure & deployment | I | C | C | I | I | I | I | **A/R** |
| Monitoring & incident response | I | I | C | I | I | I | I | **A/R** |
| Backup & disaster recovery | I | I | C | I | I | C | I | **A/R** |

Two rows show two Accountable cells deliberately (Rule Set approval; regulatory claims) — these mirror decisions this repository already treats as requiring **joint** sign-off (e.g. `RISK_RULE_SET_2026_1.md`: "becomes Accepted only on explicit Product Office / Data Science sign-off"). Joint accountability is the one intentional exception to "exactly one Accountable office" and only applies where an existing precedent already established it.

## Notes on Parser Office

Established 2026-07-26 (`docs/00-governance/DECISION_LOG.md`, `PO-U06.1-AC-001`), following a governance gap the Architecture Office identified during U-06.1 discovery (`docs/02-architecture/U-06.1-ARCHITECTURE-DISCOVERY.md` §9, R5): prior U-06 correspondence referenced a "Parser Office" that did not exist among the offices this framework originally established. Parser Office owns whether external evidence (fixture, market, or odds data SlipGuard does not itself generate) is obtainable, trustworthy, and governable in production — distinct from Architecture (structure), Data Science Lab (what evidence means mathematically once validated), and Engineering (implementation). See `docs/offices/PARSER_OFFICE.md`.

## Notes on UX Studio's Position

Per the founder's clarification during U-02 review (`docs/00-governance/ENGINEERING_CONSTITUTION.md` §1's footnote): UX Studio operates as a specialist discipline *under* Product Office governance. Strategic ownership of UX stays with Product Office (why every `docs/05-ux/` document's header reads `Owner: Product Office`); UX Studio holds execution ownership. The matrix above reflects this split explicitly (UX strategy vs. UX execution are separate rows) rather than collapsing UX into a single ambiguous row.

## Notes on Compliance Office

This is the first governance document to formally establish Compliance Office as a standing office (previously, `docs/09-compliance/PRODUCT_GUARDRAILS.md` existed with no explicit owner field). Compliance Office's constitution (`docs/offices/COMPLIANCE_OFFICE.md`) now owns that document jointly with Product Office, consistent with the matrix above — this is a governance formalisation of existing content, not a new compliance requirement.

## Notes on Operations Office

Established 2026-08-04 (`docs/00-governance/DECISION_LOG.md`, `PO-OO-RC1-AC-001`), following a governance gap `docs/product/MVP_LAUNCH_READINESS_AUDIT.md`'s Operations Audit (§9) named directly while reviewing production readiness: no standing office owned deployment, monitoring, or backup/recovery, and every item in that section was found genuinely absent, not merely unassessed. Operations Office owns whether the running system survives real usage — distinct from Engineering (whether the code is correct) and Architecture (whether the system's structure is sound). Its scope is deliberately narrow: "Security & authorization model" above stays Engineering's application-layer row (authentication, authorization, input validation); Operations Office's own security remit is infrastructure-layer only (secrets in deployed environments, SSH/admin access, credential rotation) — Consulted on the application-layer row, Accountable for none of it. See `docs/offices/OPERATIONS_OFFICE.md`.
