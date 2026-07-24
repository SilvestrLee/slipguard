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
            $sport = $this->normalizeSport->normalize($leg->sport);

            // Market normalization only ever runs for a recognized football
            // leg. A leg whose sport is unsupported or unrecognized never
            // reaches FootballMarketTaxonomyV1 — applying that taxonomy to a
            // non-football (or unidentified) leg would produce a market
            // classification that isn't semantically meaningful, even if the
            // free text coincidentally matches a football alias.
            $market = $sport->sportCode === NormalizeSport::FOOTBALL_CODE
                ? $this->normalizeMarket->normalize($leg->market_name, $leg->selection_name)
                : NormalizedMarket::notClassifiedForSport($leg->market_name, $leg->selection_name, $sport->status, FootballMarketTaxonomyV1::VERSION);

            return new NormalizedBettingSlipLeg(
                bettingSlipLegId: $leg->id,
                displayOrder: $leg->display_order,
                sport: $sport,
                market: $market,
            );
        })->all();

        return new NormalizedBettingSlip(
            bettingSlipId: $bettingSlip->id,
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
            legs: $legs,
        );
    }
}
