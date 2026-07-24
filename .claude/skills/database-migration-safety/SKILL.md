---
name: database-migration-safety
description: This skill should be used when creating or modifying a database migration, adding a column, or considering whether new data needs persisting versus computing on the fly. Trigger phrases include "add a migration", "add a column", "new table", "should this be persisted". Applies to everything under database/migrations/.
user-invocable: false
---

# Database Migration Safety (SlipGuard)

## Before Adding a Column or Table

Prefer computing/normalizing data at read time over storing a derived value, unless there's a proven performance need — this project deliberately does NOT store `total_decimal_odds`/`leg_count` on `BettingSlip` (computed from the `legs` relation instead) and deliberately does NOT store normalized taxonomy codes on `BettingSlipLeg` (computed at analysis-preparation time by `NormalizeBettingSlip` instead — see `deterministic-risk-mathematics`). Follow that precedent: ask whether a new column is a source of truth or a derivable value before adding it.

## Conventions

- Migration filenames use the `YYYY_MM_DD_HHMMSS_description.php` timestamp format matching existing files; check the latest existing migration's timestamp before picking a new one so ordering stays correct.
- Every migration has both `up()` and `down()` — reversibility is required, not optional, per `docs/06-engineering/ENGINEERING_STANDARDS.md`.
- Foreign keys use `foreignId('x')->constrained()->cascadeOnDelete()` (see `betting_slips`/`betting_slip_legs`) unless there's an explicit reason a child record should survive its parent's deletion.
- New indexes need a genuine, stated performance justification — this project has explicitly declined to add indexes "for later" (see the E-03B performance review), since `user_id` is already indexed via its foreign key and current data volumes don't need more.

## Before Running

`php artisan migrate:fresh --force` against the local SQLite dev database is the standard way to verify a new migration set runs cleanly end-to-end — do this before considering migration work done, not just a syntax read-through.
