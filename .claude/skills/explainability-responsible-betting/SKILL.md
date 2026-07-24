---
name: explainability-responsible-betting
description: This skill should be used when writing or reviewing any customer-facing copy about risk, analysis results, betting behavior, or when adding an AI-generated explanation feature. Trigger phrases include "explain the result", "write the risk report copy", "responsible betting", "AI explanation", "customer-facing message about odds/risk". Do not use for purely internal/technical text (logs, code comments, developer diagnostics).
user-invocable: false
---

# Explainability & Responsible Betting Guardrails (SlipGuard)

SlipGuard is a betting risk intelligence platform, not a tipster, prediction engine, or safe-bet generator. This is the product's core identity (`CLAUDE.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`) — every piece of customer-facing text about risk must respect it.

## Prohibited, Always

- Predicting who wins, or implying a result is likely.
- "Safe," "guaranteed," "certain," "sure thing," or any language implying elimination of risk.
- Treating a risk score as permission to bet, or as advice to bet more.
- Urgency, loss-chasing prompts, streak framing, or countdown pressure (`docs/09-compliance/PRODUCT_GUARDRAILS.md`).

## Required in Every Analysis-Facing Screen

State plainly: SlipGuard evaluates structural risk, does not predict outcomes, sport remains uncertain, and the user owns the final decision. See the report hierarchy in `docs/05-ux/UX_RULES.md` §"Risk Report Hierarchy" — headline, band, weakest leg, main reasons, per-leg detail, methodology, then the save/revise/journal action, always ending with a non-prediction disclaimer.

## AI Boundary (if an AI explanation layer is ever added)

An LLM may receive: an approved score, band, weakest-leg label, reason codes, and approved explanation facts (structured, machine-readable — see `deterministic-risk-mathematics`'s Explanation Facts). An LLM may not: change a score or band, select the weakest leg, invent match facts, claim a result will occur, or add betting recommendations unsupported by the deterministic rules. This is ADR-003, locked.

## Vocabulary

Use `docs/00-governance/PRODUCT_GLOSSARY.md`'s terms consistently — "weakest leg" means highest structural risk contribution, explicitly not a prediction it will lose; "confidence" refers to data quality/completeness, never to outcome probability. Don't invent synonyms that blur these definitions.
