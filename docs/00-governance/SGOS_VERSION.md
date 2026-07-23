# SGOS Version

## Current Version

SGOS v1.0

## Release Date

2026-07-23

## Purpose

SGOS (SlipGuard Operating System) is the durable repository context for SlipGuard. It defines what SlipGuard is, what is being built, why major decisions were made, and how implementation should proceed, so that any engineer or AI agent can pick up the project without prior chat history.

## Scope

SGOS v1.0 covers governance, product definition, architecture, the risk-engine contract, UX rules, engineering standards, quality strategy, delivery roadmap, compliance guardrails, and architecture decision records. It does not yet contain approved risk-engine formulas — that remains an explicit, tracked blocker on E-04, not a gap in SGOS itself.

## Change Philosophy

Documents are populated just-in-time: only when they support the current milestone or preserve a decision that must remain stable across milestones. SGOS is not expanded speculatively ahead of need.

## Documentation Philosophy

One authoritative document per concern. Where a decision is durable (architecture, security, cost, maintainability), it is recorded as an ADR under `docs/adr/`. Where it is a product or scope decision, it is recorded in `docs/00-governance/DECISION_LOG.md`. Superseded or duplicate material is archived, not silently deleted, so history remains inspectable.

## Versioning Policy

SGOS versions bump on structural change, not on every document edit:

- **Patch-level edits** (typo fixes, clarifications, filling in a previously-empty section) do not change the version.
- **A new minor version** (e.g. v1.1) is declared when a new durable area of SGOS is populated for the first time (a new domain, a new ADR category) without changing the meaning of existing decisions.
- **A new major version** (e.g. v2.0) is declared only when an existing locked decision is deliberately reversed or the documentation structure itself is reorganized, and requires founder approval per `CLAUDE.md`'s governance rule.

## Future Versions

- **v1.1** — anticipated when E-03/E-04 introduce approved risk-engine formulas and the Data Science domain moves from contract to specification.
- **v2.0** — reserved for a structural reorganization or a reversal of a locked MVP decision (e.g. introducing prediction, parsing, or a services split), none of which are planned.
