> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY (see note below — this one's graduation bar is lower than everything else in this directory).** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation, yet. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# R-01.7 — Constitutional Cross-Reference Index

```text
Identifier:            R-01.7
Programme:             R-01 — Research & Incubation Framework
Scope:                 Technical (a navigation aid, not a product-identity or governance change)
Status:                Exploratory — content complete and verified; placement is the only
                       open question, see note below
Owner:                 Architecture Office (role, not a standing individual)
Product Office Decision: None
Repository Status:     None
```

## A note on why this one's different

Every other package in `docs/10-research-incubation/` stayed here because it needed real external evidence (a provider trial, counsel review, a genuine founder decision on scope) before it could honestly claim more than `Exploratory`. **This package doesn't have that problem.** It introduces no new principle, no new claim, nothing that needs checking against `ADR-012` or any Locked Decision — every row below points at a real file, verified to exist, with real content I've actually read this session. The only reason this sits in incubation rather than `docs/README.md` is the same placement discipline applied everywhere else this session: nothing gets written into real SGOS from this conversation without a separate, explicit instruction to do so — not because this content is risky, but because making a content-based exception here would blur a line that's been useful precisely for being simple. **If you want this committed for real, say so directly and I'll move it into `docs/README.md` or `docs/00-governance/` — this is a one-line ask away from real adoption, unlike anything else in this directory.**

## The index

| Topic | Primary Authority | Secondary References |
|---|---|---|
| Product identity, Locked Decisions | `CLAUDE.md` (Product Identity, Locked Decisions) | `docs/00-governance/SD-001-STRATEGIC-DECISION-REPORT.md`, `PROJECT.md` |
| Operator independence, customer agency, prohibited functionality | `docs/adr/ADR-012-PRODUCT-BOUNDARIES-OPERATOR-INDEPENDENCE-AND-REGULATORY-POSITIONING.md` | `docs/09-compliance/PRODUCT_GUARDRAILS.md` |
| Deterministic risk calculation | `docs/adr/ADR-002-DETERMINISTIC-RISK.md` | `docs/03-data-science/RISK_ENGINE.md`, `RISK_RULE_SET_2026_1.md` |
| Constrained AI explanation | `docs/adr/ADR-003-CONSTRAINED-AI.md` | `docs/05-ux/EXPLAINABILITY_SYSTEM.md` |
| Analysis persistence / engine boundary | `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md` | `docs/02-architecture/DOMAIN_MODEL.md` |
| Human-designed interaction philosophy | `docs/05-ux/HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` | `docs/05-ux/DESIGN_LANGUAGE.md` |
| Screen-level UX rules, Risk Report Hierarchy | `docs/05-ux/UX_RULES.md` | `docs/05-ux/EXPLAINABILITY_SYSTEM.md` |
| Reusable components, spacing per component | `docs/05-ux/COMPONENT_PRINCIPLES.md` | `docs/05-ux/DESIGN_TOKENS.md` |
| Motion, loading behaviour | `docs/05-ux/MOTION_SYSTEM.md` | `docs/05-ux/COMPONENT_PRINCIPLES.md` (Progress Indicators) |
| Accessibility | `docs/05-ux/ACCESSIBILITY.md` | `docs/05-ux/COMPONENT_PRINCIPLES.md` (per-component rules) |
| Responsive behaviour | `docs/05-ux/RESPONSIVE_RULES.md` | `docs/05-ux/COMPONENT_PRINCIPLES.md` |
| Empty states | `docs/05-ux/EMPTY_STATES.md` | `docs/05-ux/COMPONENT_PRINCIPLES.md` |
| Product blueprint, MVP scope | `docs/01-product/PRODUCT_BLUEPRINT.md` | `MVP_DEFINITION.md`, `FEATURE_MATRIX.md`, `FEATURE_GATING.md` |
| Engineering implementation standards | `docs/06-engineering/ENGINEERING_STANDARDS.md` | `docs/00-governance/ENGINEERING_CONSTITUTION.md` |
| Cross-office authority, escalation | `docs/00-governance/office-operating-system/AUTHORITY_MODEL.md` | `DECISION_ESCALATION_MODEL.md` |
| Office-specific mandates | `docs/offices/` (one file per office) | `docs/00-governance/office-operating-system/OFFICE_TEMPLATE.md` |
| Compliance operating model, launch gates | `docs/09-compliance/PRODUCT-COMPLIANCE-OPERATING-MODEL.md` | `PRODUCT_GUARDRAILS.md` |
| Governance decision history | `docs/00-governance/DECISION_LOG.md` | `docs/00-governance/REPOSITORY_STATE.md` |
| Current active work | `TASKS.md` ("Current Phase" section) | `CHANGELOG.md` |
| Working principles (proven-value-first, restraint, single-source-of-truth) | `docs/00-governance/WORKING_PRINCIPLES.md` | — |

## What this deliberately doesn't do

No row restates its source's content — every "Notes"-style elaboration was cut from the original proposal's example format specifically to avoid the thing this whole package exists to prevent: a second, drifting copy of something that already has one home. If a row's target file is ever renamed, this table breaks loudly (a dead link), which is the honest failure mode for an index — better than silently going stale while looking authoritative.
