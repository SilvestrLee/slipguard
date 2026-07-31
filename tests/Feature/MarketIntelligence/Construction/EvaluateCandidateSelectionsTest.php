<?php

use App\Domain\MarketIntelligence\Construction\CandidateConstructionRuleSet2026_1;
use App\Domain\MarketIntelligence\Construction\CandidateLeg;
use App\Domain\MarketIntelligence\Construction\CandidateRejectionReason;
use App\Domain\MarketIntelligence\Construction\CandidateSlot;
use App\Domain\MarketIntelligence\Construction\ChosenSlot;
use App\Domain\MarketIntelligence\Construction\ConstructedCandidate;
use App\Domain\MarketIntelligence\Construction\EvaluateCandidateSelections;
use App\Domain\MarketIntelligence\Construction\NoValidCandidate;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Domain\MarketIntelligence\Construction\ReplacementNeeded;
use App\Domain\Risk\Normalization\NormalizedMarket;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Models\MarketIntelligenceFixture;
use Carbon\CarbonImmutable;

function evalBrief(array $overrides = []): PlanningBrief
{
    return new PlanningBrief(
        competitions: ['soccer_epl'],
        windowDays: 7,
        legCountTarget: $overrides['legCountTarget'] ?? 2,
        requestedMarkets: ['h2h'],
        targetOddsMin: $overrides['targetOddsMin'] ?? null,
        targetOddsMax: $overrides['targetOddsMax'] ?? null,
        riskCeiling: $overrides['riskCeiling'] ?? RiskBand::VeryHigh,
    );
}

function matchResultChosenSlot(string $homeTeam, string $decimalOdds, int $rank): ChosenSlot
{
    $fixture = MarketIntelligenceFixture::factory()->create([
        'home_team' => $homeTeam,
        'away_team' => 'Opponent',
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
            rawSelectionInput: "match.{$homeTeam}",
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
        ),
        selectionDescription: "{$homeTeam} to Win",
        decimalOdds: $decimalOdds,
        bookmakerKey: 'pinnacle',
        evidenceFresh: true,
        retrievedAt: CarbonImmutable::now(),
    );

    $slot = (new CandidateSlot([$leg]))->withRank($rank, 'test');

    return new ChosenSlot($slot, $leg);
}

test('a candidate within the risk ceiling and with no odds target is accepted', function () {
    $chosen = [
        matchResultChosenSlot('Arsenal', '1.50', 1),
        matchResultChosenSlot('Liverpool', '1.60', 2),
    ];

    $result = (new EvaluateCandidateSelections)->execute($chosen, [], evalBrief());

    expect($result)->toBeInstanceOf(ConstructedCandidate::class);
    expect($result->combinedOdds->__toString())->toBe('2.4000');
    expect($result->legAttributionRanking)->not->toBeNull();
});

test('a risk ceiling breach with an available spare returns ReplacementNeeded, not a silent auto-fix', function () {
    // Many legs at meaningfully elevated odds reliably exceeds even a
    // generous ceiling — the exact score is the real engine's own concern,
    // not something this test needs to reverse-engineer.
    $chosen = [
        matchResultChosenSlot('Team1', '5.00', 1),
        matchResultChosenSlot('Team2', '5.00', 2),
        matchResultChosenSlot('Team3', '5.00', 3),
        matchResultChosenSlot('Team4', '5.00', 4),
        matchResultChosenSlot('Team5', '5.00', 5),
        matchResultChosenSlot('Team6', '5.00', 6),
    ];
    $spare = matchResultChosenSlot('Team7', '2.00', 7)->slot;

    $result = (new EvaluateCandidateSelections)->execute($chosen, [$spare], evalBrief(['riskCeiling' => RiskBand::Low]));

    expect($result)->toBeInstanceOf(ReplacementNeeded::class);
    expect($result->replacementSlot)->toBe($spare);
    expect($result->attemptsUsedSoFar)->toBe(1);
    expect($result->reason)->toBe(CandidateRejectionReason::RiskCeilingUnreachable);
});

test('a risk ceiling breach with no spare slots available returns No-Valid-Candidate, not an infinite wait', function () {
    $chosen = [
        matchResultChosenSlot('Team1', '5.00', 1),
        matchResultChosenSlot('Team2', '5.00', 2),
        matchResultChosenSlot('Team3', '5.00', 3),
        matchResultChosenSlot('Team4', '5.00', 4),
        matchResultChosenSlot('Team5', '5.00', 5),
        matchResultChosenSlot('Team6', '5.00', 6),
    ];

    $result = (new EvaluateCandidateSelections)->execute($chosen, [], evalBrief(['riskCeiling' => RiskBand::Low]));

    expect($result)->toBeInstanceOf(NoValidCandidate::class);
    expect($result->reason)->toBe(CandidateRejectionReason::RiskCeilingUnreachable);
});

test('a risk ceiling breach after the maximum replacement attempts returns No-Valid-Candidate rather than a fourth attempt', function () {
    $chosen = [
        matchResultChosenSlot('Team1', '5.00', 1),
        matchResultChosenSlot('Team2', '5.00', 2),
        matchResultChosenSlot('Team3', '5.00', 3),
        matchResultChosenSlot('Team4', '5.00', 4),
        matchResultChosenSlot('Team5', '5.00', 5),
        matchResultChosenSlot('Team6', '5.00', 6),
    ];
    $spare = matchResultChosenSlot('Team7', '2.00', 7)->slot;

    $result = (new EvaluateCandidateSelections)->execute(
        $chosen,
        [$spare],
        evalBrief(['riskCeiling' => RiskBand::Low]),
        riskAttemptsUsed: CandidateConstructionRuleSet2026_1::MAX_RISK_REPLACEMENT_ATTEMPTS,
    );

    expect($result)->toBeInstanceOf(NoValidCandidate::class);
    expect($result->reason)->toBe(CandidateRejectionReason::RiskCeilingUnreachable);
});

test('a combined odds outside the target range triggers exactly one deterministic replacement attempt', function () {
    $chosen = [
        matchResultChosenSlot('Arsenal', '1.50', 1),
        matchResultChosenSlot('Liverpool', '1.60', 2), // combined 2.40
    ];
    $spare = matchResultChosenSlot('Everton', '3.00', 3)->slot;

    $result = (new EvaluateCandidateSelections)->execute(
        $chosen,
        [$spare],
        evalBrief(['targetOddsMin' => '10.00', 'targetOddsMax' => '20.00']),
    );

    expect($result)->toBeInstanceOf(ReplacementNeeded::class);
    expect($result->attemptsUsedSoFar)->toBe(1);
    expect($result->reason)->toBe(CandidateRejectionReason::TargetOddsUnreachable);
});

test('an unreachable odds target after the one allowed adjustment returns No-Valid-Candidate, per U-17.6 §12', function () {
    $chosen = [
        matchResultChosenSlot('Arsenal', '1.50', 1),
        matchResultChosenSlot('Liverpool', '1.60', 2),
    ];

    $result = (new EvaluateCandidateSelections)->execute(
        $chosen,
        [],
        evalBrief(['targetOddsMin' => '10.00', 'targetOddsMax' => '20.00']),
        oddsAttemptsUsed: CandidateConstructionRuleSet2026_1::MAX_ODDS_ADJUSTMENT_ATTEMPTS,
    );

    expect($result)->toBeInstanceOf(NoValidCandidate::class);
    expect($result->reason)->toBe(CandidateRejectionReason::TargetOddsUnreachable);
});

test('a combined odds within the target range is accepted without any replacement', function () {
    $chosen = [
        matchResultChosenSlot('Arsenal', '1.50', 1),
        matchResultChosenSlot('Liverpool', '1.60', 2), // combined 2.40
    ];

    $result = (new EvaluateCandidateSelections)->execute(
        $chosen,
        [],
        evalBrief(['targetOddsMin' => '2.00', 'targetOddsMax' => '3.00']),
    );

    expect($result)->toBeInstanceOf(ConstructedCandidate::class);
});
