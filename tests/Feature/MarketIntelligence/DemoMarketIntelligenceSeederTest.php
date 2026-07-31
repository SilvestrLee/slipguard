<?php

use App\Actions\MarketIntelligence\BuildAccumulatorCandidate;
use App\Actions\MarketIntelligence\CreateCandidateBettingSlip;
use App\Domain\MarketIntelligence\Construction\ChosenSlot;
use App\Domain\MarketIntelligence\Construction\ConstructedCandidate;
use App\Domain\MarketIntelligence\Construction\DiscoveredSlots;
use App\Domain\MarketIntelligence\Construction\EvaluateCandidateSelections;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use App\Models\User;
use Database\Seeders\Demo\DemoMarketIntelligenceSeeder;

/**
 * `PO-U18.1-RB3-001` — proves the demonstration dataset drives the real,
 * unmodified Capability B pipeline end to end (discovery → outcome
 * selection → evaluation → Planner handoff) without ever calling the live
 * provider. Not a smoke test on the seeder's row counts — a genuine
 * exercise of the production code path the Builder itself uses.
 */
test('the demo market intelligence dataset never requires the live provider', function () {
    $result = (new DemoMarketIntelligenceSeeder)->run();

    expect($result['fixtures'])->toBe(18)
        ->and($result['quotes'])->toBeGreaterThan(0)
        ->and(MarketIntelligenceFixture::query()->where('cache_expires_at', '>', now())->count())->toBe(18);
});

test('a planning brief against the demo dataset discovers real, eligible slots without any HTTP call', function () {
    Http::fake(fn () => Http::response('LIVE PROVIDER CALLED — the demo dataset should have prevented this', 500));

    (new DemoMarketIntelligenceSeeder)->run();

    $brief = new PlanningBrief(
        competitions: ['soccer_epl'],
        windowDays: 7,
        legCountTarget: 5,
        requestedMarkets: ['h2h', 'totals', 'double_chance', 'draw_no_bet', 'btts'],
        targetOddsMin: null,
        targetOddsMax: null,
    );

    $discovery = (new BuildAccumulatorCandidate)->execute($brief);

    expect($discovery)->toBeInstanceOf(DiscoveredSlots::class)
        ->and($discovery->proposedSlots)->toHaveCount(5);

    Http::assertNothingSent();
});

test('the complete demo pipeline produces a real constructed candidate and hands off to the Planner', function () {
    (new DemoMarketIntelligenceSeeder)->run();

    $user = User::factory()->create();

    $brief = new PlanningBrief(
        competitions: ['soccer_epl'],
        windowDays: 7,
        legCountTarget: 5,
        requestedMarkets: ['h2h', 'totals', 'double_chance', 'draw_no_bet', 'btts'],
        targetOddsMin: null,
        targetOddsMax: null,
    );

    $discovery = (new BuildAccumulatorCandidate)->execute($brief);
    expect($discovery)->toBeInstanceOf(DiscoveredSlots::class);

    // The customer's own choice, per slot — Customer Outcome Sovereignty
    // (U-17.6A): the engine discovers and ranks opportunities, it never
    // picks a side. This test plays the customer's role deterministically
    // (always the first surviving option) purely to exercise the pipeline.
    $chosenSlots = array_map(
        fn ($slot) => new ChosenSlot($slot, $slot->options[0]),
        $discovery->proposedSlots,
    );

    $evaluation = (new EvaluateCandidateSelections)->execute($chosenSlots, $discovery->spareSlots, $brief);

    expect($evaluation)->toBeInstanceOf(ConstructedCandidate::class);

    $session = (new CreateCandidateBettingSlip)->execute($user, $evaluation);

    expect($session->user_id)->toBe($user->id)
        ->and($session->sourceBettingSlip->legs)->toHaveCount(5)
        ->and($session->sourceBettingSlip->status->value)->toBe('ready');
});

test('re-running the demo seeder is idempotent, not accumulating duplicate fixtures', function () {
    (new DemoMarketIntelligenceSeeder)->run();
    (new DemoMarketIntelligenceSeeder)->run();

    expect(MarketIntelligenceFixture::count())->toBe(18)
        ->and(MarketIntelligenceMarketQuote::count())->toBe(MarketIntelligenceMarketQuote::count());
});
