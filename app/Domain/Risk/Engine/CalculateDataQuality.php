<?php

namespace App\Domain\Risk\Engine;

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Normalization\NormalizeSport;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\DataQualityResult;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

/**
 * Rule Set 2026.1 §16 — deliberately independent of structural risk (§12 of
 * the E-06B sprint: "neither determines the other's score").
 *
 * Scope is narrow on purpose: this measures only how well an all-football
 * slip's markets normalized (partial-normalization deductions). Sport-level
 * problems (unsupported/unrecognized sport) are not scored here at all —
 * they are the Analysis Availability Gate's Tier 1 hard gate
 * (DetermineAnalysisAvailability), evaluated separately, because blending
 * "wrong sport" into a soft data-quality deduction risked exactly the
 * confusing partial-credit product promise Product Office rejected.
 */
class CalculateDataQuality
{
    private const PARTIAL_DEDUCTION = 8;

    private const PARTIAL_CATEGORY_CAP = 40;

    public function calculate(NormalizedBettingSlip $input): DataQualityResult
    {
        $deductions = [];
        $totalDeduction = 0;

        foreach ($input->legs as $leg) {
            if ($leg->sport->sportCode !== NormalizeSport::FOOTBALL_CODE) {
                // Sport-level problems are Tier 1's concern, not this
                // score's — excluded here, not deducted twice.
                continue;
            }

            if ($leg->market->status === NormalizationStatus::Partial) {
                $deductions[] = [
                    'leg_id' => $leg->bettingSlipLegId,
                    'reason' => 'market_partial',
                    'deduction' => self::PARTIAL_DEDUCTION,
                ];
                $totalDeduction += self::PARTIAL_DEDUCTION;
            }
        }

        $cappedDeduction = min($totalDeduction, self::PARTIAL_CATEGORY_CAP);
        $score = max(0, 100 - $cappedDeduction);
        $band = DataQualityBand::forScore($score);

        $reasonCodes = count($deductions) > 0 ? [ReasonCode::NormalizationPartial] : [];

        return new DataQualityResult(
            score: $score,
            band: $band,
            reasonCodes: $reasonCodes,
            deductions: $deductions,
        );
    }
}
