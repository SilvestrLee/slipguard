---
name: security-authorization
description: This skill should be used when adding a new customer-owned resource, a new mutating Livewire action, a new route, or any code path that reads or writes data scoped to a specific user. Trigger phrases include "add ownership", "who can access this", "authorize this action", "new customer resource", "policy for this model". Applies whenever ownership, access control, or trust boundaries are involved.
user-invocable: false
---

# Security & Authorization Conventions (SlipGuard)

## The Ownership Pattern (established, replicate exactly)

For every customer-owned model:

1. Exclude the owning foreign key (`user_id`) from `$fillable`. Set it explicitly from `Auth::user()->id` in the constructor/action, never from a client-supplied array. See `BettingSlip`.
2. Write a dedicated Policy: `viewAny`/`create` open to any authenticated user where appropriate, `view`/`update`/`delete` scoped to `$user->id === $model->user_id` (or the current lifecycle-aware equivalent — see `BettingSlipPolicy::delete()`, which additionally restricts by status).
3. Every Livewire method that mutates or views a specific instance calls `$this->authorize(...)` itself, even inside `mount()` — never rely solely on route middleware, since Livewire components can be invoked in ways middleware doesn't fully cover.
4. Every index/list query is scoped through the relation (`Auth::user()->bettingSlips()`), never a bare `Model::all()` or `Model::where('user_id', request('user_id'))` trusting client input.

## Non-Negotiables

- Never trust an ownership ID from the client — route-model-bound instances still get policy-checked, not assumed correct because the route resolved.
- Guests get redirected to login; non-owners get a 403, not a redirect or a silent empty result.
- Internal (`is_internal`) staff status is unrelated to customer-resource ownership — an internal user does not implicitly gain access to another customer's data through the customer-facing app; internal tooling lives in Filament (see `filament-operations`) with its own authorization.

## Test Requirement

Every new ownership rule needs at minimum: guest-redirected, owner-succeeds, non-owner-forbidden. See `tests/Feature/BettingSlipManagementTest.php` and `BettingSlipLifecycleTest.php` for the pattern. A policy change (e.g. narrowing which statuses permit an action) needs a test for the non-owner case combined with the new condition, not just the new condition alone in isolation — a policy is an AND of multiple checks, and each combination needs coverage.
