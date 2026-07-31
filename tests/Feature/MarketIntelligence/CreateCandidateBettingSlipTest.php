<?php

use App\Actions\MarketIntelligence\CreateCandidateBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\MarketIntelligence\Construction\CandidateLeg;
use App\Domain\MarketIntelligence\Construction\CandidateSlot;
use App\Domain\MarketIntelligence\Construction\ChosenSlot;
use App\Domain\MarketIntelligence\Construction\ConstructedCandidate;
use App\Domain\MarketIntelligence\Construction\EvaluateCandidateSelections;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Normalization\NormalizedMarket;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Models\MarketIntelligenceFixture;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * A real ConstructedCandidate, produced through the actual, already-tested
 * EvaluateCandidateSelections pipeline — not a hand-built fake — so this
 * test exercises the genuine ADR-013 handoff contract end to end.
 */
function realAcceptedCandidate(): ConstructedCandidate
{
    $fixture = MarketIntelligenceFixture::factory()->create([
        'competition_key' => 'soccer_epl',
        'home_team' => 'Arsenal',
        'away_team' => 'Chelsea',
    ]);

    $definition = (new FootballMarketTaxonomyV1)->findByFamily(MarketFamily::MatchResult);

    $leg = new CandidateLeg(
        fixture: $fixture,
        marketFamily: MarketFamily::MatchResult,
        normalizedMarket: new NormalizedMarket(
            marketCode: $definition->marketCode,
            marketFamily: MarketFamily::MatchResult,
            complexity: $definition->complexity,
            status: NormalizationStatus::Complete,
            selectionFacts: [],
            rawMarketInput: $definition->displayName,
            rawSelectionInput: 'match.Arsenal',
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
        ),
        selectionDescription: 'Arsenal to Win',
        decimalOdds: '1.85',
        bookmakerKey: 'pinnacle',
        evidenceFresh: true,
        retrievedAt: CarbonImmutable::now(),
    );

    $slot = (new CandidateSlot([$leg]))->withRank(1, 'test');
    $chosen = new ChosenSlot($slot, $leg);

    $brief = new PlanningBrief(
        competitions: ['soccer_epl'],
        windowDays: 7,
        legCountTarget: 1,
        requestedMarkets: ['h2h'],
        targetOddsMin: null,
        targetOddsMax: null,
        riskCeiling: RiskBand::VeryHigh,
    );

    $result = (new EvaluateCandidateSelections)->execute([$chosen], [], $brief);

    expect($result)->toBeInstanceOf(ConstructedCandidate::class);

    return $result;
}

test('an accepted candidate becomes a real Ready betting slip inside a real Planner session', function () {
    $user = User::factory()->create();
    $candidate = realAcceptedCandidate();

    $session = (new CreateCandidateBettingSlip)->execute($user, $candidate);

    // StartPlannerSession's own EvaluatePlannerSession step runs immediately
    // on creation — a real, existing Planner behaviour this handoff
    // inherits unmodified, not something Capability B introduces.
    expect($session->status)->toBe(PlannerSessionStatus::Evaluated);

    $slip = $session->sourceBettingSlip;
    expect($slip->status)->toBe(BettingSlipStatus::Ready);
    expect($slip->legs)->toHaveCount(1);
    expect($slip->legs->first()->event_name)->toBe('Arsenal vs Chelsea');
    expect($slip->legs->first()->market_name)->toBe('Match Result');
    expect($slip->legs->first()->selection_name)->toBe('Arsenal to Win');
    expect($slip->legs->first()->competition)->toBe('Premier League');
});

test('the resulting Planner session uses the same selections snapshot as any customer-entered slip', function () {
    $user = User::factory()->create();
    $candidate = realAcceptedCandidate();

    $session = (new CreateCandidateBettingSlip)->execute($user, $candidate);

    expect($session->selections)->toHaveCount(1);
    expect($session->selections->first()->selection_name)->toBe('Arsenal to Win');
});
