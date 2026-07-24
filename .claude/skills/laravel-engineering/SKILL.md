---
name: laravel-engineering
description: This skill should be used when writing or modifying PHP application code in this Laravel 13 app — controllers, routes, models, Livewire components, service providers, or console commands. Trigger phrases include "add a route", "create a model", "write a controller", "add a console command", or any change under app/, routes/, or config/. Do not use for pure Blade/CSS styling with no PHP logic, or for taxonomy/risk-math work (see deterministic-risk-mathematics).
user-invocable: false
---

# Laravel Engineering Conventions (SlipGuard)

Follow Laravel 13 conventions and PHP 8.3 syntax (readonly classes, enums, first-class callable syntax) throughout.

## Structure Already Established

- `app/Models/` — Eloquent models. Keep them rich where the behavior is intrinsic to the entity (e.g. `BettingSlip::markReady()`), not just data bags.
- `app/Actions/<Area>/` — orchestrated writes that touch multiple records or need a transaction (e.g. `SaveBettingSlip`). One action, one verb, one job.
- `app/Domain/<Area>/` — enums and value objects that remove ambiguity (status enums, validation-rule providers, eligibility results). Not a dumping ground — only create an object here if it enforces an invariant or replaces a scattered set of conditionals.
- `app/Policies/` — one policy per model, authorization only, no business logic.
- `app/Console/Commands/` — developer diagnostics use the `#[Signature]`/`#[Description]` attribute style already in use (see `SlipguardHealth`, `SlipguardNormalizeSlip`), not the legacy `protected $signature` property.

## Rules

1. Controllers and Livewire components stay thin — orchestration only. Business rules belong in an Action or a Domain class, never inline in a component method beyond a few lines.
2. Prefer framework capabilities over new packages. Before adding a dependency, state the exact problem, why Laravel can't reasonably solve it, and the removal cost — this project has done this once already (`brick/math` was proposed, not yet added, for fixed-precision decimals).
3. Use transactions for any operation touching more than one table (see `SaveBettingSlip`).
4. Never mass-assign ownership. `user_id`-style foreign keys stay out of `$fillable` and get set explicitly from `Auth::user()`, never from a client-supplied array (see `BettingSlip::$fillable` and `SaveBettingSlip::execute()`).
5. Run `./vendor/bin/pint` before considering PHP changes done — this repo enforces Laravel's Pint style and a dirty diff will show up in review.
6. Enums are backed string enums with behavior on them (`canTransitionTo()`, `isEditable()`), not raw string constants — see `BettingSlipStatus`.

## Before Declaring Done

Run `php artisan test` (see `pest-verification`) and `./vendor/bin/pint --test`. Both must pass.
