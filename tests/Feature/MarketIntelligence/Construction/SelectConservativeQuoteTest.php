<?php

use App\Domain\MarketIntelligence\Construction\SelectConservativeQuote;
use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;

test('the lowest valid price across bookmakers is selected for each outcome', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'bookmaker_key' => 'pinnacle',
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85'], ['name' => 'Draw', 'price' => '3.60']],
    ]);

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'bookmaker_key' => 'onexbet',
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.70'], ['name' => 'Draw', 'price' => '3.80']],
    ]);

    $selected = (new SelectConservativeQuote)->forFixtureMarket($fixture, 'match_result');

    expect($selected['Arsenal']->price)->toBe('1.70')
        ->and($selected['Arsenal']->bookmakerKey)->toBe('onexbet')
        ->and($selected['Draw']->price)->toBe('3.60')
        ->and($selected['Draw']->bookmakerKey)->toBe('pinnacle');
});

test('an exact price tie is broken by the alphabetically-earliest bookmaker key', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'bookmaker_key' => 'zenbet',
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
    ]);

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'bookmaker_key' => 'coral',
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
    ]);

    $selected = (new SelectConservativeQuote)->forFixtureMarket($fixture, 'match_result');

    expect($selected['Arsenal']->bookmakerKey)->toBe('coral');
});

test('only quotes for the requested canonical market are considered', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'canonical_market' => 'match_result',
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
    ]);

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'canonical_market' => 'total_goals',
        'outcomes' => [['name' => 'Over', 'price' => '1.90', 'point' => '2.5']],
    ]);

    $selected = (new SelectConservativeQuote)->forFixtureMarket($fixture, 'match_result');

    expect($selected)->toHaveCount(1)->and($selected)->toHaveKey('Arsenal');
});

test('evidence quality and cache expiry are carried through unchanged for the caller to gate on', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
        'evidence_quality' => EvidenceQuality::Stale,
    ]);

    $selected = (new SelectConservativeQuote)->forFixtureMarket($fixture, 'match_result');

    expect($selected['Arsenal']->evidenceQuality)->toBe(EvidenceQuality::Stale);
});
