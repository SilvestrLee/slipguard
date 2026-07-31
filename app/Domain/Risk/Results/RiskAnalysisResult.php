<?php

namespace App\Domain\Risk\Results;

use Brick\Math\BigDecimal;

/**
 * The complete, immutable output of CalculateStructuralRisk. Nothing here
 * is persisted — this is an in-memory value object only (E-06B scope).
 */
final readonly class RiskAnalysisResult
{
    /**
     * @param  array<string, FactorResult>  $factorResults  Keyed by factor code (RF-001..RF-006).
     * @param  array<int, InteractionAdjustment>  $interactionAdjustments
     * @param  array<int, string>  $factorsNotEvaluated  Factor codes, e.g. ['RF-005'].
     * @param  array<int, ReasonCode>  $reasonCodes  Deduplicated, aggregated across every factor, data quality, and the gate outcome.
     * @param  ?BigDecimal  $rescaledScorePrecise  The rescale transform's value before the final HalfUp round to
     *                                             `structuralScore` (null exactly when `structuralScore` is null).
     *                                             Retained for the Marginal Structural Contribution (MSC) weakest-leg
     *                                             model (docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md §3.3,
     *                                             §11 — accepted PO-U06.2A-AC-001), whose tie-break needs more
     *                                             precision than the rounded integer score. Not a new calculation —
     *                                             this value was already computed by CalculateStructuralRisk and
     *                                             previously discarded.
     */
    public function __construct(
        public AnalysisAvailability $availability,
        public ?int $structuralScore,
        public ?RiskBand $riskBand,
        public DataQualityResult $dataQuality,
        public array $factorResults,
        public array $interactionAdjustments,
        public bool $limitedAnalysis,
        public array $factorsNotEvaluated,
        public array $reasonCodes,
        public string $engineVersion,
        public string $ruleSetVersion,
        public string $inputSchemaVersion,
        public string $marketTaxonomyVersion,
        public ?BigDecimal $rescaledScorePrecise = null,
    ) {}
}
