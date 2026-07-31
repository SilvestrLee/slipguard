<?php

use App\Domain\MarketIntelligence\Construction\MapCanonicalMarketToNormalizedMarket;
use App\Domain\MarketIntelligence\Construction\SelectedOutcomeQuote;
use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;
use Carbon\CarbonImmutable;

function makeQuote(string $outcomeName, ?string $point = null): SelectedOutcomeQuote
{
    return new SelectedOutcomeQuote(
        outcomeName: $outcomeName,
        price: '2.00',
        point: $point,
        bookmakerKey: 'pinnacle',
        retrievedAt: CarbonImmutable::now(),
        providerLastUpdate: CarbonImmutable::now(),
        evidenceQuality: EvidenceQuality::Fresh,
        cacheExpiresAt: CarbonImmutable::now()->addHours(2),
    );
}

function makeConstructionFixture(): MarketIntelligenceFixture
{
    return new MarketIntelligenceFixture([
        'provider_event_id' => 'fixture-1',
        'competition_key' => 'soccer_epl',
        'home_team' => 'Arsenal',
        'away_team' => 'Chelsea',
        'commence_time' => CarbonImmutable::now()->addDay(),
        'retrieved_at' => CarbonImmutable::now(),
        'cache_expires_at' => CarbonImmutable::now()->addHours(2),
    ]);
}

test('Match Result maps home, away, and draw outcomes correctly', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    $home = $mapper->map(MarketFamily::MatchResult, $fixture, makeQuote('Arsenal'));
    $away = $mapper->map(MarketFamily::MatchResult, $fixture, makeQuote('Chelsea'));
    $draw = $mapper->map(MarketFamily::MatchResult, $fixture, makeQuote('Draw'));

    expect($home->selectionDescription)->toBe('Arsenal to Win');
    expect($away->selectionDescription)->toBe('Chelsea to Win');
    expect($draw->selectionDescription)->toBe('Draw');
    expect($home->normalizedMarket->marketFamily)->toBe(MarketFamily::MatchResult);
});

test('an outcome name matching neither team nor Draw is excluded, not guessed', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    expect($mapper->map(MarketFamily::MatchResult, $fixture, makeQuote('Liverpool')))->toBeNull();
});

test('Half-Time Result reuses the same mapping with half-time-specific copy', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    $result = $mapper->map(MarketFamily::HalfTimeResult, $fixture, makeQuote('Arsenal'));

    expect($result->selectionDescription)->toBe('Arsenal to Win at Half-Time');
});

test('Draw No Bet maps home and away only, no draw outcome exists', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    expect($mapper->map(MarketFamily::DrawNoBet, $fixture, makeQuote('Arsenal'))->selectionDescription)
        ->toBe('Arsenal (Draw No Bet)');
    expect($mapper->map(MarketFamily::DrawNoBet, $fixture, makeQuote('Draw')))->toBeNull();
});

test('Total Goals maps direction and line from the outcome name and point', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    $over = $mapper->map(MarketFamily::TotalGoals, $fixture, makeQuote('Over', '2.5'));

    expect($over->selectionDescription)->toBe('Over 2.5 Goals');
    expect($over->normalizedMarket->selectionFacts)->toBe(['direction' => 'over', 'line' => '2.5']);
});

test('Total Goals is excluded when the point value is missing, never guessed', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    expect($mapper->map(MarketFamily::TotalGoals, $fixture, makeQuote('Over', null)))->toBeNull();
});

test('Both Teams to Score maps Yes/No to a selection code', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    $yes = $mapper->map(MarketFamily::BothTeamsToScore, $fixture, makeQuote('Yes'));

    expect($yes->normalizedMarket->selectionFacts)->toBe(['selection_code' => 'yes']);
    expect($mapper->map(MarketFamily::BothTeamsToScore, $fixture, makeQuote('Maybe')))->toBeNull();
});

test('Double Chance maps home-or-draw, draw-or-away, and home-or-away combinations', function () {
    $fixture = makeConstructionFixture();
    $mapper = new MapCanonicalMarketToNormalizedMarket;

    expect($mapper->map(MarketFamily::DoubleChance, $fixture, makeQuote('Arsenal or Draw'))->selectionDescription)
        ->toBe('Arsenal or Draw');
    expect($mapper->map(MarketFamily::DoubleChance, $fixture, makeQuote('Draw or Chelsea'))->selectionDescription)
        ->toBe('Draw or Chelsea');
    expect($mapper->map(MarketFamily::DoubleChance, $fixture, makeQuote('Arsenal or Chelsea'))->selectionDescription)
        ->toBe('Arsenal or Chelsea');
});
