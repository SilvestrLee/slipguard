---
name: filament-operations
description: This skill should be used when adding or changing internal/staff-only tooling — Filament resources, pages, widgets, or anything under app/Providers/Filament/ or the /operations route prefix. Trigger phrases include "add an admin panel", "add a Filament resource", "internal dashboard", "staff tooling", "operations panel". Do not use for any customer-facing screen (see livewire-product-interface) — the two UIs are a locked architectural separation.
user-invocable: false
---

# Filament Operations Conventions (SlipGuard)

Internal operations use Filament exclusively. Customer-facing UI uses Blade/Livewire exclusively. This split is ADR-004, a locked decision — never build a customer feature in Filament or an internal tool in the customer Livewire app.

## Established Setup

- `app/Providers/Filament/OperationsPanelProvider.php` registers the single `operations` panel at `/operations`, with its own login (`Filament\Auth`), separate from customer auth.
- Access is gated by `User::canAccessPanel()`, which checks `is_internal` (a boolean column, defaults `false`). Never grant panel access another way.
- `php artisan slipguard:make-internal-user {email}` promotes a user to staff; it refuses to run in production without `--force`.
- No Filament resources exist yet (`app/Filament/Resources` is empty) — Filament auto-discovers them from that path when added, no manual registration needed.

## Rules

1. Every Filament resource must respect the same ownership model as the customer side — an internal user browsing `BettingSlip` records is not the same thing as the owning customer being authorized; use Filament's own authorization hooks (`canViewAny`, `canDelete`, etc.), don't just rely on `is_internal`.
2. Do not expose deterministic risk-engine internals (calculation traces, raw factor values) through Filament without an explicit product decision — the audience is staff, not Data Science, and staff tooling for inspecting normalization/taxonomy should stay a CLI diagnostic (see `deterministic-risk-mathematics`) unless a concrete operational need is demonstrated.
3. Keep the operations nav (Overview, Users, Analyses, Sports Catalogue, Bookmakers, Risk Rules, Feature Flags, Incidents, System Health per `docs/01-product/PRODUCT_BLUEPRINT.md`) — only build a section when an active customer feature requires staff visibility into it. Don't build ahead of need.
