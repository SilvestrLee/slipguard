<?php

use App\Domain\Risk\Normalization\NormalizeFootballMarket;
use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

// --- Worked fixtures (NT-001 through NT-008) ---

test('NT-001 match result', function () {
    $result = (new NormalizeFootballMarket)->normalize('Match Result', 'Arsenal to win');

    expect($result->marketCode)->toBe('football.match_result.1x2');
    expect($result->marketFamily)->toBe(MarketFamily::MatchResult);
    expect($result->complexity)->toBe(MarketComplexity::Simple);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

test('NT-002 total goals with an explicit line in the market name', function () {
    $result = (new NormalizeFootballMarket)->normalize('Over 2.5 Goals', 'Over 2.5');

    expect($result->marketCode)->toBe('football.total_goals.over_under');
    expect($result->marketFamily)->toBe(MarketFamily::TotalGoals);
    expect($result->complexity)->toBe(MarketComplexity::Simple);
    expect($result->selectionFacts)->toBe(['direction' => 'over', 'line' => '2.5']);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

test('NT-003 BTTS alias', function () {
    $result = (new NormalizeFootballMarket)->normalize('BTTS', 'Yes');

    expect($result->marketCode)->toBe('football.goals.both_teams_to_score');
    expect($result->marketFamily)->toBe(MarketFamily::BothTeamsToScore);
    expect($result->complexity)->toBe(MarketComplexity::Moderate);
    expect($result->selectionFacts)->toBe(['selection_code' => 'yes']);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

test('NT-004 correct score', function () {
    $result = (new NormalizeFootballMarket)->normalize('Correct Score', '2-1');

    expect($result->marketCode)->toBe('football.score.correct_score');
    expect($result->marketFamily)->toBe(MarketFamily::CorrectScore);
    expect($result->complexity)->toBe(MarketComplexity::Complex);
    expect($result->selectionFacts)->toBe(['score_home' => '2', 'score_away' => '1']);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

test('NT-005 unrecognized market', function () {
    $result = (new NormalizeFootballMarket)->normalize('First Goal Between 10 and 20 Minutes', '');

    expect($result->marketCode)->toBeNull();
    expect($result->marketFamily)->toBeNull();
    expect($result->complexity)->toBe(MarketComplexity::Unknown);
    expect($result->status)->toBe(NormalizationStatus::Unrecognized);
});

test('NT-007 partial total goals — direction known, line missing', function () {
    $result = (new NormalizeFootballMarket)->normalize('Total Goals', 'Over');

    expect($result->marketCode)->toBe('football.total_goals.over_under');
    expect($result->selectionFacts)->toBe(['direction' => 'over', 'line' => null]);
    expect($result->status)->toBe(NormalizationStatus::Partial);
});

test('NT-008 no fuzzy matching — a misspelling stays unrecognized', function () {
    $result = (new NormalizeFootballMarket)->normalize('Macth Reslt', 'Arsenal to win');

    expect($result->marketCode)->toBeNull();
    expect($result->status)->toBe(NormalizationStatus::Unrecognized);
});

// --- Every approved alias, per family ---

test('match result aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'home');

    expect($result->marketFamily)->toBe(MarketFamily::MatchResult);
    expect($result->status)->toBe(NormalizationStatus::Complete);
})->with(['Match Result', '1X2', 'Full Time Result', 'Win Draw Win']);

test('double chance aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'home or draw');

    expect($result->marketFamily)->toBe(MarketFamily::DoubleChance);
})->with(['Double Chance', '1X', 'X2', '12', 'Home or Draw', 'Draw or Away', 'Home or Away']);

test('draw no bet aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'home');

    expect($result->marketFamily)->toBe(MarketFamily::DrawNoBet);
})->with(['Draw No Bet', 'DNB']);

test('total goals generic aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'Over 2.5');

    expect($result->marketFamily)->toBe(MarketFamily::TotalGoals);
})->with(['Total Goals', 'Over Under Goals', 'Match Goals', 'Goals Over Under']);

test('both teams to score aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'Yes');

    expect($result->marketFamily)->toBe(MarketFamily::BothTeamsToScore);
})->with(['Both Teams to Score', 'BTTS', 'GG', 'Both Teams Score']);

test('team total goals aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'Over 1.5');

    expect($result->marketFamily)->toBe(MarketFamily::TeamTotalGoals);
})->with(['Team Total Goals', 'Home Team Total Goals', 'Away Team Total Goals', 'Team Goals']);

test('correct score aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, '1-0');

    expect($result->marketFamily)->toBe(MarketFamily::CorrectScore);
})->with(['Correct Score', 'Exact Score']);

test('half-time result aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'home');

    expect($result->marketFamily)->toBe(MarketFamily::HalfTimeResult);
})->with(['Half Time Result', '1st Half Result', 'First Half Result', 'Half-time 1X2']);

test('half-time/full-time aliases', function (string $alias) {
    $result = (new NormalizeFootballMarket)->normalize($alias, 'home/home');

    expect($result->marketFamily)->toBe(MarketFamily::HalfTimeFullTime);
})->with(['Half Time Full Time', 'HT FT', 'HT/FT', 'Half-Time/Full-Time']);

// --- Regression: selection text must win over an ambiguous market alias ---

test('a market alias containing both direction words does not shadow the actual selection', function () {
    $result = (new NormalizeFootballMarket)->normalize('Over Under Goals', 'Under 2.5');

    expect($result->marketFamily)->toBe(MarketFamily::TotalGoals);
    expect($result->selectionFacts)->toBe(['direction' => 'under', 'line' => '2.5']);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

test('a market alias containing both direction words falls back to it only when the selection is silent', function () {
    $result = (new NormalizeFootballMarket)->normalize('Goals Over Under', '');

    expect($result->marketFamily)->toBe(MarketFamily::TotalGoals);
    expect($result->selectionFacts['direction'])->toBe('over');
});

// --- Generalized total-goals line detection beyond the literal alias example ---

test('total goals recognizes lines other than 2.5 via pattern extraction, not a hardcoded alias', function () {
    $result = (new NormalizeFootballMarket)->normalize('Over 3.5 Goals', '');

    expect($result->marketFamily)->toBe(MarketFamily::TotalGoals);
    expect($result->selectionFacts)->toBe(['direction' => 'over', 'line' => '3.5']);
    expect($result->status)->toBe(NormalizationStatus::Complete);
});

// --- Determinism ---

test('the same market and selection always produce the same canonical result', function () {
    $normalizer = new NormalizeFootballMarket;

    $first = $normalizer->normalize('Over 2.5 Goals', 'Over 2.5');
    $second = $normalizer->normalize('Over 2.5 Goals', 'Over 2.5');

    expect($first->marketCode)->toBe($second->marketCode);
    expect($first->selectionFacts)->toBe($second->selectionFacts);
    expect($first->status)->toBe($second->status);
});

// --- Raw text preservation ---

test('the original market and selection text is preserved verbatim', function () {
    $result = (new NormalizeFootballMarket)->normalize('  Over 2.5 Goals  ', 'Over 2.5');

    expect($result->rawMarketInput)->toBe('  Over 2.5 Goals  ');
    expect($result->rawSelectionInput)->toBe('Over 2.5');
});
