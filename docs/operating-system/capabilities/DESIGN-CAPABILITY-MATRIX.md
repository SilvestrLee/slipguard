# Tsotsia Design Capability Matrix

## Purpose

This matrix assigns design responsibilities to candidate capabilities and
governance authorities.

It prevents duplicated responsibility, conflicting recommendations and silent
authority expansion.

Inclusion in this matrix does not authorize installation, invocation,
production use or cross-project rollout.

All listed capabilities remain subject to the Tsotsia Capability Adoption
Standard and the current Capability Registry status.

**Added (Constitutional Review, Finding 5 — No Reconciliation Mechanism):**
Registry status is authoritative over this table when they disagree — see
`CAPABILITY-REGISTRY.md`'s Registry Rules, which obligates whoever changes a
Status there to check this table for references to that capability and
update or flag them. A capability listed below as Primary or Supporting for
some responsibility is not thereby authorized to be invoked if the Registry
says otherwise.

## Authority Principle

Capabilities provide specialist input.

They do not receive final authority over:

- product scope;
- business rules;
- architecture;
- brand identity;
- acceptance criteria;
- security;
- compliance;
- production release decisions.

Final authority remains with the applicable office, approved repository
documentation and direct user instruction.

## Responsibility Matrix

**Corrected (Constitutional Review, Finding 2 — Fabricated Authority):** this
table previously cited "UX Office" as final authority in ten rows below. No
office by that name exists anywhere in this repository's governance
(`docs/offices/`). The real, established body is **UX Studio**
(`docs/offices/UX_STUDIO.md`, active since Governance Programme G-02,
2026-07-25) — every occurrence below has been corrected to name it.

| Responsibility | Primary capability | Supporting capability | Final authority |
|---|---|---|---|
| Product scope | None | None | Product Office |
| Business rules | None | None | Product Office |
| User objective | Experience Architect | Approved product documentation | Product Office |
| User journey | Experience Architect | UI/UX Pro Max | UX Studio |
| Information architecture | Experience Architect | UI/UX Pro Max | UX Studio |
| Navigation structure | Experience Architect | Existing repository patterns | UX Studio |
| Workflow sequencing | Experience Architect | UI/UX Pro Max | UX Studio |
| Interface-state definition | Experience Architect | Design QA | UX Studio |
| Progressive disclosure | Experience Architect | UI/UX Pro Max | UX Studio |
| Cognitive-load review | Experience Architect | Design QA | UX Studio |
| Brand compliance | Brand Governor | Impeccable | Approved project brand context |
| Product-interface composition | Frontend Design | UI/UX Pro Max | Approved UX specification |
| Marketing-page visual exploration | Design Taste Frontend | Frontend Design | UX and Brand authority |
| Frontend implementation | Frontend Design | Existing repository conventions | Engineering Office |
| Responsive implementation | Frontend Design | Design QA | Engineering Office |
| Generic-pattern critique | Impeccable | Brand Governor | UX Studio |
| Accessibility guidance | UI/UX Pro Max | Design QA | Approved acceptance criteria |
| Accessibility verification | Design QA | Existing testing tools | QA and UX authority |
| Responsive verification | Design QA | Frontend Design | QA and UX authority |
| Motion-opportunity identification | Emil Design Engineering | Experience Architect | UX Studio |
| Motion specification | Emil Design Engineering | Brand Governor | UX Studio |
| Motion implementation | Engineering Office | Emil Design Engineering | Approved motion specification |
| Final interface audit | Design QA | Impeccable and Brand Governor | Product Office acceptance |
| Capability evaluation | Capability Auditor | Capability Adoption Standard | Product Office |
| Capability classification | None | Capability Auditor findings | Product Office |
| Capability installation authorization | None | Capability Registry | Product Office |
| Production release acceptance | None | Design QA and Engineering evidence | Product Office |

## Capability Boundaries

### Experience Architect

May define:

- journeys;
- workflow stages;
- screen inventories;
- interface states;
- hierarchy;
- navigation implications;
- progressive disclosure;
- accessibility considerations.

May not:

- add product scope;
- change business rules;
- select final brand direction;
- implement production code;
- approve its own recommendations.

### Brand Governor

May evaluate:

- brand consistency;
- visual identity;
- tone;
- hierarchy;
- restraint;
- language;
- approved asset treatment.

May not:

- invent a new brand;
- expand product scope;
- approve engineering quality;
- replace approved product requirements;
- import another project’s identity.

### Design QA

May evaluate:

- requirement coverage;
- state completeness;
- responsive behaviour;
- accessibility;
- visual quality;
- interaction feedback;
- motion restraint;
- brand compliance.

May not:

- silently redesign accepted work;
- redefine requirements;
- approve unresolved release blockers;
- add unrelated enhancements during defect correction.

### Capability Auditor

May evaluate:

- factuality;
- maturity;
- scalability;
- governance fit;
- security;
- return on complexity.

May not:

- install capabilities;
- authorize capabilities;
- alter the registry independently;
- treat popularity as evidence;
- approve production use.

### Frontend Design

May support:

- frontend composition;
- component implementation;
- responsive structure;
- translation of approved direction into code.

May not:

- define product scope;
- redefine brand identity;
- replace accepted UX decisions;
- approve its own implementation.

### Impeccable

May support:

- critique;
- anti-slop review;
- coherence review;
- generic-pattern detection;
- final quality challenge.

May not:

- become the source of product requirements;
- invent brand identity;
- override accepted decisions;
- authorize implementation changes independently.

### UI/UX Pro Max

May support:

- structured UX research;
- layout references;
- interaction-pattern research;
- accessibility guidance;
- responsive guidance;
- design-system comparison.

May not:

- replace the approved project design system;
- override repository evidence;
- assume stack compatibility without verification;
- authorize implementation.

### Design Taste Frontend

May support:

- marketing-page visual direction;
- landing-page exploration;
- portfolio-like presentation;
- suitable public-site redesign work.

May not serve as the primary capability for:

- dashboards;
- data tables;
- complex workflows;
- multi-step product interfaces;
- analysis reports;
- operational interfaces.

### Emil Design Engineering

May support:

- motion judgment;
- timing;
- easing;
- transition quality;
- animation review;
- purposeful interaction polish.

May not:

- animate unapproved static structures;
- add decorative motion;
- replace accessibility requirements;
- bypass reduced-motion support;
- authorize its own proposals.

## Prohibited Overlap

1. No capability may define or expand product scope.
2. No external capability may override an approved Product Office decision.
3. No external capability may replace approved brand or UX documentation.
4. Frontend Design may not approve the implementation it produces.
5. Design QA may not redefine the requirements it audits.
6. Motion capability may not operate before static hierarchy and states are approved.
7. Design Taste Frontend may not govern complex SlipGuard product interfaces.
8. UI/UX Pro Max recommendations may not be treated as adopted design decisions.
9. Impeccable critique may not silently become implementation authority.
10. Brand Governor may not import rules from another project.
11. Experience Architect may not change business logic.
12. Capability Auditor may not authorize installation or production use.
13. No capability may claim invocation without evidence.
14. No capability may bypass office handoff or acceptance gates.
15. No capability may automatically commit repository changes.

## Conflict Resolution

**This section is the single canonical authority ordering for the Tsotsia
Design Capability System.** `docs/operating-system/capabilities/
CAPABILITY-ADOPTION-STANDARD.md`, `docs/design-intelligence/SKILL-ROUTING.md`,
and `docs/design-intelligence/PROJECT-DESIGN-CONTEXT.md` each defer to this
ordering rather than restate a separate one — see those documents' own
Authority sections. Amend it here; do not fork it elsewhere.

When recommendations conflict, use this order:

1. Direct user instruction
2. Repository Constitution
3. Approved Product Office decision
4. Approved compliance and security rules
5. Approved architecture decision
6. Approved feature requirement and acceptance criteria
7. Approved project brand and UX documentation
8. Project Design Context
9. Tsotsia internal capability guidance
10. Approved external capability guidance
11. General model preference

The conflict and its resolution must be documented when it materially affects
the outcome.
