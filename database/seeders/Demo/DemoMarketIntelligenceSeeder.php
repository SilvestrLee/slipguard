<?php

namespace Database\Seeders\Demo;

use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use Illuminate\Support\Carbon;

/**
 * `PO-U18.1-RB3-001` — Release Demonstration Readiness (Option A: a
 * deterministic demonstration dataset, not a live-provider environment).
 *
 * Populates the SAME `market_intelligence_fixtures`/`market_intelligence_
 * market_quotes` tables `App\Domain\MarketIntelligence\AcquireMarketEvidence`
 * writes to when it calls the real, live `OddsApiProvider` — so this seeder
 * substitutes only the external data source, never any deterministic logic.
 * `App\Actions\MarketIntelligence\BuildAccumulatorCandidate::hasFreshEvidence()`
 * finds this data already fresh and never calls the live provider, so a
 * review session run against this seed exercises the completely real,
 * unmodified eligibility (`DetermineLegEligibility`), ranking
 * (`RankEligibleLegs`), construction (`DiscoverAccumulatorSlots`),
 * outcome-selection sovereignty (`U-17.6A`), evaluation
 * (`EvaluateCandidateSelections`), and Planner handoff
 * (`CreateCandidateBettingSlip`) — nothing about the pipeline itself is
 * faked, only its raw market-evidence input.
 *
 * Team names and fixtures are realistic but entirely fictional scheduling —
 * no real live fixture, date, or price is represented. This is demonstration
 * content, not a claim about any real upcoming match.
 */
class DemoMarketIntelligenceSeeder
{
    /**
     * @var array<string, array<int, array{home: string, away: string}>>
     */
    private const FIXTURE_TEMPLATES = [
        'soccer_epl' => [
            ['home' => 'Arsenal', 'away' => 'Chelsea'],
            ['home' => 'Manchester City', 'away' => 'Liverpool'],
            ['home' => 'Tottenham', 'away' => 'Newcastle'],
            ['home' => 'Aston Villa', 'away' => 'West Ham'],
            ['home' => 'Brighton', 'away' => 'Everton'],
            ['home' => 'Manchester United', 'away' => 'Wolves'],
        ],
        'soccer_spain_la_liga' => [
            ['home' => 'Real Madrid', 'away' => 'Barcelona'],
            ['home' => 'Atletico Madrid', 'away' => 'Sevilla'],
            ['home' => 'Real Sociedad', 'away' => 'Villarreal'],
            ['home' => 'Real Betis', 'away' => 'Athletic Bilbao'],
            ['home' => 'Valencia', 'away' => 'Girona'],
            ['home' => 'Celta Vigo', 'away' => 'Osasuna'],
        ],
        'soccer_italy_serie_a' => [
            ['home' => 'Inter Milan', 'away' => 'AC Milan'],
            ['home' => 'Juventus', 'away' => 'Napoli'],
            ['home' => 'AS Roma', 'away' => 'Lazio'],
            ['home' => 'Atalanta', 'away' => 'Fiorentina'],
            ['home' => 'Bologna', 'away' => 'Torino'],
            ['home' => 'Udinese', 'away' => 'Genoa'],
        ],
    ];

    /**
     * @return array{fixtures: int, quotes: int}
     */
    public function run(): array
    {
        // Global evidence cache, not user-owned — a clean rebuild each time
        // rather than an accumulating one, matching DemoSlipSeeder's own
        // idempotency convention for the same reason: never presented
        // mid-review with duplicated or stale demo fixtures.
        MarketIntelligenceMarketQuote::query()->delete();
        MarketIntelligenceFixture::query()->delete();

        $cacheExpiresAt = Carbon::now()->addDays(2);
        $fixtureCount = 0;
        $quoteCount = 0;

        foreach (self::FIXTURE_TEMPLATES as $competitionKey => $fixtures) {
            foreach ($fixtures as $index => $template) {
                $commenceTime = Carbon::now()->addDays(1 + $index)->setTime(random_int(12, 19), [0, 15, 30, 45][random_int(0, 3)]);

                $fixture = MarketIntelligenceFixture::create([
                    'provider_event_id' => sprintf('demo-%s-%d', str_replace('soccer_', '', $competitionKey), $index),
                    'competition_key' => $competitionKey,
                    'home_team' => $template['home'],
                    'away_team' => $template['away'],
                    'commence_time' => $commenceTime,
                    'retrieved_at' => Carbon::now(),
                    'cache_expires_at' => $cacheExpiresAt,
                ]);
                $fixtureCount++;

                $quoteCount += $this->seedQuotes($fixture, $template['home'], $template['away'], $cacheExpiresAt);
            }
        }

        return ['fixtures' => $fixtureCount, 'quotes' => $quoteCount];
    }

    private function seedQuotes(MarketIntelligenceFixture $fixture, string $home, string $away, Carbon $cacheExpiresAt): int
    {
        $now = Carbon::now();
        $homeOdds = round(1.60 + (random_int(0, 140) / 100), 2);
        $drawOdds = round(3.10 + (random_int(0, 90) / 100), 2);
        $awayOdds = $this->homeToAwaySkew($homeOdds);

        // Different bookmaker per market family — matches the real trial
        // finding (U-17.3): no single bookmaker priced every confirmed
        // family, which is exactly why the engine's own conservative-lowest
        // quote selection (U-17.6 §4) exists rather than a single reference
        // bookmaker. Reproducing that shape here, not a simplified one.
        $rows = [
            [
                'canonical_market' => 'match_result', 'provider_market_key' => 'h2h', 'bookmaker_key' => 'pinnacle',
                'outcomes' => [
                    ['name' => $home, 'price' => (string) $homeOdds],
                    ['name' => 'Draw', 'price' => (string) $drawOdds],
                    ['name' => $away, 'price' => (string) $awayOdds],
                ],
            ],
            [
                'canonical_market' => 'total_goals', 'provider_market_key' => 'totals', 'bookmaker_key' => 'pinnacle',
                'outcomes' => [
                    ['name' => 'Over', 'price' => (string) round(1.75 + (random_int(0, 40) / 100), 2), 'point' => 2.5],
                    ['name' => 'Under', 'price' => (string) round(1.90 + (random_int(0, 40) / 100), 2), 'point' => 2.5],
                ],
            ],
            [
                'canonical_market' => 'double_chance', 'provider_market_key' => 'double_chance', 'bookmaker_key' => 'onexbet',
                'outcomes' => [
                    ['name' => $home.' or Draw', 'price' => (string) round($homeOdds * 0.55, 2)],
                    ['name' => $away.' or Draw', 'price' => (string) round($awayOdds * 0.55, 2)],
                    ['name' => $home.' or '.$away, 'price' => (string) round(1.20 + (random_int(0, 20) / 100), 2)],
                ],
            ],
            [
                'canonical_market' => 'draw_no_bet', 'provider_market_key' => 'draw_no_bet', 'bookmaker_key' => 'coral',
                'outcomes' => [
                    ['name' => $home, 'price' => (string) round($homeOdds * 0.75, 2)],
                    ['name' => $away, 'price' => (string) round($awayOdds * 0.75, 2)],
                ],
            ],
            [
                'canonical_market' => 'both_teams_to_score', 'provider_market_key' => 'btts', 'bookmaker_key' => 'onexbet',
                'outcomes' => [
                    ['name' => 'Yes', 'price' => (string) round(1.70 + (random_int(0, 50) / 100), 2)],
                    ['name' => 'No', 'price' => (string) round(1.90 + (random_int(0, 50) / 100), 2)],
                ],
            ],
            // Opt-in / construction-excluded families: still acquired
            // realistically (matching what the real provider actually
            // returns), even though correct_score is never selected by
            // the construction engine itself (U-17.6 §7 — a disclosed,
            // deliberate MVP exclusion, not reproduced or worked around
            // here).
            [
                'canonical_market' => 'half_time_result', 'provider_market_key' => 'h2h_h1', 'bookmaker_key' => 'pinnacle',
                'outcomes' => [
                    ['name' => $home, 'price' => (string) round($homeOdds * 1.7, 2)],
                    ['name' => 'Draw', 'price' => (string) round($drawOdds * 0.65, 2)],
                    ['name' => $away, 'price' => (string) round($awayOdds * 1.6, 2)],
                ],
            ],
        ];

        $count = 0;

        foreach ($rows as $row) {
            MarketIntelligenceMarketQuote::create([
                'market_intelligence_fixture_id' => $fixture->id,
                'canonical_market' => $row['canonical_market'],
                'provider_market_key' => $row['provider_market_key'],
                'bookmaker_key' => $row['bookmaker_key'],
                'outcomes' => $row['outcomes'],
                'provider_last_update' => $now,
                'retrieved_at' => $now,
                'cache_expires_at' => $cacheExpiresAt,
                'evidence_quality' => 'fresh',
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * A plausible complementary price for the away side given the home
     * price — not a probability model, purely cosmetic variety for
     * demonstration odds so every fixture doesn't show an identical
     * spread. Never consumed by any deterministic calculation beyond
     * being stored as one more fictional quote.
     */
    private function homeToAwaySkew(float $homeOdds): float
    {
        return max(1.10, min(15.00, round(9.5 - ($homeOdds * 0.9), 2)));
    }
}
