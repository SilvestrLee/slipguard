# Architecture Boundary Validation Report — Sprint E-06C Validation

**Stage:** 2 of 12 · **Scope:** ADR-007 dependency chain — Presentation → Persistence → Risk Engine → Normalization → Betting Domain

## Evidence

**Risk Engine purity** (`app/Domain/Risk/Engine`, `Factors`, `Support`, `Results`, `Trace`, `RuleSets`, `Contracts`):
`grep -rn "Illuminate\\\\" ...` across all seven directories returns **zero matches**. Full `use` import listing (46 imports across 16 files) contains only internal `App\Domain\Risk\*` classes and `Brick\Math\{BigDecimal,RoundingMode}` (a pure arbitrary-precision math library, not a Laravel component). No `DB::`, `Model::`, `session()`, `auth()`, `Cache::`, `Queue::`, `event()`, `Http::`, or `Redis::` anywhere in `app/Domain/Risk` (confirmed by combined regex grep). This layer is fully deterministic and framework-free, matching ADR-007 §3 exactly.

**Normalization/Taxonomy purity** (`app/Domain/Risk/Normalization`, `Taxonomy`):
`FootballMarketTaxonomyV1.php`, `NormalizeFootballMarket.php`, `NormalizeSport.php`, `NormalizedMarket.php`, `NormalizedSport.php` are clean (internal imports + one `LogicException` only). **One exception**: `app/Domain/Risk/Normalization/NormalizeBettingSlip.php:8` imports `App\Models\BettingSlip` (Eloquent) and its `execute()` method (lines 23–56) reads `$bettingSlip->status` and `$bettingSlip->legs` (an Eloquent relation) directly — see Finding AV-1.

**Single orchestration path:** `grep` for `CalculateStructuralRisk` across `app/` and `database/` shows exactly one call site outside the engine's own definition/value-objects: `app/Actions/Analysis/AnalyzeBettingSlip.php:30,42`. `NormalizeBettingSlip` has exactly two callers: `AnalyzeBettingSlip.php` (the sanctioned orchestration boundary, `execute()` lines 33–41) and `app/Console/Commands/SlipguardNormalizeSlip.php` (a pre-ADR-007 developer diagnostic command that normalizes but never scores or persists — non-customer-facing).

**Orchestration boundary** (`app/Actions/Analysis/AnalyzeBettingSlip.php`): confirms exact ADR-007 call order — eligibility check (line 35) → normalize (line 41) → calculate (line 42) → single `DB::transaction()` (lines 44–87) that persists `SlipAnalysis`/`LegAnalysis` and transitions the slip via `markAnalysed()`. The engine's return value (`RiskAnalysisResult`) is only ever read and serialized (`serializeFactorResults`/`serializeInteractionAdjustments`, lines 94–128) into primitive arrays/strings — never recomputed.

**Persistence layer** (`SlipAnalysis`, `LegAnalysis` models): both are plain Eloquent models with `$fillable` arrays and casts only — no methods that invoke `App\Domain\Risk\Engine\*` or recompute scores. `SlipAnalysis::$fillable` deliberately excludes `user_id` (set explicitly in the action, not mass-assigned).

**Presentation layer:** `app/Http/Controllers` contains only the base `Controller.php` (no custom controllers). `app/Livewire` contains only `Forms/LoginForm.php` and `Actions/Logout.php` (Breeze auth scaffolding). **No dashboard, history, or risk-report screen exists yet** — U-02 has not started per `TASKS.md`. The "presentation reads persisted records only" criterion is therefore currently **vacuously satisfied** (nothing to violate), not yet positively demonstrated. This is expected given the roadmap position, not a defect.

## Findings

| ID | Category | Finding |
|---|---|---|
| AV-1 | **B** | `App\Domain\Risk\Normalization\NormalizeBettingSlip` (namespace `App\Domain\Risk\Normalization`) directly type-hints and reads from the Eloquent model `App\Models\BettingSlip` (`->status`, `->legs` relation), giving the Domain/Normalization layer real Laravel/Eloquent knowledge. This does not violate ADR-007's forbidden call flow (it doesn't persist, recalculate, or leak into the Risk Engine — the engine itself stays pure) and every behavior is covered by passing tests, but it is hidden coupling: the "pure domain" boundary the sprint directive's Stage 2 criteria describe ("Domain contains no Laravel knowledge / no Eloquent") is not literally true for this one class. Recommend (future sprint, not this one): accept a plain DTO/array of legs instead of the Eloquent model, pushing the `BettingSlip::legs` read up into `AnalyzeBettingSlip` (which already depends on Eloquent legitimately). Low risk, not a release blocker — no incorrect behavior results today. |
| AV-2 | **D** | No presentation layer exists yet to positively test "reads persisted records only, never re-invokes the engine" — the criterion is currently untestable rather than violated. Flag for re-verification once U-02 (Dashboard/History/Risk Report) is built. Product Office / roadmap observation, not an engineering defect. |

No Category A findings — the Risk Engine itself, the single-orchestration-path guarantee, and the transactional persistence-then-transition sequence are all intact exactly as ADR-007 requires.

## Conclusion

**ADR-007's boundary holds.** The Risk Engine (`Engine`/`Factors`/`Support`/`Results`/`Trace`/`RuleSets`/`Contracts`) is provably framework-free and has exactly one caller. The orchestration action (`AnalyzeBettingSlip`) is the sole place normalization, calculation, persistence, and lifecycle transition meet, wrapped in one transaction, with immutable serialization (no BigDecimal/enum objects reach the database). The one real gap (AV-1) is a minor purity softness in the Normalization sub-layer, not a call-flow or immutability violation, and does not block production readiness.
