# ADR-004 — Customer and Operations UI Separation

**Status:** Accepted

## Decision

Use Blade and Livewire for public and customer-facing experiences. Use Filament only for authenticated internal operations.

## Reason

Customer experience requires purpose-built, simple, non-administrative interfaces. Filament is suitable for internal management and diagnostics.