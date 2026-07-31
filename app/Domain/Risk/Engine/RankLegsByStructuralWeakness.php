<?php

namespace App\Domain\Risk\Engine;

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Normalization\NormalizedBettingSlipLeg;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\LegAttribution;
use App\Domain\Risk\Results\LegAttributionRanking;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Results\RiskAnalysisResult;
use App\Domain\Risk\Taxonomy\MarketComplexity;
use Brick\Math\BigDecimal;

/**
 * Data Science Lab's Marginal Structural Contribution (MSC) model —
 * docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md, accepted
 * PO-U06.2A-AC-001 (docs/00-governance/DECISION_LOG.md). A pure calculator,
 * exactly like CalculateStructuralRisk: no persistence, HTTP, clock, or
 * randomness.
 *
 * Ranks a slip's own legs from weakest (most responsible for structural
 * risk) to strongest by leave-one-out marginal contribution —
 * MSC_i = score(S) - score(S₋ᵢ) — re-invoking the unmodified
 * CalculateStructuralRisk engine once per candidate leg removed. Adds no
 * new factor, weight, threshold, cap, or reason code to Rule Set 2026.1
 * (specification §3.2); every number produced here comes from that engine,
 * never from a new formula.
 *
 * Per-leg normalization has no cross-leg dependency (specification §2
 * point 2, verified against NormalizeBettingSlip/NormalizeFootballMarket),
 * so removing a leg never requires re-normalizing the remainder — this
 * class only ever removes an already-normalized leg before re-invoking the
 * engine on what's left.
 */
final class RankLegsByStructuralWeakness
{
    public function __construct(
        private readonly CalculateStructuralRisk $calculateStructuralRisk = new CalculateStructuralRisk,
    ) {}

    public function calculate(NormalizedBettingSlip $slip): LegAttributionRanking
    {
        $legs = array_values($slip->legs);

        $baseline = $this->calculateStructuralRisk->calculate($slip);

        // Specification §2/§3.4: ranking does not apply to a single-leg
        // "accumulator" (nothing to compare against) or to a baseline that
        // is itself Unavailable (no score to take a marginal difference
        // against).
        if (count($legs) < 2 || $baseline->availability === AnalysisAvailability::Unavailable) {
            return new LegAttributionRanking(
                baselineAvailability: $baseline->availability,
                baselineScore: $baseline->structuralScore,
                baselineBand: $baseline->riskBand,
                attributions: [],
            );
        }

        $attributions = [];
        foreach ($legs as $index => $leg) {
            $attributions[] = $this->attributeLeg($slip, $legs, $index, $leg, $baseline);
        }

        return new LegAttributionRanking(
            baselineAvailability: $baseline->availability,
            baselineScore: $baseline->structuralScore,
            baselineBand: $baseline->riskBand,
            attributions: $this->rankAndAssignPositions($attributions),
        );
    }

    /**
     * @param  array<int, NormalizedBettingSlipLeg>  $legs
     */
    private function attributeLeg(
        NormalizedBettingSlip $slip,
        array $legs,
        int $index,
        NormalizedBettingSlipLeg $leg,
        RiskAnalysisResult $baseline,
    ): LegAttribution {
        $remainingLegs = array_values(array_filter(
            $legs,
            fn ($candidate, $candidateIndex) => $candidateIndex !== $index,
            ARRAY_FILTER_USE_BOTH,
        ));

        $subSlip = new NormalizedBettingSlip($slip->bettingSlipId, $slip->taxonomyVersion, $remainingLegs);
        $subResult = $this->calculateStructuralRisk->calculate($subSlip);

        if ($subResult->availability === AnalysisAvailability::Unavailable) {
            // Specification §4: this leg's removal alone pushes the
            // remainder past the analysis-availability gate — no numeric
            // MSC exists, so none is computed or forced, and no rank is
            // assigned (never an interpolated or default rank).
            return new LegAttribution(
                bettingSlipLegId: $leg->bettingSlipLegId,
                displayOrder: $leg->displayOrder,
                decimalOdds: $leg->decimalOdds,
                marketComplexity: $leg->market->complexity,
                gateDependent: true,
                rank: null,
                msc: null,
                mscPrecise: null,
                scoreWithoutLeg: null,
                bandWithoutLeg: null,
                availabilityWithoutLeg: $subResult->availability,
                reasonCodesGainedWithoutLeg: [],
                reasonCodesLostWithoutLeg: [],
            );
        }

        return new LegAttribution(
            bettingSlipLegId: $leg->bettingSlipLegId,
            displayOrder: $leg->displayOrder,
            decimalOdds: $leg->decimalOdds,
            marketComplexity: $leg->market->complexity,
            gateDependent: false,
            rank: null,
            msc: $baseline->structuralScore - $subResult->structuralScore,
            mscPrecise: $baseline->rescaledScorePrecise->minus($subResult->rescaledScorePrecise),
            scoreWithoutLeg: $subResult->structuralScore,
            bandWithoutLeg: $subResult->riskBand,
            availabilityWithoutLeg: $subResult->availability,
            reasonCodesGainedWithoutLeg: array_values(array_filter(
                $subResult->reasonCodes,
                fn (ReasonCode $code) => ! in_array($code, $baseline->reasonCodes, true),
            )),
            reasonCodesLostWithoutLeg: array_values(array_filter(
                $baseline->reasonCodes,
                fn (ReasonCode $code) => ! in_array($code, $subResult->reasonCodes, true),
            )),
        );
    }

    /**
     * Specification §3.5's five-step tie-break, applied only among rankable
     * (non gate-dependent) legs: integer MSC descending, then unrounded
     * precision descending, then individual odds descending, then market
     * complexity descending, then original entry order ascending — the
     * last step exists purely to guarantee a strict total order once every
     * risk-relevant signal is exhausted (§3.5, proven necessary, not
     * decorative, by the specification's §7.2 worked example). Gate-
     * dependent legs are appended after, in original slip order, with no
     * rank assigned (§4).
     *
     * @param  array<int, LegAttribution>  $attributions
     * @return array<int, LegAttribution>
     */
    private function rankAndAssignPositions(array $attributions): array
    {
        $rankable = array_values(array_filter($attributions, fn (LegAttribution $a) => ! $a->gateDependent));
        $gateDependent = array_values(array_filter($attributions, fn (LegAttribution $a) => $a->gateDependent));

        usort($rankable, function (LegAttribution $a, LegAttribution $b) {
            return $b->msc <=> $a->msc
                ?: $b->mscPrecise->compareTo($a->mscPrecise)
                ?: BigDecimal::of($b->decimalOdds)->compareTo(BigDecimal::of($a->decimalOdds))
                ?: $this->complexityRank($b->marketComplexity) <=> $this->complexityRank($a->marketComplexity)
                ?: $a->displayOrder <=> $b->displayOrder;
        });

        foreach ($rankable as $position => $attribution) {
            $rankable[$position] = $attribution->withRank($position + 1);
        }

        return [...$rankable, ...$gateDependent];
    }

    /**
     * Ordinal scale for tie-break step 4 — mirrors MarketComplexityFactor's
     * own points (simple=0, moderate=1, complex=2); `unknown` ranks below
     * all three, consistent with RF-005 never treating unknown as evidence
     * of complexity (Rule Set 2026.1 §10).
     */
    private function complexityRank(MarketComplexity $complexity): int
    {
        return match ($complexity) {
            MarketComplexity::Complex => 3,
            MarketComplexity::Moderate => 2,
            MarketComplexity::Simple => 1,
            MarketComplexity::Unknown => 0,
        };
    }
}
