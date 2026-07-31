<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Normalization\NormalizedMarket;

/**
 * A successfully mapped outcome — the `NormalizedMarket` shape the Risk
 * Engine expects, plus the plain-language description the customer sees.
 */
final readonly class MappedSelection
{
    public function __construct(
        public NormalizedMarket $normalizedMarket,
        public string $selectionDescription,
    ) {}
}
