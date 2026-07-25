<?php

namespace App\Domain\Risk\Results;

/**
 * The outcome of DetermineAnalysisAvailability — bundles which of the three
 * tiers (Rule Set 2026.1 §17) decided the outcome, so the caller doesn't
 * have to re-derive it from the availability value alone.
 */
final readonly class AnalysisGateResult
{
    /**
     * @param  array<int, ReasonCode>  $reasonCodes
     */
    public function __construct(
        public AnalysisAvailability $availability,
        public DataQualityBand $effectiveBand,
        public array $reasonCodes,
    ) {}
}
