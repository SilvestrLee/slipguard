<?php

use App\Actions\MarketIntelligence\BuildAccumulatorCandidate;
use App\Domain\MarketIntelligence\Construction\CandidateRejectionReason;
use App\Domain\MarketIntelligence\Construction\DiscoveredSlots;
use App\Domain\MarketIntelligence\Construction\NoValidCandidate;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use Illuminate\Support\Facades\Http;

function buildBrief(int $legCountTarget = 2): PlanningBrief
{
    return new PlanningBrief(
        competitions: ['soccer_epl'],
        windowDays: 7,
        legCountTarget: $legCountTarget,
        requestedMarkets: ['h2h'],
        targetOddsMin: null,
        targetOddsMax: null,
    );
}

test('when no evidence exists at all, No-Valid-Candidate is returned for insufficient fixtures', function () {
    Http::fake([
        'api.the-odds-api.com/v4/*' => Http::response([]),
    ]);

    $result = (new BuildAccumulatorCandidate)->execute(buildBrief());

    expect($result)->toBeInstanceOf(NoValidCandidate::class);
    expect($result->reason)->toBe(CandidateRejectionReason::InsufficientFixtures);
});

test('fewer eligible slots than the leg-count target returns No-Valid-Candidate for insufficient eligible legs', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85'], ['name' => 'Draw', 'price' => '3.60']],
    ]);

    $result = (new BuildAccumulatorCandidate)->execute(buildBrief(legCountTarget: 2));

    expect($result)->toBeInstanceOf(NoValidCandidate::class);
    expect($result->reason)->toBe(CandidateRejectionReason::InsufficientEligibleLegs);
});

test('enough eligible slots are split into a proposed set matching the leg count, plus spares', function () {
    foreach (range(1, 4) as $i) {
        $fixture = MarketIntelligenceFixture::factory()->create(['provider_event_id' => "fixture-{$i}"]);
        MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
            'outcomes' => [['name' => 'Arsenal', 'price' => '1.85'], ['name' => 'Draw', 'price' => '3.60']],
        ]);
    }

    $result = (new BuildAccumulatorCandidate)->execute(buildBrief(legCountTarget: 2));

    expect($result)->toBeInstanceOf(DiscoveredSlots::class);
    expect($result->proposedSlots)->toHaveCount(2);
    expect($result->spareSlots)->toHaveCount(2);
});

test('existing fresh evidence is reused without acquiring anything new', function () {
    $fixture = MarketIntelligenceFixture::factory()->create();
    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => [['name' => 'Arsenal', 'price' => '1.85'], ['name' => 'Draw', 'price' => '3.60']],
    ]);

    Http::fake();

    (new BuildAccumulatorCandidate)->execute(buildBrief(legCountTarget: 1));

    Http::assertNothingSent();
});
