<?php

use App\Domain\MarketIntelligence\Construction\CandidateExclusionReason;
use App\Domain\MarketIntelligence\Construction\DiscoverAccumulatorSlots;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;

function discoveryBrief(array $overrides = []): PlanningBrief
{
    return new PlanningBrief(
        competitions: $overrides['competitions'] ?? ['soccer_epl'],
        windowDays: $overrides['windowDays'] ?? 7,
        legCountTarget: $overrides['legCountTarget'] ?? 5,
        requestedMarkets: $overrides['requestedMarkets'] ?? ['h2h'],
        targetOddsMin: null,
        targetOddsMax: null,
    );
}

function fixtureWithMatchResultQuote(array $fixtureOverrides = [], ?array $outcomeOverrides = null): MarketIntelligenceFixture
{
    $fixture = MarketIntelligenceFixture::factory()->create($fixtureOverrides);

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => $outcomeOverrides ?? [
            ['name' => 'Arsenal', 'price' => '1.85'],
            ['name' => 'Draw', 'price' => '3.60'],
            ['name' => 'Chelsea', 'price' => '4.50'],
        ],
    ]);

    return $fixture;
}

test('each eligible fixture becomes exactly one ranked slot with all surviving outcomes', function () {
    fixtureWithMatchResultQuote();
    fixtureWithMatchResultQuote();

    $result = (new DiscoverAccumulatorSlots)->execute(discoveryBrief());

    expect($result['slots'])->toHaveCount(2);
    expect($result['slots'][0]->rank)->toBe(1);
    expect($result['slots'][1]->rank)->toBe(2);
    expect($result['slots'][0]->options)->toHaveCount(3);
});

test('an ineligible fixture contributes no slot but is recorded as excluded', function () {
    // A competition the brief itself requests but that isn't on the real,
    // Product-Office-enabled config allowlist (CBE-001) — the fixture must
    // still be fetched (it matches the brief's own competitions list) for
    // this specific gate to have anything to reject.
    fixtureWithMatchResultQuote(['competition_key' => 'soccer_bundesliga']);

    $result = (new DiscoverAccumulatorSlots)->execute(discoveryBrief(['competitions' => ['soccer_bundesliga']]));

    expect($result['slots'])->toBeEmpty();
    expect($result['excluded'])->not->toBeEmpty();
    expect($result['excluded'][0]->reason)->toBe(CandidateExclusionReason::CompetitionNotEnabled);
});

test('only one slot is ever produced per fixture, even across multiple requested markets', function () {
    $fixture = fixtureWithMatchResultQuote();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'canonical_market' => 'total_goals',
        'provider_market_key' => 'totals',
        'outcomes' => [
            ['name' => 'Over', 'price' => '1.90', 'point' => '2.5'],
            ['name' => 'Under', 'price' => '1.90', 'point' => '2.5'],
        ],
    ]);

    $result = (new DiscoverAccumulatorSlots)->execute(discoveryBrief(['requestedMarkets' => ['h2h', 'totals']]));

    expect($result['slots'])->toHaveCount(1);
    $fixtureAlreadyUsedReasons = array_filter(
        $result['excluded'],
        fn ($e) => $e->reason === CandidateExclusionReason::FixtureAlreadyUsed,
    );
    expect($fixtureAlreadyUsedReasons)->not->toBeEmpty();
});

test('slots rank the market-preference order named first in the planning brief above later ones', function () {
    $fixtureA = fixtureWithMatchResultQuote();

    MarketIntelligenceMarketQuote::factory()->for($fixtureA, 'fixture')->create([
        'canonical_market' => 'total_goals',
        'provider_market_key' => 'totals',
        'outcomes' => [
            ['name' => 'Over', 'price' => '1.90', 'point' => '2.5'],
            ['name' => 'Under', 'price' => '1.90', 'point' => '2.5'],
        ],
    ]);

    $fixtureB = MarketIntelligenceFixture::factory()->create(['provider_event_id' => 'fixture-only-totals']);
    MarketIntelligenceMarketQuote::factory()->for($fixtureB, 'fixture')->create([
        'canonical_market' => 'total_goals',
        'provider_market_key' => 'totals',
        'outcomes' => [
            ['name' => 'Over', 'price' => '1.90', 'point' => '2.5'],
            ['name' => 'Under', 'price' => '1.90', 'point' => '2.5'],
        ],
    ]);

    // fixtureA has BOTH h2h and totals evidence, but CBE-008 only keeps one
    // slot per fixture — since 'h2h' is listed first in requestedMarkets,
    // fixtureA's h2h slot should outrank fixtureB's totals-only slot.
    $result = (new DiscoverAccumulatorSlots)->execute(discoveryBrief(['requestedMarkets' => ['h2h', 'totals']]));

    expect($result['slots'])->toHaveCount(2);
    expect($result['slots'][0]->options[0]->marketFamily->value)->toBe('match_result');
});
