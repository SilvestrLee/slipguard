<?php

use App\Domain\MarketIntelligence\MapOddsApiMarket;
use App\Domain\Risk\Taxonomy\MarketFamily;

/**
 * U-17.5 §6 — every mapping here was confirmed against a real,
 * authenticated The Odds API request during U-17.3. Nothing in this
 * test asserts a guess.
 */
test('every confirmed provider key maps to its real canonical family', function () {
    $mapper = new MapOddsApiMarket;

    expect($mapper->toCanonicalFamily('h2h'))->toBe(MarketFamily::MatchResult);
    expect($mapper->toCanonicalFamily('h2h_h1'))->toBe(MarketFamily::HalfTimeResult);
    expect($mapper->toCanonicalFamily('totals'))->toBe(MarketFamily::TotalGoals);
    expect($mapper->toCanonicalFamily('double_chance'))->toBe(MarketFamily::DoubleChance);
    expect($mapper->toCanonicalFamily('draw_no_bet'))->toBe(MarketFamily::DrawNoBet);
    expect($mapper->toCanonicalFamily('btts'))->toBe(MarketFamily::BothTeamsToScore);
    expect($mapper->toCanonicalFamily('correct_score'))->toBe(MarketFamily::CorrectScore);
});

test('an unrecognized provider key returns null rather than a guess', function () {
    $mapper = new MapOddsApiMarket;

    expect($mapper->toCanonicalFamily('team_totals'))->toBeNull();
    expect($mapper->toCanonicalFamily('h2h_h2'))->toBeNull();
    expect($mapper->toCanonicalFamily('some_unknown_key'))->toBeNull();
});

test('supportedProviderKeys lists exactly the seven confirmed keys', function () {
    $mapper = new MapOddsApiMarket;

    expect($mapper->supportedProviderKeys())->toHaveCount(7)
        ->and($mapper->supportedProviderKeys())->toContain('h2h', 'h2h_h1', 'totals', 'double_chance', 'draw_no_bet', 'btts', 'correct_score');
});
