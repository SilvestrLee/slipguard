# ADR-002 — Deterministic Risk Calculations

**Status:** Accepted

## Decision

All user-facing risk scores, risk bands, weakest-leg findings, and rule contributions are produced by versioned deterministic logic.

## Consequences

- Results are reproducible.
- Rules can be tested and audited.
- Historical analyses retain their engine version.
- LLM output cannot alter mathematical conclusions.