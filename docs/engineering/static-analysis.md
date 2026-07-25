# Static Analysis Report — Sprint E-06C Validation

**Stage:** 3 of 12 · **Date:** 2026-07-25 · **Environment:** PHP 8.5.8 (Homebrew, `/usr/local/bin/php`), Laravel 13.21.1

## Environment Note (Category A candidate — resolved, not code)

The Herd-linked `php`/`php83` binaries (`~/Library/Application Support/Herd/bin/`) fail at process start with a `dyld` symbol error (`__ZNSt3__122__libcpp_verbose_abortEPKcz` not found in `libc++.1.dylib`) — a broken local toolchain link, not a repository defect. `/usr/local/bin/php` (Homebrew, 8.5.8) is functional and satisfies `composer.json`'s `"php": "^8.3"` constraint. All commands in this validation sprint were run through that binary. This is a local machine configuration issue, not a release blocker, but is recorded here because a CI/production environment relying on the Herd binary would fail entirely.

## Commands Executed

| Command | Result |
|---|---|
| `composer validate --no-check-publish` | `./composer.json is valid` |
| `composer dump-autoload -o` | 9,682 classes, no errors, `filament:upgrade` and package discovery clean |
| `vendor/bin/pint --test` | `{"tool":"pint","result":"passed"}` — zero style violations |
| `vendor/bin/pest --compact` | `{"tool":"pest","result":"passed","tests":309,"passed":309,"assertions":799,"duration_ms":12746}` |

## Tooling Inventory

Only **Pint** is installed and configured (`composer.json` require-dev). **PHPStan, Psalm, Rector, Deptrac, and PHP Insights are not present** in `vendor/bin/` or `composer.json`. No `phpstan.neon`, `psalm.xml`, `rector.php`, or `deptrac.yaml` exists in the repository root.

This means the architecture boundary described in ADR-007 (Presentation → Persistence → Risk Engine → Normalization → Betting Domain, with the Risk Engine forbidden from touching Laravel/Eloquent) is currently enforced **only by code review and test discipline**, not by an automated dependency-boundary tool. See `architecture-validation.md` for the manual boundary check.

## Findings

| ID | Category | Finding |
|---|---|---|
| SA-1 | **C** | No static type-checking tool (PHPStan/Psalm) is configured. The codebase is PHP 8.3+ with typed properties/returns used consistently in spot checks, but nothing enforces this project-wide or catches type-contract drift automatically. |
| SA-2 | **C** | No architecture-boundary enforcement tool (Deptrac) exists to make ADR-007's forbidden call flow (Risk Engine → persistence/HTTP/UI) a build-time failure rather than a review-time convention. |
| SA-3 | **C** | Local Herd PHP binaries are broken (dyld error); undocumented anywhere, so a future engineer will hit the same wall. Worth a one-line note in engineering docs (not a `docs/05-ux` or product-freeze concern). |

No Category A or B findings — the configured tools (Pint, Pest, Composer) all pass cleanly with zero violations across the full 309-test suite.

## Conclusion

Static analysis coverage is **minimal but not failing** — what is configured passes cleanly. The absence of PHPStan is a genuine gap for a codebase this deterministic/math-heavy (a type-level guarantee would suit `BigDecimal`-based factor code well), but per the sprint's engineering philosophy ("prefer small targeted improvements... do not add complexity for hypothetical future needs"), introducing a new tool is a Category C recommendation for a future sprint, not a blocker for this one.
