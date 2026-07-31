# R-01.1 — Match Evidence & Context Intelligence (MECI)

> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review — not merely further discussion — before any implementation may begin. See `docs/10-research-incubation/README.md`.

```text
Identifier:            R-01.1
Programme:             R-01 — Research & Incubation Framework
Status:                Exploratory (per docs/10-research-incubation/LIFECYCLE.md §1 — real, cited
                       research exists; no real provider trial and no real counsel engagement
                       have occurred, so this does not reach Parser-Validated or
                       Compliance-Cleared regardless of how thorough the research reads)
Owner:                 Architecture Office (role, not a standing individual)
Date Created:          this conversation's date
Last Reviewed:         this conversation's date
Dependencies:          none outside this package
Related Architecture:  ADR-002, ADR-003, ADR-007, ADR-012 (referenced, none amended)
Product Office Decision: None
Repository Status:     None — no code, migration, or credential exists anywhere in this repository
```

## Provenance

Produced entirely in a single conversation, outside the repository, before any repository file existed for this programme. The research (provider capabilities, pricing, and licensing terms) is real — drawn from live web search and direct page fetches, cited in each document — but the architecture, findings, and "decisions" below were never reviewed by anyone outside that conversation, never resulted in a real provider contract, and are not implemented anywhere in this codebase.

## Contents

1. `01-ARCHITECTURE-DISCOVERY.md` — the complete architecture (acquisition, validation, attribution, versioning, snapshotting, consumption, explanation) for attaching factual football context evidence (injuries, suspensions, fixture congestion, travel burden, rest days, league context, head-to-head, weather, expected lineup availability) to the existing deterministic structural risk analysis, without modifying `CalculateStructuralRisk`/`RiskAnalysisResult`. Includes the original discovery and its Revision 1 (report-immutability and Parser Office scope refinements).
2. `02-PARSER-OFFICE-PHASE-0.md` — real evidence-source viability research: candidate providers, coverage, freshness, reliability, and the finding that a mainstream commercial "expected lineups" product is an algorithmic prediction, not usable as evidence under this architecture's own constraints.
3. `03-COMPLIANCE-REVIEW.md` — real licensing-term research for the shortlisted providers, including the finding that OpenWeatherMap's self-service tier carries a share-alike (ODbL) obligation materially different from every other provider reviewed.
4. `04-PROVIDER-DIRECTION-DISCUSSED.md` — the provider direction discussed at the end of that research (SportMonks + Visual Crossing, not OpenWeatherMap), recorded as a discussed direction, explicitly **not** an adopted decision — no provider is contracted, no credential exists.
5. `05-READINESS-ASSESSMENT.md` — this package assessed against `LIFECYCLE.md`'s own graduation criteria: recommendation is **retain within incubation** (`Parser-Validated`/`Compliance-Cleared` not yet reached — no real trial, no sent compliance enquiries), with a narrow, separately-tracked exception noted for a bounded, inert Engineering scaffold.

## What would actually need to happen before any of this graduates

Per the parent README's graduation framework: a real founder/Product Office instruction to proceed (given outside this material itself), real provider contracts, the two written licensing clarifications named in `03-COMPLIANCE-REVIEW.md` §6 actually sent and answered, real Engineering implementation reviewed and tested normally, and only then a real `TASKS.md`/`DECISION_LOG.md` entry reflecting what has actually happened.
