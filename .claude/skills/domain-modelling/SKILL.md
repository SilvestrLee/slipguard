---
name: domain-modelling
description: This skill should be used when designing a new business concept, a lifecycle/state machine, a value object, or an eligibility/validation rule — not just wiring up CRUD. Trigger phrases include "add a lifecycle", "model this as an enum", "what states can this be in", "centralize this validation", "is this eligible". Applies across app/Domain/**. Use alongside deterministic-risk-mathematics specifically for Risk-domain work.
user-invocable: false
---

# Domain Modelling Conventions (SlipGuard)

## When to Reach for a Domain Object

Create an enum, value object, or dedicated exception only when it removes real ambiguity or enforces an invariant that would otherwise be checked inconsistently in multiple places. Three `if` statements repeated in two files is a signal to centralize; three `if` statements that only ever appear once is not yet a problem.

## Established Shapes

- **Lifecycle as a backed enum with `allowedTransitions()`/`canTransitionTo()`**, not a bare string column with scattered `if ($status === 'x')` checks. See `App\Domain\BettingSlip\BettingSlipStatus`. Illegal transitions throw a dedicated exception (`InvalidBettingSlipTransitionException`), never fail silently or return false.
- **Eligibility as a value object with named reasons**, not a bare boolean. See `App\Domain\BettingSlip\AnalysisEligibility` + `AnalysisIneligibilityReason` — when something is "not eligible," the caller can always say why, from a fixed, testable set of reasons.
- **Validation rules centralized in one class the UI layer calls**, never re-derived inline in a Livewire component. See `BettingSlipValidationRules::slipRules()`/`messages()`.
- Keep the enum/value object and the model in sync: if the model has a method like `markReady()` that mirrors logic already expressed elsewhere (e.g. `analysisEligibility()`), have one call the other rather than maintaining two independent implementations of the same rule. This project has had this exact drift bug once (fixed) — don't reintroduce it.

## Anti-Patterns Seen and Corrected in This Project

- A business rule (e.g. "which statuses allow deletion") duplicated as a raw array literal in a policy AND a Blade view AND another Blade view, instead of one method on the enum all three call. Known, accepted debt here — don't add a fourth copy; if touching one of the three, consider consolidating into the enum.
- A lifecycle-changing Livewire method missing a catch for the transition exception, causing an uncaught 500 instead of a graceful flash message when a race condition hits an already-terminal state. Every state-transition call site needs a catch, matching the pattern already used in `markReady()`.
