# Engineering Standards

## General
- Follow Laravel 13 conventions.
- Prefer framework capabilities over packages.
- Keep controllers and Livewire components thin.
- Put business rules in actions or services.
- Use transactions for multi-record operations.
- Authorize before accessing customer-owned data.
- Use enums or value objects where they remove ambiguity.
- Avoid abstractions without an active use case.
- Keep migrations reversible.
- Add factories and tests for core entities.

## Testing
Use Pest. Cover happy paths, validation, authorization, important edge cases, and regressions.

## Frontend
Blade and Livewire for customers; Filament for internal operations; Vite for assets. No casino styling or excessive animation.

## Dependencies
Before adding a package, establish the exact problem, why Laravel cannot reasonably solve it, maintenance status, security implications, and removal cost.

## Completion Report
Report what works, files changed, tests run, risks, and the next task.
