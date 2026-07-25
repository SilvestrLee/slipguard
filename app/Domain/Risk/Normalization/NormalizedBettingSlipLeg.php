<?php

namespace App\Domain\Risk\Normalization;

final readonly class NormalizedBettingSlipLeg
{
    /**
     * @param  string  $decimalOdds  Fixed-precision string, e.g. "1.90" — never a float. Odds require no
     *                               normalization of their own (already a clean DECIMAL(6,2) column), but the
     *                               Risk Engine needs them, so they travel through the normalization boundary
     *                               alongside sport/market rather than being re-fetched separately.
     */
    public function __construct(
        public int $bettingSlipLegId,
        public int $displayOrder,
        public NormalizedSport $sport,
        public NormalizedMarket $market,
        public string $decimalOdds,
    ) {}
}
