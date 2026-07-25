# Decision Escalation Model

**Status:** Active · **Effective:** 2026-07-25 · **Authority:** Product Office · **Part of:** the Office Operating System (`OFFICE_OPERATING_SYSTEM.md`)

Defines exactly when an office may decide alone, when it must stop for another office's approval, and how work resumes. This is the general model; `docs/00-governance/ENGINEERING_CONSTITUTION.md` §2 (Behaviour Change Policy) is Engineering Office's own pre-existing, specific application of it and remains in force unchanged — this document generalises that pattern to every office rather than replacing it.

## The Core Rule

**Never silently invent behaviour, mathematics, architecture, or scope outside your own Decision Rights.** The instant a piece of work would require a decision outside an office's own constitution (`OFFICE_TEMPLATE.md`'s Decision Rights section), that office stops.

```
Work in progress
        ↓
A decision is needed outside this office's Decision Rights
        ↓
STOP
        ↓
Document the specific ambiguity/decision needed
        ↓
Route to the Accountable office (AUTHORITY_MODEL.md's Responsibility Matrix)
        ↓
Accountable office decides
        ↓
Decision recorded (docs/00-governance/DECISION_LOG.md, if durable)
        ↓
RESUME — implement exactly what was decided, nothing more
```

## When an Office May Decide Independently

Only when **both** are true:
1. The decision falls entirely inside that office's Decision Rights (its own constitution).
2. Nothing about the decision touches another office's Prohibited Actions (e.g. Engineering choosing *how* to implement an approved formula is in-bounds; Engineering choosing *what* the formula should be is not, per `docs/offices/ENGINEERING_OFFICE.md`'s Prohibited Actions).

If either condition is unclear, treat it as false and escalate — ambiguity about whether to escalate is itself resolved by escalating, not by assuming autonomy.

## When Work Stops

Immediately on discovering the ambiguity — not after building around it, not after shipping a "reasonable guess" version pending confirmation. Every gap this repository has surfaced so far (the RF-003A reference-script defect, the missing `DESIGN_TOKENS.md` colour values, the undefined "Discipline Trend" metric) was caught and escalated *before* implementation proceeded past that point, not patched retroactively.

## When Work Continues

Only once the Accountable office has actually decided — not once the requesting office's own preferred answer seems likely to be approved. A pending decision is not a decided one.

## Worked Examples

**Engineering discovers a behavioural conflict** (e.g. an approved spec doesn't cover a case the implementation now needs to handle):
```
Engineering → STOP → Product Office decision → Engineering implements the approved decision
```

**Data Science Lab discovers a mathematical ambiguity** (e.g. two internally-consistent readings of an approved formula):
```
Data Science Lab → STOP → Product Office (+ Data Science Lab jointly, per Rule Set precedent) → Engineering implements
```

**UX Studio discovers a product ambiguity** (e.g. a screen needs a pattern the UX Constitution doesn't define — the Gap Rule):
```
UX Studio → STOP → Product Office clarification → UX Studio (or Engineering, once documented) proceeds
```

**Architecture Office identifies a structural risk in a proposed feature:**
```
Architecture Office → STOP (raise before implementation begins, not after) → Product Office informed / joint decision if it affects scope or cost → Engineering implements the resolved design
```

**Compliance Office finds a claim or copy that may not be defensible under `docs/09-compliance/PRODUCT_GUARDRAILS.md`:**
```
Compliance Office → STOP the specific claim, not the whole feature → Product Office (+ Compliance Office jointly) → UX Studio/Engineering revise only the flagged element
```

## What Escalation Is Not

- **Not a blanket pause.** Only the specific decision is blocked; unrelated work continues (e.g. U-02's colour-token escalation didn't halt the rest of the dashboard build — only the colour-dependent styling waited).
- **Not a request for permission to do routine work.** Routine, in-boundary decisions never escalate — escalating everything is as much a failure of this model as inventing behaviour unilaterally, since it defeats the autonomy `OFFICE_OPERATING_SYSTEM.md` grants each office.
- **Not reversible by the escalating office once decided.** Once the Accountable office has ruled, the requesting office implements that ruling — it does not re-litigate it by re-escalating the same question hoping for a different answer, absent new information.

## Recording

Escalations that produce a durable decision (a new rule, a new constraint, a permanent scope boundary) are recorded in `docs/00-governance/DECISION_LOG.md`, following the precedent already established there (e.g. the RF-003A entry, the U-02 design-token acceptance, the reserved-product-concepts entry). Escalations resolved as routine, one-off clarifications with no lasting governance implication do not require a Decision Log entry — only their outcome needs to reach the requesting office.
