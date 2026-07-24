---
name: accessibility-review
description: This skill should be used when a customer-facing screen is materially changed or added — new forms, new interactive controls, new navigation. Trigger phrases include "check accessibility", "is this keyboard accessible", "add a form field", "new interactive component". Not needed for backend-only changes or internal Filament tooling (Filament handles its own accessibility).
user-invocable: false
---

# Accessibility Review (SlipGuard)

Applies to customer-facing Blade/Livewire screens (see `livewire-product-interface`). Check against `docs/05-ux/UX_RULES.md`'s Accessibility section on every material UI change:

- Every input has a visible, associated `<label>` (`x-input-label` + matching `for`/`id`, already the pattern in the slip builder) — never a placeholder used as the only label.
- Every interactive element is keyboard-reachable and has a visible focus state — real `<button>`/`<a>` elements, not `<div onclick>`.
- Icon-only controls (e.g. the reorder chevrons, the notifications bell) get an `aria-label` describing the action, not just a visual icon.
- No meaning conveyed by color alone — status badges pair color with text (e.g. "Draft", "Ready"), never a bare colored dot.
- Validation errors are associated with their field (`x-input-error` under the relevant input) and written in plain language, not a generic "invalid input."
- Responsive by default — check at a narrow viewport, not just desktop, especially for the mobile nav menu pattern already established in `layout/navigation.blade.php`.

When reviewing existing code for a new feature, check the nearest existing screen doing something similar first (e.g. the slip builder for a new form) — consistency with established patterns is itself an accessibility and usability win.
