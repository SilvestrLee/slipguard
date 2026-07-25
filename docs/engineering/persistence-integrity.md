# Persistence Integrity Report — Stage 5

**Scope:** `SlipAnalysis`, `LegAnalysis`, `AnalyzeBettingSlip`, their factories, and `tests/Feature/Analysis/*`.

## Evidence Reviewed

- `app/Models/SlipAnalysis.php`, `app/Models/LegAnalysis.php`
- `app/Actions/Analysis/AnalyzeBettingSlip.php`
- `database/migrations/2026_07_24_130000_create_slip_analyses_table.php`, `..._130001_create_leg_analyses_table.php`
- `database/factories/SlipAnalysisFactory.php`, `LegAnalysisFactory.php`
- `app/Policies/SlipAnalysisPolicy.php`
- `tests/Feature/Analysis/AnalyzeBettingSlipTest.php` (232 lines), `SlipAnalysisPolicyTest.php`
- Repo-wide grep for `SlipAnalysis::`, `->save(`, `->update(`, `forceFill`, `->touch(`, and route/Livewire references.

## Relationships

Confirmed wired both directions and correctly typed:

| From | To | Method | Type |
|---|---|---|---|
| `SlipAnalysis` | `BettingSlip` | `bettingSlip()` | `BelongsTo` |
| `SlipAnalysis` | `User` | `user()` | `BelongsTo` |
| `SlipAnalysis` | `LegAnalysis` | `legAnalyses()` | `HasMany`, ordered by `display_order` |
| `LegAnalysis` | `SlipAnalysis` | `slipAnalysis()` | `BelongsTo` |
| `LegAnalysis` | `BettingSlipLeg` | `bettingSlipLeg()` | `BelongsTo` |
| `BettingSlip` | `SlipAnalysis` | `analysis()` | `HasOne` (`app/Models/BettingSlip.php:42`) |

No gaps.

## Snapshot Fidelity

`LegAnalysis` stores `sport_code`, `sport_status`, `market_code`, `market_family`, `market_complexity`, `market_status`, `decimal_odds`, `raw_market_input`, `raw_selection_input` as **plain scalar/enum-string columns**, not foreign keys into any taxonomy table. `AnalyzeBettingSlip::execute()` writes these directly from the `NormalizedBettingSlipLeg` value object at analysis time (lines 72–80). There is no live join or lookup back into `FootballMarketTaxonomyV1` at read time — the taxonomy class only *produces* the values once, at write time. A future taxonomy version bump cannot retroactively change a persisted `LegAnalysis` row. Snapshot fidelity holds.

## Metadata / Reason Codes / Trace Round-Trip

- `factor_results`, `interaction_adjustments`, `data_quality_deductions`, `factors_not_evaluated` are cast `array` (JSON columns) — `AnalyzeBettingSlip`'s private `serializeFactorResults()`/`serializeInteractionAdjustments()` reduce every `BigDecimal` to a string and every value object to a plain array **before** the model ever sees them, so the model itself has zero coupling to engine value objects (matches the ADR-007 intent stated in the model's own docblock).
- `reason_codes` uses `AsEnumCollection::of(ReasonCode::class)` — verified round-trips back to `ReasonCode` enum instances, not raw strings (test assertion at `AnalyzeBettingSlipTest.php:126`).
- Test at line 114 (`expect($reloaded->factor_results)->toBe($analysis->factor_results)`) proves a `fresh()` reload reproduces the exact array — no lossy JSON coercion.

No silent coercion found.

## Four-Axis Version Storage

`engine_version`, `rule_set_version`, `input_schema_version`, `market_taxonomy_version` are all real, non-nullable `string` columns (migration lines 29–32), all populated from `$result->{...}Version` inside `AnalyzeBettingSlip::execute()` (lines 60–63), and all asserted directly in `AnalyzeBettingSlipTest.php:55-58`. Matches ADR-007's traceability requirement exactly.

## Immutability

- Repo-wide grep found **exactly one** `->save()` call on a `SlipAnalysis` instance in application code — the initial creation inside `AnalyzeBettingSlip::execute()` (line 65) — and **zero** `->update(...)` calls on either model anywhere in `app/`.
- `SlipAnalysisPolicy` defines **only `view`** — no `update`/`delete` ability exists, so no future controller/Livewire component can authorize a mutation even if one were mistakenly wired up; the omission is structural, not just discipline.
- No route or Livewire component currently references `SlipAnalysis` at all (presentation layer, U-02, not yet built) — consistent with ADR-007's "Presentation reads persisted analyses only" and with TASKS.md's stated milestone status.
- `$fillable` on `SlipAnalysis` deliberately excludes `user_id`; it is set explicitly (`$slipAnalysis->user_id = $bettingSlip->user_id`, line 46) from the slip's own owner, never from request input — correct denormalization, no mass-assignment surface.
- Both tables carry `$table->timestamps()`, so an `updated_at` column exists but is never written to by any code path (Category C — see below).

**Conclusion: ADR-007's immutability guarantee holds in the actual implementation.** There is no code path — application or test — that mutates a `SlipAnalysis`/`LegAnalysis` row after creation.

## Findings

| ID | Category | Finding |
|---|---|---|
| PI-1 | **C** | `slip_analyses` and `leg_analyses` both include an `updated_at` column via `$table->timestamps()` that is structurally guaranteed to never change (no code path writes it). A `created_at`-only schema (`$table->timestamp('created_at')->useCurrent()`) would make the immutability guarantee visible in the schema itself, not just in code discipline. Cosmetic; zero functional risk since nothing currently touches it. |
| PI-2 | **D** | Not evaluated here (out of scope): whether a future re-analysis feature (flagged as an open gap in ADR-007 itself) will require relaxing the `betting_slip_id` unique constraint. This report only confirms today's single-analysis-per-slip model is immutable as implemented — it does not assess the future gap, which ADR-007 already tracks explicitly. |

No Category A or B findings.

## Summary

Relationships correct both directions. Snapshot fidelity confirmed (no live taxonomy lookups). JSON/enum round-trip confirmed lossless. All four version axes persisted and tested. Immutability holds — single write path, no update/delete policy ability, no presentation-layer access yet. One cosmetic (Category C) schema observation; no blockers.
