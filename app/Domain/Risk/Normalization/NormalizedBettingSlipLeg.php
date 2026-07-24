<?php

namespace App\Domain\Risk\Normalization;

final readonly class NormalizedBettingSlipLeg
{
    public function __construct(
        public int $bettingSlipLegId,
        public int $displayOrder,
        public NormalizedSport $sport,
        public NormalizedMarket $market,
    ) {}
}
