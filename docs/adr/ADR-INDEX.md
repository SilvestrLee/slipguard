# Architecture Decision Records

| ADR | Decision | Status |
|---|---|---|
| ADR-001 | Modular Laravel monolith | Accepted |
| ADR-002 | Deterministic risk calculations | Accepted |
| ADR-003 | Constrained AI explanation | Accepted |
| ADR-004 | Customer and operations UI separation | Accepted |
| ADR-005 | Authentication strategy (Laravel Breeze, Livewire stack) | Accepted |
| ADR-006 | Betting slip content versioning — none; lifecycle status is sufficient | Accepted |
| ADR-007 | Analysis persistence boundary — engine stays a pure calculator, persistence wraps it, presentation never invokes the engine or mutates a persisted analysis | Accepted |
| ADR-008 | Planner engine boundary — same pure-calculator discipline as ADR-007, applied to the U-06 Planner bounded context | Accepted |
| ADR-009 | Planner lifecycle & regeneration boundary — resolves ADR-008's Open Dependency for Capability A; PlannerSessionStatus lifecycle; export creates a new BettingSlip, source slip permanently preserved (PD-009) | Accepted |
| ADR-010 | Journal entry reference boundary — a JournalEntry belongs to exactly one SlipAnalysis; the reference is immutable after creation, the customer's own reflection text is editable | Accepted |
| ADR-011 | Operations Console authorization independence — reserved, cited inline (`U-10.1`/`U-10.3`), not yet drafted as its own standalone document | Reserved |
| ADR-012 | Product boundaries, operator independence & regulatory positioning — permanent product-identity/business-model boundaries (no customer-fund custody, no autonomous betting, operator independence, regulatory simplicity by design); a wider-scope entry than this series' usual narrow technical boundary, recorded here by direct Product Office ruling | Accepted |
| ADR-013 | Capability B (market-wide planning) Planner integration boundary — a generated candidate is either pre-`BettingSlip` (Capability B's own domain) or an ordinary `Ready` `BettingSlip` inside the existing, unmodified Planner; no parallel session/lifecycle system | Accepted |

Create ADRs only for durable architecture, security, cost, or maintainability decisions.

Authority boundaries (who may approve architecture changes) and the behaviour-change escalation policy are recorded in `docs/00-governance/ENGINEERING_CONSTITUTION.md`, not restated here.
