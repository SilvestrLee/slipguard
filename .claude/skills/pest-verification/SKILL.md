---
name: pest-verification
description: This skill should be used when writing tests, deciding where a new test belongs, or verifying that a change works before declaring a task complete. Trigger phrases include "add a test", "write Pest coverage", "verify this works", "run the tests". Use automatically before reporting any code change as done, not only when explicitly asked to test.
user-invocable: false
---

# Pest Testing Conventions (SlipGuard)

## Where Tests Live

- `tests/Feature/` — anything touching the database, HTTP routes, or a Livewire component's full lifecycle. Bound to `RefreshDatabase` in `tests/Pest.php`.
- `tests/Unit/` — pure logic with no Eloquent/DB dependency (e.g. `NormalizeSport`, `NormalizeFootballMarket`). These run without Laravel's app bootstrap — do not use `config()`, `__()`, or other framework helpers inside classes you want unit-testable this way.
- Mirror `app/` structure under `tests/`: `app/Domain/Risk/Normalization/` → `tests/Unit/Risk/` (pure) and `tests/Feature/Risk/` (anything needing a real `BettingSlip` model).

## Conventions

- Test Livewire/Volt components with `Livewire\Volt\Volt::test('component.name')` / `Volt::actingAs($user)->test(...)`, not raw HTTP requests, when testing component behavior directly. Use `$this->actingAs($user)->get(route(...))` when testing routing/middleware/authorization at the HTTP layer.
- Every new domain rule gets a dedicated test, not just incidental coverage from a happy-path test. Illegal-transition tests, ownership-denial tests, and empty/boundary-condition tests are not optional extras — write them alongside the feature, not after.
- When fixing a bug found in review, add a regression test in the same commit/change that names the bug scenario in the test description (see `tests/Unit/Risk/NormalizeFootballMarketTest.php`'s "a market alias containing both direction words does not shadow the actual selection").

## Before Declaring Anything Done

1. Run the targeted test file(s) for the change.
2. Run the full suite: `php artisan test`.
3. Report the exact pass count (e.g. "167/167 passing") — never round or approximate, and never say "tests pass" without having actually run them in this session.
4. Run `./vendor/bin/pint --test` (or `./vendor/bin/pint` to auto-fix) — a passing test suite with a dirty style diff is not done.
