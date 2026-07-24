<?php

namespace App\Domain\Risk\Normalization;

final readonly class NormalizedBettingSlip
{
    /**
     * @param  array<int, NormalizedBettingSlipLeg>  $legs
     */
    public function __construct(
        public int $bettingSlipId,
        public string $taxonomyVersion,
        public array $legs,
    ) {}
}
