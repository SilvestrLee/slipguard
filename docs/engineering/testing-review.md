# Testing Review — Sprint E-06C Validation

**Stage:** 8 of 12 · **Date:** 2026-07-25

## Evidence

- Full suite: `vendor/bin/pest --compact` → `309/309 passing, 799 assertions, 12.7s` (Stage 3 result, re-cited here).
- Line counts and per-directory test counts surveyed via `wc -l` / grep across `tests/Feature` and `tests/Unit`. Largest files: `BettingSlipLifecycleTest.php` (371 lines), `AnalyzeBettingSlipTest.php` (232 lines), `NormalizeBettingSlipTest.php` (174 lines), `NormalizeFootballMarketTest.php` (177 lines).
- Grepped for weak-assertion smells (`expect(true)->toBeTrue()`, bare `assertStatus` with no content assertion), skip markers (`->skip(`, `markTestIncomplete`, `xit(`, `xtest(`), and TODOs — none found in business-logic tests.
- Read `tests/Unit/Risk/Engine/CalculateStructuralRiskVectorsTest.php` in full and `AnalyzeBettingSlipTest.php`'s test list.

## Findings

### Strengths (no action needed, recorded as evidence for the readiness gate)

- **Exact-value determinism protection**: `CalculateStructuralRiskVectorsTest` asserts all 19 scoreable canonical vectors (TV-001–TV-021, minus TV-011/012/013 which are gate/unavailable cases) against **exact** integer scores and risk bands from `RISK_RULE_SET_2026_1.md` §20 — no tolerance, no approximate matchers. This is the strongest possible protection against silent regression in the deterministic math the entire product depends on.
- **Boundary coverage**: TV-014 (3.99) vs TV-014b (4.00) explicitly tests a threshold edge. TV-015 explicitly re-tests TV-004 with reordered legs to assert order-independence.
- **Negative/ineligibility coverage**: `AnalyzeBettingSlipTest` covers Draft-rejection, empty-Draft-specific-reason, already-Analysed-rejection, Archived-rejection — each asserting the *specific* `AnalysisIneligibilityReason`, not just "it throws."
- **Architectural-boundary tests as executable proof, not just prose**: `AnalyzeBettingSlipTest::'the risk engine never persists anything itself'` and `'a betting slip can have at most one SlipAnalysis at the database level'` turn ADR-007's claims into regression-checked facts rather than documentation-only assertions.
- **Cross-user/ownership negative tests exist** (confirmed independently by the Stage 7 security review's citation of `SlipAnalysisPolicyTest`).
- Assertion density (799 assertions / 309 tests ≈ 2.6 per test) is healthy for this codebase's mix of unit math tests (many assertions per vector) and feature tests (fewer, request-shaped assertions).

### Findings requiring classification

| ID | Category | Finding |
|---|---|---|
| TR-1 | **C** | `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` are unmodified Laravel/Pest scaffold defaults (`expect(true)->toBeTrue()`, and a bare `GET /` 200-status check). They protect no SlipGuard-specific behaviour. Harmless, but candidates for deletion during any future test-suite pass — the root `/` 200-check has marginal value as a smoke test, the "true is true" test has none. |
| TR-2 | **C** | Test factories (`BettingSlipLegFactory`) use unseeded Faker randomness for `sport`/`decimal_odds` by default. Every test that depends on deterministic math correctly overrides these fields explicitly (confirmed by spot-check), so this poses no current flakiness risk — flagged only so a future contributor doesn't add a new determinism-sensitive test that forgets to override the factory defaults. |

No Category A or B findings. No flaky tests observed (query counts and assertions are exact-value, not timing-dependent; no `sleep()`, no unseeded-random assertions found in business-logic tests). No coverage gaps identified in the areas this sprint's code touches (persistence, engine, normalization, lifecycle) — Stage 5's and Stage 7's independent reviews corroborate this (immutability and authorization are both test-covered, not just implemented).

## Conclusion

The test suite is a genuine regression safety net, not coverage theater. Determinism-critical code (the risk engine, normalization) is protected by exact-value assertions against an independently-derived reference; architectural invariants (ADR-007) are protected by executable tests, not just documentation. The two scaffold leftovers are cosmetic. **No blockers.**
