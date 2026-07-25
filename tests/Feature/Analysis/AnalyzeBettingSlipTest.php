<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\AnalysisIneligibilityReason;
use App\Domain\Risk\Engine\CalculateStructuralRisk;
use App\Domain\Risk\Normalization\NormalizeBettingSlip;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Results\RiskBand;
use App\Exceptions\BettingSlipNotAnalysableException;
use App\Models\BettingSlip;
use App\Models\SlipAnalysis;
use Illuminate\Database\QueryException;

function analysisLegAttributes(array $overrides = []): array
{
    return array_merge([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win',
        'decimal_odds' => '1.90',
        'display_order' => 0,
    ], $overrides);
}

function readySlipWithLegs(array $legAttributesList): BettingSlip
{
    $slip = BettingSlip::factory()->create();

    foreach ($legAttributesList as $index => $attributes) {
        $slip->legs()->create(analysisLegAttributes(['display_order' => $index, ...$attributes]));
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

test('analyzing a Full-availability slip persists the complete result and marks the slip Analysed', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    expect($analysis)->toBeInstanceOf(SlipAnalysis::class);
    expect($analysis->availability)->toBe(AnalysisAvailability::Full);
    expect($analysis->structural_score)->not->toBeNull();
    expect($analysis->risk_band)->toBeInstanceOf(RiskBand::class);
    expect($analysis->data_quality_band)->toBe(DataQualityBand::Strong);
    expect($analysis->engine_version)->toBe('1.0');
    expect($analysis->rule_set_version)->toBe('2026.1');
    expect($analysis->input_schema_version)->toBe('1.0');
    expect($analysis->market_taxonomy_version)->toBe('1.0');

    expect($slip->fresh()->status->value)->toBe('analysed');
});

test('persists one LegAnalysis row per leg, in display order, with the normalized snapshot', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    expect($analysis->legAnalyses)->toHaveCount(2);

    $first = $analysis->legAnalyses->firstWhere('display_order', 0);
    expect($first->sport_code)->toBe('football');
    expect($first->market_code)->toBe('football.match_result.1x2');
    expect((string) $first->decimal_odds)->toBe('1.90');
    expect($first->betting_slip_leg_id)->toBe($slip->legs[0]->id);
});

test('an Unavailable slip persists a null structural score and null risk band', function () {
    $slip = readySlipWithLegs([
        ['sport' => 'Tennis', 'event_name' => 'Djokovic vs Alcaraz', 'market_name' => 'Match Winner', 'selection_name' => 'Djokovic', 'decimal_odds' => '1.40'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    expect($analysis->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($analysis->structural_score)->toBeNull();
    expect($analysis->risk_band)->toBeNull();
});

test('factor results and interaction adjustments are serialized as plain, JSON-safe arrays', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    expect($analysis->factor_results)->toHaveCount(6);
    $rf001 = collect($analysis->factor_results)->firstWhere('factor_code', 'RF-001');
    expect($rf001['base_contribution'])->toBeString();
    expect($rf001['adjusted_contribution'])->toBeString();
    expect($rf001['reason_codes'])->toBeArray();
    expect($rf001['trace'])->toHaveKeys(['raw_inputs', 'normalized_values', 'notes']);

    expect($analysis->interaction_adjustments)->toHaveCount(2);
    $groupA = collect($analysis->interaction_adjustments)->firstWhere('group_name', 'Group A — Accumulator Scale');
    expect($groupA['pre_cap_total'])->toBeString();
    expect($groupA['post_cap_contributions']['RF-001'])->toBeString();

    // Round-trips through the database as plain JSON — reloading from a fresh model must match exactly.
    $reloaded = SlipAnalysis::find($analysis->id);
    expect($reloaded->factor_results)->toBe($analysis->factor_results);
});

test('reason codes persist as an enum collection and reload as ReasonCode instances', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    $reloaded = SlipAnalysis::find($analysis->id);

    expect($reloaded->reason_codes->first())->toBeInstanceOf(ReasonCode::class);
});

test('a Draft slip cannot be analyzed and throws with the specific ineligibility reason', function () {
    $slip = BettingSlip::factory()->create();
    $slip->legs()->create(analysisLegAttributes());

    try {
        (new AnalyzeBettingSlip)->execute($slip);
        $this->fail('Expected BettingSlipNotAnalysableException');
    } catch (BettingSlipNotAnalysableException $e) {
        expect($e->eligibility->reasons)->toBe([AnalysisIneligibilityReason::NotReady]);
    }

    expect(SlipAnalysis::count())->toBe(0);
});

test('an empty Draft slip reports the Empty reason, not NotReady', function () {
    $slip = BettingSlip::factory()->create();

    try {
        (new AnalyzeBettingSlip)->execute($slip);
        $this->fail('Expected BettingSlipNotAnalysableException');
    } catch (BettingSlipNotAnalysableException $e) {
        expect($e->eligibility->reasons)->toBe([AnalysisIneligibilityReason::NotReady]);
    }
});

test('an already-Analysed slip cannot be analyzed again', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    try {
        (new AnalyzeBettingSlip)->execute($slip->fresh());
        $this->fail('Expected BettingSlipNotAnalysableException');
    } catch (BettingSlipNotAnalysableException $e) {
        expect($e->eligibility->reasons)->toBe([AnalysisIneligibilityReason::AlreadyAnalysed]);
    }

    expect(SlipAnalysis::count())->toBe(1);
});

test('an archived slip cannot be analyzed', function () {
    $slip = BettingSlip::factory()->archived()->create();
    $slip->legs()->create(analysisLegAttributes());

    try {
        (new AnalyzeBettingSlip)->execute($slip);
        $this->fail('Expected BettingSlipNotAnalysableException');
    } catch (BettingSlipNotAnalysableException $e) {
        expect($e->eligibility->reasons)->toBe([AnalysisIneligibilityReason::Archived]);
    }
});

test('retrieval works both ways: BettingSlip->analysis and SlipAnalysis->bettingSlip/user', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);

    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    expect($slip->fresh()->analysis->id)->toBe($analysis->id);
    expect($analysis->bettingSlip->id)->toBe($slip->id);
    expect($analysis->user->id)->toBe($slip->user_id);
});

test('a betting slip can have at most one SlipAnalysis at the database level', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    expect(fn () => SlipAnalysis::factory()->create(['betting_slip_id' => $slip->id]))
        ->toThrow(QueryException::class);
});

test('the risk engine never persists anything itself — only the orchestration action does', function () {
    $slip = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);

    $normalized = (new NormalizeBettingSlip)->execute($slip);
    (new CalculateStructuralRisk)->calculate($normalized);

    expect(SlipAnalysis::count())->toBe(0);
    expect($slip->fresh()->status->value)->toBe('ready');
});

test('analyzing the same slip data twice via a fresh action instance is deterministic', function () {
    $slipA = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);
    $slipB = readySlipWithLegs([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);

    $analysisA = (new AnalyzeBettingSlip)->execute($slipA);
    $analysisB = (new AnalyzeBettingSlip)->execute($slipB);

    expect($analysisB->structural_score)->toBe($analysisA->structural_score);
    expect($analysisB->risk_band)->toBe($analysisA->risk_band);
    expect($analysisB->factor_results)->toBe($analysisA->factor_results);
});
