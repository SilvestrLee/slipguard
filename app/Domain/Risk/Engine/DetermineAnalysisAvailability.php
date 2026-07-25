<?php

namespace App\Domain\Risk\Engine;

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Normalization\NormalizeSport;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\AnalysisGateResult;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

/**
 * Rule Set 2026.1 §17 — the three-tier analysis availability gate. Ordered
 * so the tiers cannot silently contradict each other: Tier 1 is absolute
 * and checked first, Tier 2 can only make things worse than Tier 3 (never
 * better), and only the worse of Tier 2/3's bands is used.
 */
class DetermineAnalysisAvailability
{
    private const UNRECOGNIZED_PROPORTION_LIMIT = 0.25;

    public function determine(NormalizedBettingSlip $input, int $dataQualityScore): AnalysisGateResult
    {
        $legCount = count($input->legs);

        // Tier 1 — sport hard gate. Any non-football leg blocks the whole
        // slip, regardless of everything else (Product Office's explicit
        // recommendation, §7.4 / §17).
        $hasNonFootballLeg = false;
        foreach ($input->legs as $leg) {
            if ($leg->sport->sportCode !== NormalizeSport::FOOTBALL_CODE) {
                $hasNonFootballLeg = true;
                break;
            }
        }

        if ($hasNonFootballLeg) {
            return new AnalysisGateResult(
                availability: AnalysisAvailability::Unavailable,
                effectiveBand: DataQualityBand::Insufficient,
                reasonCodes: [ReasonCode::SportUnsupported, ReasonCode::AnalysisUnavailable],
            );
        }

        // Tier 2 — market-unrecognized proportion gate.
        $unrecognizedCount = 0;
        foreach ($input->legs as $leg) {
            if ($leg->market->status === NormalizationStatus::Unrecognized) {
                $unrecognizedCount++;
            }
        }
        $unrecognizedProportion = $unrecognizedCount / $legCount;

        if ($unrecognizedProportion > self::UNRECOGNIZED_PROPORTION_LIMIT) {
            return new AnalysisGateResult(
                availability: AnalysisAvailability::Unavailable,
                effectiveBand: DataQualityBand::Insufficient,
                reasonCodes: [ReasonCode::MarketUnrecognized, ReasonCode::AnalysisUnavailable],
            );
        }

        $tier2Cap = $unrecognizedProportion > 0 ? DataQualityBand::Limited : null;

        // Tier 3 — partial-normalization deduction score (CalculateDataQuality).
        $tier3Band = DataQualityBand::forScore($dataQualityScore);

        $effectiveBand = $tier2Cap !== null ? $tier2Cap->worseOf($tier3Band) : $tier3Band;

        if ($effectiveBand === DataQualityBand::Insufficient) {
            return new AnalysisGateResult(
                availability: AnalysisAvailability::Unavailable,
                effectiveBand: $effectiveBand,
                reasonCodes: [ReasonCode::AnalysisUnavailable],
            );
        }

        if ($effectiveBand === DataQualityBand::Limited) {
            $reasonCodes = [ReasonCode::AnalysisLimited];
            if ($unrecognizedCount > 0) {
                $reasonCodes[] = ReasonCode::MarketUnrecognized;
            }

            return new AnalysisGateResult(
                availability: AnalysisAvailability::Limited,
                effectiveBand: $effectiveBand,
                reasonCodes: $reasonCodes,
            );
        }

        return new AnalysisGateResult(
            availability: AnalysisAvailability::Full,
            effectiveBand: $effectiveBand,
            reasonCodes: [],
        );
    }
}
