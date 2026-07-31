<?php

namespace App\Domain\MarketIntelligence;

use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * U-17.5 §4 — the bulk-first, per-event-reduced acquisition sequence.
 * This is the one place the request-cost finding from `U-17.3` (5 of 7
 * confirmed markets cost one request per fixture) is actually acted on:
 * bulk markets are always acquired first, and per-event markets are only
 * ever requested for fixtures that exist after that cheap step — never
 * for a fixture that could have been excluded for free.
 *
 * This orchestration action does NOT decide which fixtures are
 * structurally eligible for a customer's candidate (that is `U-17.6`'s
 * own, separately-implemented rule engine) — it only acquires and
 * persists evidence. Keeping that boundary here, not blurring it, is
 * what lets this package ship before the construction rules exist.
 */
class AcquireMarketEvidence
{
    public function __construct(
        private readonly MarketEvidenceProvider $provider = new OddsApiProvider,
    ) {}

    /**
     * @param  array<int, string>  $requestedMarkets  Provider market keys, e.g. ['h2h', 'totals', 'btts']
     * @return array{fixtures: int, quotes: int}
     */
    public function execute(string $competitionKey, array $requestedMarkets): array
    {
        if (! array_key_exists($competitionKey, config('slipguard-market-intelligence.allowed_competitions'))) {
            throw new RuntimeException("Competition [{$competitionKey}] is not on the allowed-competitions list.");
        }

        $bulkMarkets = array_values(array_intersect($requestedMarkets, config('slipguard-market-intelligence.bulk_markets')));
        $perEventMarkets = array_values(array_intersect($requestedMarkets, config('slipguard-market-intelligence.per_event_markets')));

        $bulkResult = $this->provider->bulkAcquire(
            $competitionKey,
            $bulkMarkets !== [] ? $bulkMarkets : config('slipguard-market-intelligence.bulk_markets')
        );

        $fixtures = $bulkResult['fixtures'];
        $bulkQuotes = $bulkMarkets !== [] ? $bulkResult['quotes'] : [];

        // The request-budget guard (U-17.5 §8): every remaining fixture
        // would need one request per per-event market requested. Refuse
        // before issuing any of them if that would exceed the configured
        // ceiling, rather than partially acquiring and silently stopping.
        $projectedPerEventRequests = count($fixtures) * count($perEventMarkets);
        $budget = config('slipguard-market-intelligence.max_per_event_requests');

        if ($projectedPerEventRequests > $budget) {
            throw new RuntimeException(
                "Projected per-event requests ({$projectedPerEventRequests}) exceed the configured budget ({$budget}). ".
                'Reduce the competition/market selection before retrying.'
            );
        }

        $perEventQuotes = [];

        if ($perEventMarkets !== []) {
            foreach ($fixtures as $fixture) {
                $perEventQuotes = [
                    ...$perEventQuotes,
                    ...$this->provider->eventMarketQuotes($competitionKey, $fixture->providerEventId, $perEventMarkets),
                ];
            }
        }

        return DB::transaction(function () use ($fixtures, $bulkQuotes, $perEventQuotes) {
            $fixtureModels = $this->persistFixtures($fixtures);
            $quoteCount = $this->persistQuotes([...$bulkQuotes, ...$perEventQuotes], $fixtureModels);

            return ['fixtures' => count($fixtureModels), 'quotes' => $quoteCount];
        });
    }

    /**
     * @param  array<int, CanonicalFixture>  $fixtures
     * @return array<string, MarketIntelligenceFixture> keyed by provider_event_id
     */
    private function persistFixtures(array $fixtures): array
    {
        $ttl = now()->addHours(config('slipguard-market-intelligence.cache_ttl_hours'));
        $models = [];

        foreach ($fixtures as $fixture) {
            $models[$fixture->providerEventId] = MarketIntelligenceFixture::updateOrCreate(
                ['provider_event_id' => $fixture->providerEventId],
                [
                    'competition_key' => $fixture->competitionKey,
                    'home_team' => $fixture->homeTeam,
                    'away_team' => $fixture->awayTeam,
                    'commence_time' => $fixture->commenceTime,
                    'retrieved_at' => $fixture->retrievedAt,
                    'cache_expires_at' => $ttl,
                ]
            );
        }

        return $models;
    }

    /**
     * @param  array<int, CanonicalMarketQuote>  $quotes
     * @param  array<string, MarketIntelligenceFixture>  $fixtureModels
     */
    private function persistQuotes(array $quotes, array $fixtureModels): int
    {
        $ttl = now()->addHours(config('slipguard-market-intelligence.cache_ttl_hours'));
        $count = 0;

        foreach ($quotes as $quote) {
            $fixtureModel = $fixtureModels[$quote->providerEventId] ?? null;

            if (! $fixtureModel) {
                continue;
            }

            MarketIntelligenceMarketQuote::updateOrCreate(
                [
                    'market_intelligence_fixture_id' => $fixtureModel->id,
                    'canonical_market' => $quote->canonicalMarket,
                    'bookmaker_key' => $quote->bookmakerKey,
                ],
                [
                    'provider_market_key' => $quote->providerMarketKey,
                    'outcomes' => $quote->outcomes,
                    'provider_last_update' => $quote->providerLastUpdate,
                    'retrieved_at' => $quote->retrievedAt,
                    'cache_expires_at' => $ttl,
                    'evidence_quality' => $quote->evidenceQuality,
                ]
            );

            $count++;
        }

        return $count;
    }
}
