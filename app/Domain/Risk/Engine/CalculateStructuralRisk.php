<?php

namespace App\Domain\Risk\Engine;

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskAnalysisResult;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\RuleSets\RuleSet2026_1;
use App\Domain\Risk\Support\ProportionalGroupCap;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * The Risk Intelligence Engine's entry point. A pure calculator: accepts an
 * already-normalized slip, returns an immutable result. Never persists
 * anything, never modifies the slip, never fetches external data — Rule
 * Set 2026.1 §5.1 / the E-06B sprint's own prohibition.
 *
 * Calculation order follows the sprint's §7 exactly — no alternative
 * ordering:
 *   normalize input (already done by the caller) -> calculate each factor
 *   -> apply factor caps (inside each factor) -> apply interaction groups
 *   -> calculate adjusted total -> transform to display score -> round
 *   -> determine risk band -> calculate data quality -> determine data
 *   quality band -> collect reason codes -> build immutable result.
 *
 * Factors are always calculated, even for a slip the gate will end up
 * marking Unavailable — "do not generate a structural score" (Rule Set
 * §17) is satisfied by not *exposing* one in the final result, not by
 * skipping cheap, harmless, pure arithmetic. This keeps the pipeline linear
 * with no early-exit branches, matching §7's explicit ordering instruction.
 */
class CalculateStructuralRisk
{
    /**
     * Versions the engine's own calculation code (this class and its
     * factor/support classes), independently of the rule set it executes —
     * ADR-007's four traceability axes are engine, rule set, taxonomy, and
     * input schema, each tracked separately since any one can change
     * without the others (e.g. a performance refactor bumps this without
     * touching Rule Set 2026.1 at all).
     */
    public const ENGINE_VERSION = '1.0';

    public function __construct(
        private readonly RuleSet2026_1 $ruleSet = new RuleSet2026_1,
        private readonly CalculateDataQuality $calculateDataQuality = new CalculateDataQuality,
        private readonly DetermineAnalysisAvailability $determineAnalysisAvailability = new DetermineAnalysisAvailability,
    ) {}

    public function calculate(NormalizedBettingSlip $input): RiskAnalysisResult
    {
        // Calculate each factor.
        $factorResults = [];
        foreach ($this->ruleSet->factors() as $factor) {
            $factorResults[$factor->code()] = $factor->calculate($input);
        }

        // Apply interaction groups (proportional scaling, the one approved method).
        [$factorResults, $groupAAdjustment] = ProportionalGroupCap::apply(
            $factorResults,
            RuleSet2026_1::GROUP_A_FACTOR_CODES,
            RuleSet2026_1::GROUP_A_CAP,
            'Group A — Accumulator Scale',
        );

        [$factorResults, $groupBAdjustment] = ProportionalGroupCap::apply(
            $factorResults,
            RuleSet2026_1::GROUP_B_FACTOR_CODES,
            RuleSet2026_1::GROUP_B_CAP,
            'Group B — Odds Distribution',
        );

        // Calculate adjusted total, transform to display score, round.
        $adjustedSum = BigDecimal::zero();
        foreach ($factorResults as $factorResult) {
            $adjustedSum = $adjustedSum->plus($factorResult->adjustedContribution);
        }

        $rescaled = $adjustedSum->multipliedBy(100)
            ->dividedBy(RuleSet2026_1::ACHIEVABLE_CEILING, 10, RoundingMode::HalfUp);

        $rescaled = BigDecimal::max($rescaled, BigDecimal::zero());
        $rescaled = BigDecimal::min($rescaled, BigDecimal::of(100));

        $structuralScore = (int) $rescaled->toScale(0, RoundingMode::HalfUp)->__toString();

        // Determine risk band.
        $riskBand = RiskBand::forScore($structuralScore);

        // Calculate data quality (independent of structural risk).
        $dataQuality = $this->calculateDataQuality->calculate($input);

        // Determine data quality band / analysis availability.
        $gateResult = $this->determineAnalysisAvailability->determine($input, $dataQuality->score);

        // Collect reason codes — every factor's, data quality's, and the gate's.
        $reasonCodes = [];
        foreach ($factorResults as $factorResult) {
            array_push($reasonCodes, ...$factorResult->reasonCodes);
        }
        array_push($reasonCodes, ...$dataQuality->reasonCodes);
        array_push($reasonCodes, ...$gateResult->reasonCodes);
        $reasonCodes = array_values(array_unique($reasonCodes, SORT_REGULAR));

        $factorsNotEvaluated = [];
        if ($gateResult->availability !== AnalysisAvailability::Unavailable
            && $factorResults['RF-005']->baseContribution->isZero()
            && count($factorResults['RF-005']->trace->rawInputs) > 0
            && ($factorResults['RF-005']->trace->rawInputs['known_complexity_legs'] ?? null) === 0) {
            $factorsNotEvaluated[] = 'RF-005';
        }

        $isUnavailable = $gateResult->availability === AnalysisAvailability::Unavailable;

        // Build immutable result.
        return new RiskAnalysisResult(
            availability: $gateResult->availability,
            structuralScore: $isUnavailable ? null : $structuralScore,
            riskBand: $isUnavailable ? null : $riskBand,
            dataQuality: $dataQuality,
            factorResults: $factorResults,
            interactionAdjustments: [$groupAAdjustment, $groupBAdjustment],
            limitedAnalysis: $gateResult->availability === AnalysisAvailability::Limited,
            factorsNotEvaluated: $factorsNotEvaluated,
            reasonCodes: $reasonCodes,
            engineVersion: self::ENGINE_VERSION,
            ruleSetVersion: RuleSet2026_1::VERSION,
            inputSchemaVersion: RuleSet2026_1::INPUT_SCHEMA_VERSION,
            marketTaxonomyVersion: FootballMarketTaxonomyV1::VERSION,
        );
    }
}
