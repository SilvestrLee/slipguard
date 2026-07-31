<?php

use App\Domain\MarketIntelligence\Construction\CandidateExclusionReason;
use App\Domain\MarketIntelligence\Construction\DetermineLegEligibility;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;

function eligibilityBrief(array $overrides = []): PlanningBrief
{
    return new PlanningBrief(
        competitions: $overrides['competitions'] ?? ['soccer_epl'],
        windowDays: $overrides['windowDays'] ?? 7,
        legCountTarget: $overrides['legCountTarget'] ?? 5,
        requestedMarkets: $overrides['requestedMarkets'] ?? ['h2h'],
        targetOddsMin: null,
        targetOddsMax: null,
        excludedFixtureIds: $overrides['excludedFixtureIds'] ?? [],
    );
}

function eligibilityFixture(array $overrides = []): MarketIntelligenceFixture
{
    return MarketIntelligenceFixture::factory()->create($overrides);
}

test('CBE-001 excludes a competition not on the allowlist', function () {
    $fixture = eligibilityFixture(['competition_key' => 'soccer_bundesliga']);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toBeEmpty();
    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::CompetitionNotEnabled);
});

test('CBE-002 excludes a fixture outside the planning window', function () {
    $fixture = eligibilityFixture(['commence_time' => now()->addDays(10)]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(['windowDays' => 7]), $fixture, MarketFamily::MatchResult);

    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::OutsideFixtureWindow);
});

test('CBE-002 excludes a fixture that has already kicked off', function () {
    $fixture = eligibilityFixture(['commence_time' => now()->subHour()]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::OutsideFixtureWindow);
});

test('CBE-009 excludes a fixture the customer has explicitly excluded', function () {
    $fixture = eligibilityFixture();

    $result = (new DetermineLegEligibility)->evaluate(
        eligibilityBrief(['excludedFixtureIds' => [$fixture->provider_event_id]]),
        $fixture,
        MarketFamily::MatchResult,
    );

    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::CustomerExcluded);
});

test('CBE-004 excludes Correct Score entirely, per U-17.6 §7', function () {
    $fixture = eligibilityFixture();

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::CorrectScore);

    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::MarketNotVerified);
});

test('CBE-006 excludes a quote whose cache has expired even if tagged Fresh', function () {
    $fixture = eligibilityFixture();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
        'evidence_quality' => EvidenceQuality::Fresh,
        'cache_expires_at' => now()->subMinute(),
    ]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toBeEmpty();
    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::EvidenceNotFresh);
});

test('CBE-006 excludes evidence tagged Stale even if the cache has not yet expired', function () {
    $fixture = eligibilityFixture();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85']],
        'evidence_quality' => EvidenceQuality::Stale,
    ]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::EvidenceNotFresh);
});

test('CBE-007 excludes an outcome whose price falls outside the odds bounds', function () {
    $fixture = eligibilityFixture();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [
            ['name' => 'Arsenal', 'price' => '1.05'], // below 1.10 minimum
            ['name' => 'Chelsea', 'price' => '20.00'], // above 15.00 maximum
            ['name' => 'Draw', 'price' => '3.60'], // within bounds
        ],
    ]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toHaveCount(1);
    expect($result->options[0]->selectionDescription)->toBe('Draw');
    $reasons = array_map(fn ($e) => $e->reason, $result->excluded);
    expect($reasons)->toContain(CandidateExclusionReason::OddsOutOfBounds);
});

test('CBE-005 excludes an outcome that cannot be mapped, while surviving outcomes remain eligible', function () {
    $fixture = eligibilityFixture();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [
            ['name' => 'Arsenal', 'price' => '1.85'],
            ['name' => 'Some Unrecognized Outcome', 'price' => '2.50'],
        ],
    ]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toHaveCount(1);
    expect($result->options[0]->selectionDescription)->toBe('Arsenal to Win');
    $reasons = array_map(fn ($e) => $e->reason, $result->excluded);
    expect($reasons)->toContain(CandidateExclusionReason::CanonicalMappingUnconfident);
});

test('a fixture with no quotes at all for the requested market is excluded as not fresh', function () {
    $fixture = eligibilityFixture();

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toBeEmpty();
    expect($result->excluded[0]->reason)->toBe(CandidateExclusionReason::EvidenceNotFresh);
});

test('a fully eligible fixture returns every surviving outcome as a real candidate leg', function () {
    $fixture = eligibilityFixture();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [
            ['name' => 'Arsenal', 'price' => '1.85'],
            ['name' => 'Draw', 'price' => '3.60'],
            ['name' => 'Chelsea', 'price' => '4.50'],
        ],
    ]);

    $result = (new DetermineLegEligibility)->evaluate(eligibilityBrief(), $fixture, MarketFamily::MatchResult);

    expect($result->options)->toHaveCount(3);
    expect($result->excluded)->toBeEmpty();
});
