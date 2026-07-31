# Release Demonstration Readiness (response to `PO-U18.1-RB3-001`)

Date: 2026-07-31
Authority: `PO-U18.1-RB3-001`, following `PO-U18.1-RV1-AC-001` Blocker Three
Status: implemented and verified (automated); browser walkthrough not performed in this environment — see §4

## Approach chosen: Option A — deterministic demonstration dataset

Not Option B (bounded live-provider environment): no real provider credentials exist in this environment, `MARKET_WIDE_PLANNER_ENABLED` is `false` by default per `U-17.4`'s no-public-launch constraint, and `U-17.4` §9 itself recommends fictional data for anything not already cleared for public/live exposure. A deterministic dataset is also the *stronger* review artifact — the same data is available every time, not subject to real-world fixture/odds availability at review time.

## What was built

`Database\Seeders\Demo\DemoMarketIntelligenceSeeder`, wired into the existing `slipguard:demo` command/`DemoWorkspaceSeeder` (the same demo-workspace convention `DemoSlipSeeder`/`DemoJournalSeeder` already established — nothing new invented).

**The load-bearing design point**: this seeder writes directly to the same `market_intelligence_fixtures`/`market_intelligence_market_quotes` tables the real, live `OddsApiProvider` writes to via `AcquireMarketEvidence`. `BuildAccumulatorCandidate::hasFreshEvidence()` finds this data already fresh (`cache_expires_at` two days out) and never calls the live provider. **Nothing about the deterministic pipeline itself is faked** — eligibility (`DetermineLegEligibility`), ranking (`RankEligibleLegs`), construction (`DiscoverAccumulatorSlots`), Customer Outcome Sovereignty (`U-17.6A`), whole-slip evaluation (`EvaluateCandidateSelections` → the real, unmodified `CalculateStructuralRisk`/`RankLegsByStructuralWeakness`), and Planner handoff (`CreateCandidateBettingSlip`) all run for real, unmodified, against fictional-but-realistic input.

**Content**: 18 fictional fixtures (6 per allowed competition — Premier League, La Liga, Serie A), each with quotes for the five default-enabled market families (Match Result, Total Goals, Double Chance, Draw No Bet, Both Teams to Score) plus Half-Time Result, using varied bookmaker keys per market family (matching the real `U-17.3` trial finding that no single bookmaker priced every family) and realistic decimal odds. Team names are real club names — matching the existing `DemoSlipSeeder` convention already used for Capability A's own demo slips, not a new pattern — but fixtures, dates, and prices are entirely fictional; no real live fixture or price is represented.

## Automated verification (real, run in this environment, not merely asserted)

`tests/Feature/MarketIntelligence/DemoMarketIntelligenceSeederTest.php` — 4 new tests, all passing:

1. The seeder produces 18 fresh fixtures.
2. A planning brief against the seeded data discovers exactly the requested leg count of eligible slots **with `Http::fake()` asserting zero requests sent** — proof the live provider is never touched, not merely an assumption.
3. **The complete pipeline** — discovery → simulated customer outcome choice → `EvaluateCandidateSelections` → `ConstructedCandidate` → `CreateCandidateBettingSlip` → a real `PlannerSession` with a `Ready` `BettingSlip` and 5 legs — runs end to end against the demo data.
4. Re-running the seeder is idempotent (18 fixtures, not 36).

Broader regression: `SlipguardDemoCommandTest` + full `MarketIntelligence` suite — **83/83 passing, 351 assertions**.

## Demonstration Guide

**Prerequisites**: local environment already set up (`README.md`'s "Running Locally"), `php artisan migrate` run.

**Execution steps**:
1. `MARKET_WIDE_PLANNER_ENABLED=true` in `.env` — checked directly against the component (`builder.blade.php`'s `capabilityEnabled()`), not assumed from `routes/web.php`'s own comment, which is stale: the route does not 404 while the flag is off, it renders a graceful "Experience preview" state instead. The flag stays `false` in the committed config default; this is a local-only override for the review session, matching exactly how `U-18.1`'s own browser verification was already performed ("feature flag supplied only to the local server process").
2. `php artisan slipguard:demo --fresh` — seeds the demo user, betting slips, journal entries, and now the Market Intelligence dataset. Prints the demo user's email and (first run only) a generated password.
3. `php artisan serve`, sign in as the printed demo user.
4. Visit `/plan-accumulator` — submit a planning brief (any of the three allowed competitions, default markets, 2-8 legs). Every discovered opportunity, chosen outcome, evaluated candidate, and Planner handoff exercises the real production code path against the seeded data — confirmed automatically per the tests above, not asserted here without evidence.
5. Dashboard/History/Planning History continuity: the accepted candidate becomes a real Planner session, visible in the dashboard's "recent sessions" region (`U-18.1`) and Planning History, using the same, already-verified code paths.

**Expected outcomes**: a constructed candidate should be reachable for any reasonable brief across all three competitions — 6 fixtures × 5+ market families per competition comfortably covers the 2-8 leg range. A brief requesting more legs than eligible slots exist, or an unreachable target-odds range, correctly returns an honest `No Valid Candidate` state (`CBR-00x`) — this is the pipeline working correctly, not a demo-data gap, per `U-17.6`/`U-17.7`'s own explicit design.

**Known limitations**:
- Half-Time Result is seeded but opt-in only (matches real product behaviour, not requested by default).
- Correct Score is seeded (for realistic acquisition-shape parity) but never selectable — `U-17.6` §7 excludes it from construction entirely, by design; this dataset doesn't work around that.
- No browser walkthrough was performed as part of this pass (see §4) — automated coverage proves the pipeline is exercised correctly; it does not substitute for looking at the rendered screens.

## Evidence Statement

| Category | Status |
|---|---|
| Live provider data | **None used or required.** `Http::fake()` in the automated test asserts zero requests sent when demo data is fresh. |
| Demonstration data | 18 fictional fixtures / ~108 market quotes, clearly documented as such in this report, the seeder's own docblock, and `TASKS.md`. Real club names (matching existing `DemoSlipSeeder` convention), fictional fixtures/dates/prices. |
| Unavailable integrations | Real `SPORTMONKS_API_KEY`/`THE_ODDS_API_KEY` credentials — not present in this environment; not required for this demonstration path. |
| Remaining external dependencies for a *real* (non-demo) launch | Unchanged by this work package: `U-17.4`'s own still-open written provider clarification (caching duration), and the standing external legal opinion `U-17.4`/`U-09` require before any public launch. Neither is addressed or claimed addressed here. |

## Explicit non-claims

This work package does not authorise public release, does not enable `MARKET_WIDE_PLANNER_ENABLED` by default, does not modify any deterministic rule, and does not touch `U-17`'s own accepted, closed scope — it adds a demonstration data path alongside it, following the same "operational data, not deterministic logic" boundary `AcquireMarketEvidence` already draws for its live counterpart.
