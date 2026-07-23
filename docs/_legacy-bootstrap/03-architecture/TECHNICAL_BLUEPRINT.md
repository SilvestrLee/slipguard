# SlipGuard Technical Blueprint

**Version:** 1.0  
**Status:** Implementation baseline  
**Owner:** Architecture Office

## 1. Architecture Style

Use a modular Laravel 13 monolith.

Reasons:

- Current product stage does not justify distributed systems.
- Shared transactions and domain logic are easier to maintain.
- Deployment and observability remain simpler.
- Features can still be internally modular.

## 2. Technology Baseline

- PHP: repository-supported version satisfying Composer requirements.
- Framework: Laravel 13.
- Frontend: Blade, Livewire, Vite.
- Internal operations: Filament.
- Testing: Pest.
- Database: use the configured relational database; do not change database technology without an ADR.
- Queues: Laravel queues when asynchronous work becomes necessary.
- Cache: Laravel cache abstraction.
- Scheduler: Laravel scheduler.
- AI: optional explanation adapter after deterministic results exist.

Do not add infrastructure merely because it may be useful later.

## 3. Application Boundaries

Suggested domain-oriented structure:

```text
app/
├── Actions/
├── Domain/
│   ├── Analysis/
│   ├── BettingSlip/
│   ├── Catalogue/
│   ├── Journal/
│   └── Risk/
├── Filament/
├── Http/
├── Livewire/
├── Models/
├── Policies/
├── Services/
└── Support/
```

Do not reorganize the whole Laravel scaffold before the first feature. Introduce folders as real code requires them.

## 4. Initial Domain Model

### User

Owns analyses, slips, and journal entries.

### BettingSlip

Represents the submitted slip.

Suggested fields:

- id
- user_id
- bookmaker_id nullable
- name nullable
- status
- total_decimal_odds
- leg_count
- submitted_at
- analyzed_at
- timestamps

### BettingSlipLeg

Suggested fields:

- id
- betting_slip_id
- sport
- competition nullable
- event_name
- event_start_time nullable
- market_name
- selection_name
- decimal_odds
- display_order
- timestamps

### SlipAnalysis

Stores the immutable result of one engine version.

Suggested fields:

- id
- betting_slip_id
- engine_version
- overall_risk_score
- risk_band
- weakest_leg_id nullable
- confidence_or_data_quality score nullable
- summary_payload JSON
- analyzed_at
- timestamps

### LegAnalysis

Suggested fields:

- id
- slip_analysis_id
- betting_slip_leg_id
- risk_contribution
- risk_band
- rule_results JSON
- explanation_payload JSON
- timestamps

### JournalEntry

Suggested fields:

- id
- user_id
- betting_slip_id
- decision
- result nullable
- note nullable
- reflected_at nullable
- timestamps

### Bookmaker

Keep minimal until integrations require more.

### FeatureFlag

Use a simple, auditable implementation. Avoid an external service for MVP.

## 5. Risk Engine Contract

The risk engine:

- Accepts validated normalized slip data.
- Applies versioned deterministic rules.
- Produces structured findings.
- Identifies weakest leg.
- Produces an overall score and risk band.
- Records rule-level contributions.
- Never calls an LLM.
- Produces the same result for the same input and engine version.

Natural-language generation occurs after this contract and may only explain structured findings.

## 6. Service Boundaries

Likely services/actions:

- CreateBettingSlip
- AddOrUpdateSlipLeg
- ValidateSlipForAnalysis
- AnalyzeBettingSlip
- CalculateAccumulatorRisk
- DetectWeakestLeg
- BuildRiskReport
- SaveJournalDecision

Use interfaces only where multiple implementations or testing boundaries genuinely benefit.

## 7. Request and Processing Flow

1. Livewire validates user input.
2. Application action creates or updates slip.
3. Domain validation confirms analyzable state.
4. Risk engine returns structured result.
5. Analysis records are saved in one transaction.
6. Report presenter transforms findings for the UI.
7. Optional AI explanation receives only approved structured findings.
8. User sees and may save the report.

## 8. Security and Privacy

- Authorize every customer-owned resource.
- Never trust user-provided ownership IDs.
- Validate and normalize odds.
- Escape customer text.
- Rate-limit analysis submission.
- Protect internal Filament routes.
- Keep staff actions auditable when rules or flags are changed.
- Do not expose prompts, provider secrets, raw exceptions, or internal diagnostics.
- Store only data needed for the product.

## 9. Testing Strategy

### Unit

- Odds normalization.
- Combined odds.
- Rule calculations.
- Risk bands.
- Weakest-leg selection.
- Engine-version determinism.

### Feature

- Authentication.
- Slip creation.
- Authorization.
- Analysis submission.
- Report access.
- History.
- Journal.

### Browser/End-to-End

Add only for critical journeys once screens stabilize.

## 10. Observability

MVP minimum:

- Structured Laravel logs.
- Failed job tracking if queues are introduced.
- Analysis engine version on every report.
- Error and incident log.
- Health check endpoint suitable for deployment monitoring.
- No sensitive user input in logs unless explicitly sanitized.

## 11. Deployment Principle

Keep one deployable Laravel application.

Use the existing repository and environment strategy. CI/CD should run tests and build frontend assets. Production changes must be reversible and documented.

## 12. Architecture Constraints

Do not introduce:

- Microservices.
- Event streaming.
- Multiple databases.
- Separate Python service before a mathematical requirement proves it necessary.
- Vector databases.
- Kubernetes.
- Premature real-time architecture.
- Unnecessary repository abstractions.