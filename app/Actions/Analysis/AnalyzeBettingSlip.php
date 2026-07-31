<?php

namespace App\Actions\Analysis;

use App\Domain\Risk\Engine\CalculateStructuralRisk;
use App\Domain\Risk\Normalization\NormalizeBettingSlip;
use App\Domain\Risk\Results\FactorResult;
use App\Domain\Risk\Results\InteractionAdjustment;
use App\Exceptions\BettingSlipNotAnalysableException;
use App\Models\BettingSlip;
use App\Models\LegAnalysis;
use App\Models\SlipAnalysis;
use Brick\Math\BigDecimal;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * The orchestration boundary between the pure Risk Engine and persistence.
 * Normalizes a Ready slip, runs CalculateStructuralRisk, persists the
 * complete immutable result, and transitions the slip to Analysed —
 * exactly the four responsibilities E-06C scoped, nothing more. The engine
 * itself (App\Domain\Risk\Engine\CalculateStructuralRisk) never touches the
 * database, the clock, or this action's models; this class wraps it.
 * Not to be confused with E-06D (weakest-leg/highest-risk-leg ranking),
 * a distinct, still-blocked sprint — see TASKS.md's Naming note.
 */
class AnalyzeBettingSlip
{
    public function __construct(
        private readonly NormalizeBettingSlip $normalize = new NormalizeBettingSlip,
        private readonly CalculateStructuralRisk $calculate = new CalculateStructuralRisk,
    ) {}

    /**
     * @param  null|Closure(string, array<string, mixed>): void  $progress
     */
    public function execute(BettingSlip $bettingSlip, ?Closure $progress = null): SlipAnalysis
    {
        $this->notify($progress, 'request_received', [
            'selections_received' => $bettingSlip->legs->count(),
            'competitions_recorded' => $bettingSlip->legs
                ->pluck('competition')
                ->filter(fn ($competition) => filled($competition))
                ->unique()
                ->count(),
            'markets_recorded' => $bettingSlip->legs
                ->pluck('market_name')
                ->filter(fn ($market) => filled($market))
                ->unique()
                ->count(),
        ]);

        $this->notify($progress, 'eligibility_validation_started');
        $eligibility = $bettingSlip->analysisEligibility();

        if (! $eligibility->eligible) {
            throw new BettingSlipNotAnalysableException($eligibility);
        }

        $this->notify($progress, 'selections_validated', [
            'selections_validated' => $bettingSlip->legs->count(),
        ]);
        $this->notify($progress, 'normalization_started');
        $normalizedSlip = $this->normalize->execute($bettingSlip);
        $this->notify($progress, 'normalization_complete', [
            'market_types_recognized' => collect($normalizedSlip->legs)
                ->pluck('market.marketFamily')
                ->filter()
                ->unique(fn ($family) => $family->value)
                ->count(),
            'normalization_limitations' => collect($normalizedSlip->legs)
                ->filter(fn ($leg) => ! in_array($leg->market->status->value, ['complete'], true))
                ->count(),
        ]);

        $this->notify($progress, 'structural_evaluation_started');
        $result = $this->calculate->calculate($normalizedSlip);
        $this->notify($progress, 'structural_evaluation_complete', [
            'structural_factors_evaluated' => count($result->factorResults),
            'rule_set_version' => $result->ruleSetVersion,
            'report_availability' => $result->availability->value,
            'factors_not_evaluated' => count($result->factorsNotEvaluated),
        ]);
        $this->notify($progress, 'report_construction_started');

        $slipAnalysis = DB::transaction(function () use ($bettingSlip, $normalizedSlip, $result, $progress) {
            $slipAnalysis = new SlipAnalysis;
            $slipAnalysis->user_id = $bettingSlip->user_id;
            $slipAnalysis->fill([
                'betting_slip_id' => $bettingSlip->id,
                'availability' => $result->availability,
                'structural_score' => $result->structuralScore,
                'risk_band' => $result->riskBand,
                'data_quality_score' => $result->dataQuality->score,
                'data_quality_band' => $result->dataQuality->band,
                'limited_analysis' => $result->limitedAnalysis,
                'factor_results' => $this->serializeFactorResults($result->factorResults),
                'interaction_adjustments' => $this->serializeInteractionAdjustments($result->interactionAdjustments),
                'data_quality_deductions' => $result->dataQuality->deductions,
                'factors_not_evaluated' => $result->factorsNotEvaluated,
                'reason_codes' => $result->reasonCodes,
                'engine_version' => $result->engineVersion,
                'rule_set_version' => $result->ruleSetVersion,
                'input_schema_version' => $result->inputSchemaVersion,
                'market_taxonomy_version' => $result->marketTaxonomyVersion,
            ]);

            $this->notify($progress, 'persistence_started');
            $slipAnalysis->save();

            foreach ($normalizedSlip->legs as $leg) {
                LegAnalysis::create([
                    'slip_analysis_id' => $slipAnalysis->id,
                    'betting_slip_leg_id' => $leg->bettingSlipLegId,
                    'display_order' => $leg->displayOrder,
                    'sport_code' => $leg->sport->sportCode,
                    'sport_status' => $leg->sport->status,
                    'market_code' => $leg->market->marketCode,
                    'market_family' => $leg->market->marketFamily,
                    'market_complexity' => $leg->market->complexity,
                    'market_status' => $leg->market->status,
                    'decimal_odds' => $leg->decimalOdds,
                    'raw_market_input' => $leg->market->rawMarketInput,
                    'raw_selection_input' => $leg->market->rawSelectionInput,
                ]);
            }

            $bettingSlip->markAnalysed();

            return $slipAnalysis->fresh('legAnalyses');
        });

        $this->notify($progress, 'analysis_complete', [
            'analysis_id' => $slipAnalysis->id,
            'report_availability' => $slipAnalysis->availability->value,
        ]);

        return $slipAnalysis;
    }

    /**
     * @param  null|Closure(string, array<string, mixed>): void  $progress
     * @param  array<string, mixed>  $facts
     */
    private function notify(?Closure $progress, string $event, array $facts = []): void
    {
        $progress?->__invoke($event, $facts);
    }

    /**
     * @param  array<string, FactorResult>  $factorResults
     * @return array<int, array<string, mixed>>
     */
    private function serializeFactorResults(array $factorResults): array
    {
        return array_values(array_map(fn (FactorResult $factor) => [
            'factor_code' => $factor->factorCode,
            'base_contribution' => (string) $factor->baseContribution,
            'cap' => (string) $factor->cap,
            'adjusted_contribution' => (string) $factor->adjustedContribution,
            'reason_codes' => array_map(fn ($code) => $code->value, $factor->reasonCodes),
            'trace' => [
                'raw_inputs' => $factor->trace->rawInputs,
                'normalized_values' => $factor->trace->normalizedValues,
                'notes' => $factor->trace->notes,
            ],
        ], $factorResults));
    }

    /**
     * @param  array<int, InteractionAdjustment>  $interactionAdjustments
     * @return array<int, array<string, mixed>>
     */
    private function serializeInteractionAdjustments(array $interactionAdjustments): array
    {
        return array_map(fn (InteractionAdjustment $adjustment) => [
            'group_name' => $adjustment->groupName,
            'factor_codes' => $adjustment->factorCodes,
            'pre_cap_total' => (string) $adjustment->preCapTotal,
            'cap' => (string) $adjustment->cap,
            'was_capped' => $adjustment->wasCapped,
            'adjustment_ratio' => (string) $adjustment->adjustmentRatio,
            'post_cap_contributions' => array_map(
                fn (BigDecimal $value) => (string) $value,
                $adjustment->postCapContributions,
            ),
        ], $interactionAdjustments);
    }
}
