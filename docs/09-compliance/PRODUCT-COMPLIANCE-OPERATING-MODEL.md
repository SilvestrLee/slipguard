# Product Compliance Operating Model

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Owner | Product Office / Compliance Office jointly |
| Adopted | 2026-07-29, direct founder instruction (`PO-PD011-001` Decision Four) |
| Related | `docs/09-compliance/PRODUCT_GUARDRAILS.md`, `ADR-012`, `CLAUDE.md`'s Locked Decisions, `docs/02-architecture/U-17.4-PROVIDER-LICENSING-AND-COMPLIANCE-REVIEW.md` |

## Provenance

Adopted from the founder's own direct instruction, which cited a prior "Compliance Office submission" as the basis for restructuring. **No such submission exists anywhere in this repository** — verified before writing anything here, consistent with this project's established practice (`G-03.1`'s own verification standard) of checking a citation against real state before treating it as authoritative. The restructured content itself is coherent with, and introduces no exception to, `ADR-012`'s already-locked boundaries — so it is adopted here on its own merits, honestly attributed as new guidance issued today rather than a ratification of a document that never existed.

---

## Permanent Product Principles

These restate, in this document's own organizing vocabulary, boundaries `ADR-012` and `CLAUDE.md`'s Locked Decisions already established — no new restriction is introduced, none is loosened.

**Decision Intelligence Platform.** SlipGuard shall not become a bookmaker, gambling operator, betting intermediary, automated wagering platform, or customer fund custodian, unless authorised through a future Strategic Decision supported by independent legal advice — restates `ADR-012` directly.

**Customer Sovereignty.** SlipGuard informs; customers decide. SlipGuard shall never place bets, guarantee winnings, replace customer judgement, or make betting decisions — restates `ADR-012`'s "no autonomous betting" and `CLAUDE.md`'s customer-final-decision-maker principle.

**Deterministic Integrity.** Legal, marketing, and customer communication shall accurately reflect deterministic intelligence; prediction language is prohibited — restates the Locked Decision "no outcome prediction" and `EXPLAINABILITY_SYSTEM.md`'s existing Language Rules.

**Evidence Transparency.** Every conclusion must remain explainable — restates the Locked Decision on deterministic, explainable risk calculations.

**Compliance by Design.** Compliance review is part of feature design, not a post-development exercise — the practice this session's own `U-17.4` review already modelled (reviewed before, not after, `U-17.5`'s architecture confirmation).

## Compliance Trigger Matrix

Not every feature needs Compliance Office review — blanket review was explicitly rejected in favour of a named trigger list.

**Mandatory Compliance review**: bookmaker integrations, payment capabilities, affiliate systems, OCR expansion beyond MVP, bet-code imports, AI-generated recommendations, automated wagering, customer account linking, live market intelligence services (`U-17`'s own Capability B — already reviewed once under `U-17.4`, and any further expansion of it should trigger this row again), third-party betting integrations.

**Standard Product Office review** (no mandatory Compliance step): UI improvements, dashboard improvements, accessibility, performance, design refinements, existing deterministic functionality.

## Launch Readiness Gates (not a permanent work package)

Before Public Beta, five gates — release conditions, not standing programmes needing their own constitutional documentation each:

1. Independent legal classification.
2. Terms of Service / Privacy Policy / Disclaimer Framework / Cookie Policy where applicable.
3. External Data Register (provider, licence, commercial rights, renewal terms, operational dependency — `U-17.2`/`U-17.3`/`U-17.4`'s own findings on The Odds API are this register's first real entry).
4. Responsible Product Messaging review (`ADR-012`'s "no pressure mechanics" boundary, verified in practice, not just in principle).
5. Security and Incident Response documentation appropriate for launch.

## Deferred until market expansion (not commissioned now)

International jurisdiction matrix, country-specific gambling regulation, regional advertising compliance, country-specific legal reviews — explicitly out of scope until Product Office approves entry into a specific market. This directly supersedes the internationalisation/localisation architecture directive declined earlier this session (`PO-U13.2-001`) — that decline stands; this section is the honest, deferred placeholder for if and when that decision is actually made, not a reversal of it.
