# Product Decision Log

| Date | Decision | Status | Reason |
|---|---|---|---|
| 2026-07-23 | No outcome prediction | Locked | Protects trust and category clarity |
| 2026-07-23 | MVP starts with manual slip entry | Locked | Proves core value before parser complexity |
| 2026-07-23 | AI explains but does not calculate | Locked | Keeps results auditable |
| 2026-07-23 | Customer UI remains separate from Filament | Locked | Customer UX requires purpose-built screens |
| 2026-07-23 | Authentication uses Laravel Breeze, Livewire stack | Locked | Minimal, first-party, fits existing Blade/Livewire and Filament setup — ADR-005 |
| 2026-07-24 | Analysed and Archived slips cannot be deleted, only archived | Locked | Protects analysis durability once SlipAnalysis records exist — ADR-006 addendum |
| 2026-07-24 | Risk Rule Set 2026.1 (deterministic factor mathematics) | Proposed, ready for approval | All seven prior open decisions individually resolved (§22.1–22.7); achievable score ceiling proven reachable; analysis-availability gate redesigned to three tiers — see `docs/03-data-science/RISK_RULE_SET_2026_1.md` |
| 2026-07-24 | Market normalization gated on recognized sport | Locked | Prevents non-football legs from being misclassified through the football taxonomy on overlapping alias text — implemented, `NormalizeBettingSlip` commit `7d13c27` |
