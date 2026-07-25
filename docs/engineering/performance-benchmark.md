# Performance Benchmark Report — Sprint E-06C Validation

**Stage:** 6 of 12 · **Date:** 2026-07-25

## Methodology

A temporary Pest test (`tests/Feature/Analysis/ZZBenchmarkTest.php`, not committed — deleted immediately after this benchmark run, per the sprint's "do not add tests merely to measure, only to protect behaviour" principle) built Ready football slips of 1/5/10/20 legs using real factories and the actual `App\Actions\Analysis\AnalyzeBettingSlip` action end-to-end (normalize → engine → persist → transition). Each leg cycled through 5 real, recognized football markets (Match Result, Over 2.5 Goals, BTTS, Double Chance, Draw No Bet) to exercise the full factor set, not a single degenerate case.

For each leg count: one untimed warm-up run, then 20 timed iterations. Per iteration: `hrtime(true)` around `execute()`, `memory_get_usage()` delta, and `DB::getQueryLog()` count (query logging enabled only for the measured call).

**Environment:** PHP 8.5.8 (Homebrew CLI, `/usr/local/bin/php`), Laravel 13.21.1, SQLite `:memory:` (the project's standard test database per `phpunit.xml`), macOS (Darwin 21.6.0), single-threaded synchronous execution, no queue/cache warm infrastructure — i.e. the same environment as the rest of this validation sprint.

## Results

| Legs | Min (ms) | Median (ms) | Max (ms) | Avg (ms) | Avg Memory (bytes) | Queries |
|---:|---:|---:|---:|---:|---:|---:|
| 1 | 2.077 | 2.233 | 4.525 | 2.419 | 17,566 | 5 |
| 5 | 3.966 | 4.856 | 6.099 | 4.757 | 24,491 | 9 |
| 10 | 6.040 | 6.589 | 9.496 | 6.875 | 34,529 | 14 |
| 20 | 10.499 | 11.459 | 22.472 | 12.582 | 50,011 | 24 |

Query count is deterministic per leg count across all 20 iterations (`queries_consistent: true`) — no query-count variance, confirming no conditional/random query paths.

## Analysis

- **Scaling is linear**, ~4 base queries (persist `SlipAnalysis`, transition the slip, plus supporting selects) **+ 1 query per leg** — because `AnalyzeBettingSlip::execute()` calls `LegAnalysis::create()` inside a per-leg `foreach` loop (`app/Actions/Analysis/AnalyzeBettingSlip.php:67-82`) rather than a single batched `insert()`. At 20 legs this is 20 separate `INSERT` statements in one transaction.
- Time scales similarly linearly, ~0.5ms per additional leg. No superlinear behaviour (no N² pattern, no missing eager-load).
- Memory growth is modest and linear (~1.7KB/leg), no leaks or unbounded accumulation across iterations.
- No documented end-to-end persistence SLA exists in `docs/03-data-science/RISK_RULE_SET_2026_1.md` or `docs/07-quality/QUALITY_STRATEGY.md` — the only recorded target is the **pure engine calculation** (§13: "sub-millisecond ... at the 20-leg ceiling"), already validated separately in E-06B (0.615ms). This benchmark measures the full pipeline including a real DB transaction and per-leg inserts, which is a different (and necessarily larger) number than the pure-math figure; the two should not be conflated when reading `TASKS.md`.
- 20-leg end-to-end median of ~11.5ms is well within any reasonable web-request budget (a synchronous form submission tolerates hundreds of ms), so this is **not a release blocker**.

## Findings

| ID | Category | Finding |
|---|---|---|
| PB-1 | **C** | `LegAnalysis` rows are inserted one-by-one in a loop instead of a single batched `insert()`/`insertMany()`. Linear but avoidable per-query overhead; worth revisiting if leg counts grow materially beyond today's 20-leg cap or if analysis becomes a high-frequency operation (e.g. bulk re-analysis). Not worth changing now — correct, deterministic, and fast enough at current scale, per the sprint's "don't optimise prematurely" principle. |
| PB-2 | **C** | No formally recorded end-to-end (normalize+engine+persist) performance target exists, only the pure-engine sub-millisecond target. Recommend Product Office/Engineering agree a persistence-inclusive SLA once a real request-path benchmark (HTTP round trip, not just the action) is possible — i.e. once U-02's report screen exists. |

No Category A or B findings — performance is well within any plausible product requirement at current scale.

## Conclusion

Performance is **not a release blocker**. The engine + persistence pipeline scales linearly and predictably from 1 to 20 legs, with the 20-leg ceiling completing in low double-digit milliseconds — an order of magnitude below anything a customer would perceive as slow. The one identified inefficiency (per-leg insert loop) is real but immaterial at current scale and correctly deferred rather than "fixed" preemptively.
