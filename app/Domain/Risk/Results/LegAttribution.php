<?php

namespace App\Domain\Risk\Results;

use App\Domain\Risk\Taxonomy\MarketComplexity;
use Brick\Math\BigDecimal;

/**
 * One leg's result under the Marginal Structural Contribution (MSC) model —
 * docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md §3, accepted
 * PO-U06.2A-AC-001 (docs/00-governance/DECISION_LOG.md).
 *
 * `rank` is null only when `gateDependent` is true (specification §4):
 * removing this leg alone would push the remaining accumulator's
 * unrecognized-market proportion past Tier 2's threshold, making the
 * remainder Unavailable. There is no valid subtraction to compute a
 * numeric MSC in that case, so none is invented and no interpolated or
 * default rank is assigned — this leg is reported separately instead.
 */
final readonly class LegAttribution
{
    /**
     * @param  array<int, ReasonCode>  $reasonCodesGainedWithoutLeg  Present in S₋ᵢ but not in S — reason codes this leg's removal would introduce.
     * @param  array<int, ReasonCode>  $reasonCodesLostWithoutLeg  Present in S but not in S₋ᵢ — reason codes this leg's removal would remove.
     */
    public function __construct(
        public int $bettingSlipLegId,
        public int $displayOrder,
        public string $decimalOdds,
        public MarketComplexity $marketComplexity,
        public bool $gateDependent,
        public ?int $rank,
        public ?int $msc,
        public ?BigDecimal $mscPrecise,
        public ?int $scoreWithoutLeg,
        public ?RiskBand $bandWithoutLeg,
        public AnalysisAvailability $availabilityWithoutLeg,
        public array $reasonCodesGainedWithoutLeg,
        public array $reasonCodesLostWithoutLeg,
    ) {}

    public function withRank(int $rank): self
    {
        return new self(
            $this->bettingSlipLegId,
            $this->displayOrder,
            $this->decimalOdds,
            $this->marketComplexity,
            $this->gateDependent,
            $rank,
            $this->msc,
            $this->mscPrecise,
            $this->scoreWithoutLeg,
            $this->bandWithoutLeg,
            $this->availabilityWithoutLeg,
            $this->reasonCodesGainedWithoutLeg,
            $this->reasonCodesLostWithoutLeg,
        );
    }
}
