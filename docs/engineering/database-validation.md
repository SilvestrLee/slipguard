# Database Validation Report — Sprint E-06C Validation

**Stage:** 4 of 12 · **Date:** 2026-07-25

## Evidence Reviewed

All 8 migrations in `database/migrations/` (users/cache/jobs framework defaults, `add_is_internal_to_users_table`, `create_betting_slips_table`, `create_betting_slip_legs_table`, `create_slip_analyses_table`, `create_leg_analyses_table`); `app/Models/SlipAnalysis.php`, `app/Models/LegAnalysis.php`, `app/Models/BettingSlip.php`; `docs/adr/ADR-007-ANALYSIS-PERSISTENCE-BOUNDARY.md`; `docs/03-data-science/RISK_RULE_SET_2026_1.md` §2–3.

## Schema Summary

| Table | PK | Key FKs | Notable columns |
|---|---|---|---|
| `betting_slips` | `id` | `user_id → users`, cascade | `status` string, `name` nullable |
| `betting_slip_legs` | `id` | `betting_slip_id → betting_slips`, cascade | `decimal_odds DECIMAL(6,2)`, `display_order` |
| `slip_analyses` | `id` | `betting_slip_id → betting_slips` **unique**, cascade; `user_id → users`, cascade | `structural_score`/`risk_band` nullable; `data_quality_score`/`data_quality_band` non-nullable; 5 JSON columns; 4 version strings |
| `leg_analyses` | `id` | `slip_analysis_id → slip_analyses`, cascade; `betting_slip_leg_id → betting_slip_legs`, cascade | `decimal_odds DECIMAL(6,2)`, `sport_code`/`market_code`/`market_family` nullable, `sport_status`/`market_status`/`market_complexity` non-nullable |

## Findings

| ID | Category | Finding |
|---|---|---|
| DB-1 | **B** | `slip_analyses.betting_slip_id` and `slip_analyses.user_id` both `cascadeOnDelete()`, and `leg_analyses` cascades from both its parents. This means a hard `DELETE` of a `BettingSlip` (or `User`) silently destroys the "historical record" ADR-007 calls immutable. In practice this is mitigated — `BettingSlipPolicy::delete()` (per `TASKS.md` E-05A) only permits deleting Draft/Ready slips, and a slip with a `SlipAnalysis` is always `Analysed`, so the customer-facing path cannot trigger it. But the schema itself provides no independent guarantee — a future admin tool, `forceDelete`, artisan command, or user cascade-delete (e.g. account deletion, not yet built) would silently erase audit history with no application-layer gate. ADR-007 does not address deletion at all. **Recommend:** Architecture/Product Office decide explicitly whether analyses must outlive their source slip (e.g. `restrictOnDelete()` or a soft-delete/archival requirement) before any account-deletion or admin-deletion feature is built — flagging per the Category D discipline (product/architecture decision, not an engineering unilateral fix). |
| DB-2 | **C** | No explicit index beyond the implicit FK index on `slip_analyses.user_id`, `leg_analyses.slip_analysis_id`, `leg_analyses.betting_slip_leg_id`. On SQLite, `constrained()` creates the FK constraint but Laravel does not add a redundant secondary index. At current/expected data volumes (single-user history, ≤20 legs/slip) this is immaterial; worth revisiting only if a "History" list ever queries `slip_analyses` across users at scale. |
| DB-3 | **C** | `leg_analyses` has no composite index on `(slip_analysis_id, display_order)` to support the `HasMany` `orderBy('display_order')` in `SlipAnalysis::legAnalyses()`. Irrelevant at ≤20 rows per parent; not worth adding now. |

## Confirmed Correct (no finding)

- **Primary keys** — every table has a proper auto-increment `id()` (or a sensible natural PK for framework tables: `sessions.id`, `cache.key`, `password_reset_tokens.email`).
- **One-analysis-per-slip constraint** — `slip_analyses.betting_slip_id` is `unique()`, correctly enforcing ADR-007's "one analysis per slip, ever" at the database level, not just in application logic.
- **Nullability matches the approved design** — `structural_score`/`risk_band` nullable (Unavailable case), `data_quality_score`/`data_quality_band` non-nullable (always computed, per `RISK_RULE_SET_2026_1.md`), `sport_code`/`market_code`/`market_family` nullable (unsupported/unrecognized sport), `sport_status`/`market_status`/`market_complexity` non-nullable (always resolve to a value, including `"unknown"`/`"unrecognized"` strings) — all consistent with the model casts and the taxonomy's documented state table.
- **Precision** — `decimal_odds` is `DECIMAL(6,2)` in both `betting_slip_legs` and `leg_analyses`, matching `RISK_RULE_SET_2026_1.md` §2/§3 exactly (2 d.p., not 4).
- **JSON columns** — all 5 (`factor_results`, `interaction_adjustments`, `data_quality_deductions`, `factors_not_evaluated`, `reason_codes`) are `json()` columns with matching `array`/`AsEnumCollection` casts in `SlipAnalysis`; no raw value objects (`BigDecimal`, engine enums) leak into storage — consistent with the model's own documented boundary comment.
- **Migration ordering** — filenames are strictly dependency-ordered (`users` → `betting_slips` → `betting_slip_legs` → `slip_analyses` → `leg_analyses`); every `constrained()` call references a table created in an earlier migration.
- **Mass-assignment boundary** — `SlipAnalysis::$fillable` deliberately excludes `user_id`, matching the model's own documented comment and the security-authorization convention.

## Conclusion

The schema matches the approved domain model (ADR-007, `RISK_RULE_SET_2026_1.md`) with no Category A defects — keys, precision, nullability, and JSON handling are all correct and internally consistent. One Category B finding (DB-1, cascade-delete vs. immutability) is a genuine but currently-mitigated architectural gap that should be resolved by explicit decision before any slip/account-deletion feature is built, not silently left as an implicit cascade. Two Category C items are indexing hygiene, not urgent at current scale.
