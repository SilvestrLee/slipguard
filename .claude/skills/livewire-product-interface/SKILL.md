---
name: livewire-product-interface
description: This skill should be used when building or changing customer-facing screens — anything under resources/views/livewire/, resources/views/layouts/, or resources/views/dashboard.blade.php, coming-soon.blade.php, welcome.blade.php, profile.blade.php. Trigger phrases include "add a screen", "change the dashboard", "update the slip builder UI", "add a customer page". Do not use for the Filament operations panel (see filament-operations) or for pure backend logic with no view changes.
user-invocable: false
---

# Livewire Product Interface Conventions (SlipGuard)

Customer UI is Blade + Livewire (Volt single-file components under `resources/views/livewire/`), never Filament. This is a locked decision in `CLAUDE.md` — do not blur that boundary.

## Established Patterns

- Every customer screen extends `layouts.app` (via `#[Layout('layouts.app')]` on Volt components, or `<x-app-layout>` for plain Blade views). Do not build a second layout.
- Full-page Volt components render their heading inline in the content body, not via a `<x-slot name="header">` sibling to a bare `<div>` — Livewire's layout mechanism does not reliably capture named slots outside an actual component tag. Confirmed by testing; see `betting-slips/index.blade.php` and `builder.blade.php` for the working pattern.
- Non-functional nav destinations render `coming-soon.blade.php` (title + "back to dashboard"), never a dead link or a half-built page.
- Lifecycle-driven UI (status badges, action buttons, read-only rendering) asks the domain object for the answer — `$bettingSlip->isEditable()`, `$bettingSlip->status->label()` — rather than hardcoding status strings in Blade `@if` conditions. Known debt: some status-based conditionals in the builder/index views currently duplicate the enum's logic as raw string comparisons instead of calling it — don't add more of this pattern; ask the model/enum instead.
- Loading states use `wire:loading.attr="disabled"` and `wire:loading`/`wire:loading.remove` pairs on the button whose action is running, not a global spinner.

## UX Rules (from docs/05-ux/UX_RULES.md)

- Plain, short sentences. No jargon without explanation. Never imply certainty about outcomes.
- No casino aesthetics: no neon, no flashing elements, no confetti, no countdown pressure.
- Every screen answers: what is happening, why it matters, what to do next.
- Accessible by default: labels on every input, visible focus states, semantic headings — see `accessibility-review` for the deeper checklist.

## Authorization in Views

Livewire methods that mutate a customer-owned resource call `$this->authorize()` themselves — never assume the route already covered it. See `security-authorization`.
