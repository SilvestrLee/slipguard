<?php

namespace App\Domain\Risk\Results;

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
    ) {}
}
