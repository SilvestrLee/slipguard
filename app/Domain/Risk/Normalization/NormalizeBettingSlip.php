<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Exceptions\BettingSlipNotReadyException;
use App\Models\BettingSlip;

/**
 * The engine-preparation boundary: turns a Ready slip's raw, free-text legs
 * into normalized, taxonomy-classified facts. Deliberately does not
 * calculate risk, persist anything, or change the slip's state — that is
 * the future Risk Engine's job (Stage 2 onward), not this one's.
 */
class NormalizeBettingSlip
{
    public function __construct(
        private readonly NormalizeSport $normalizeSport = new NormalizeSport,
        private readonly NormalizeFootballMarket $normalizeMarket = new NormalizeFootballMarket,
    ) {}

    public function execute(BettingSlip $bettingSlip): NormalizedBettingSlip
    {
        if ($bettingSlip->status !== BettingSlipStatus::Ready) {
            throw new BettingSlipNotReadyException;
        }

        $legs = $bettingSlip->legs->map(function ($leg) {
            return new NormalizedBettingSlipLeg(
                bettingSlipLegId: $leg->id,
                displayOrder: $leg->display_order,
                sport: $this->normalizeSport->normalize($leg->sport),
                market: $this->normalizeMarket->normalize($leg->market_name, $leg->selection_name),
            );
        })->all();

        return new NormalizedBettingSlip(
            bettingSlipId: $bettingSlip->id,
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
            legs: $legs,
        );
    }
}
