# Security Review — Sprint E-06C Validation

**Stage:** 7 of 12 · **Date:** 2026-07-25

## Scope

Authorization, mass assignment, injection risk, validation, and historical-immutability from a trust-boundary angle, across `BettingSlip`/`BettingSlipLeg`/`SlipAnalysis`/`LegAnalysis` and existing auth flows.

## Evidence

**Policies** (`app/Policies/`): `SlipAnalysisPolicy::view` — `$user->id === $slipAnalysis->user_id`. No `create`/`update`/`delete` methods exist on it at all, so Laravel's policy `Gate` denies those abilities by default (`AuthorizationException`) rather than falling open. `BettingSlipPolicy` scopes `view`/`update`/`delete` to `$user->id === $bettingSlip->user_id`; `delete` additionally restricts to Draft/Ready status. `UserPolicy` uses `$user->is($model)`. Policies rely on Laravel's model-name auto-discovery convention (`App\Models\X` → `App\Policies\XPolicy`); no `AuthServiceProvider`/policy-map registration exists or is needed for that convention, and `SlipAnalysisPolicyTest.php` (2 tests) exercises it end-to-end (owner allowed, intruder denied).

**Mass assignment:** `SlipAnalysis::$fillable` deliberately excludes `user_id`; `AnalyzeBettingSlip::execute()` (`app/Actions/Analysis/AnalyzeBettingSlip.php:53`) sets `$slipAnalysis->user_id = $bettingSlip->user_id` directly (not via `fill()`), then fills the remaining engine-output fields. `LegAnalysis::create()` is called with a fully server-constructed array from the normalized/engine output — no request input reaches either model's mass assignment. Both models are only ever written from inside `AnalyzeBettingSlip`, which takes a `BettingSlip` (already authorized upstream) and produces output entirely from server-side computation — no controller or Livewire component calls `SlipAnalysis::create`/`LegAnalysis::create`/`->fill()` with request data anywhere in the codebase.

**Route/UI surface:** `routes/web.php` exposes no route that creates, edits, or even *views* a `SlipAnalysis` yet — `history`/`journal` are still `coming-soon` placeholders, and no controller invokes `AnalyzeBettingSlip`. `SlipAnalysisPolicy::view` is therefore currently exercised only by direct Pest calls (`$user->can('view', $analysis)`), not by an HTTP request path. This isn't a vulnerability — nothing is reachable — but it means the authorization enforcement point has no live request-level test yet (see Testing Review, Stage 8).

**Injection:** no `DB::raw`, `whereRaw`, `selectRaw`, or string-interpolated query found anywhere under `app/` (`grep -rn "DB::raw\|whereRaw\|selectRaw"` — zero matches). All persistence goes through Eloquent with bound parameters.

**Validation:** every customer-controlled leg field (`sport`, `competition`, `event_name`, `market_name`, `selection_name`, `decimal_odds`) is validated centrally in `App\Domain\BettingSlip\BettingSlipValidationRules` (`required`/`string`/`max:255`, `decimal_odds` constrained `numeric`, `min:1.01`, `max:1000`), invoked via Livewire's `$this->validate()` in `builder.blade.php:118` — not raw `$request->all()`. `BettingSlipLeg::isComplete()` re-derives the same completeness check server-side as defense in depth before the Risk Engine ever sees a leg.

**Ownership on mutating actions:** every mutating Livewire action (`builder.blade.php`, `index.blade.php`) calls `$this->authorize(...)` before acting — `create`, `update` (save, add/remove/reorder leg, lifecycle transitions), `delete` — 9 call sites, all against the model instance, never a client-supplied ID interpreted as already-trusted.

**Historical immutability (trust-boundary angle):** no controller, Livewire component, or policy ability exists to update or delete a `SlipAnalysis`/`LegAnalysis` once created — `SlipAnalysisPolicy` has no `update`/`delete` method (denied by default), and no code path calls `->update()`/`->save()`/`->delete()` on either model outside their creation inside `AnalyzeBettingSlip`'s single transaction. An authenticated user has no reachable action that mutates a persisted analysis.

**Auth flows (spot check):** registration/login/password-reset Livewire components unchanged from E-02 baseline; `User::$fillable` via `#[Fillable(['name','email','password'])]` attribute excludes `is_internal`, so self-registration cannot grant panel access — consistent with prior sprints' hardening. No regression observed.

## Findings

| ID | Category | Finding |
|---|---|---|
| SEC-1 | **D** | `SlipAnalysisPolicy::view` and the whole persisted-analysis layer have no live HTTP route yet (no "view report"/history page). Not a defect — it's simply unbuilt — but flagged so Stage 8/11 don't credit it with request-level test coverage it doesn't have. Product/Engineering scheduling observation, not a fix Engineering should make unilaterally. |
| SEC-2 | **C** | No policy explicitly denies `create`/`update`/`delete` on `SlipAnalysis` — correctness currently relies on Laravel's "no method ⇒ denied" default plus the absence of any call site, rather than an explicit `return false`. Low risk today (nothing calls it), but a future engineer adding a route could add ad hoc authorization logic instead of relying on the implicit deny. Consider adding explicit `create`/`update`/`delete` methods returning `false` with a comment, once a real use case forces the question — not before. |

No Category A or B findings. Authorization, mass assignment, input validation, and injection surfaces are all correctly enforced for every currently-reachable code path.

## Conclusion

No exploitable authorization bypass, mass-assignment hole, or injection vector found. The one substantive observation (SEC-1) is a scope/coverage note, not a defect: the persistence layer is authorization-correct but not yet wired to any customer-facing route, so its policy enforcement is proven only at the unit/model level. Security posture for what exists today is sound.
