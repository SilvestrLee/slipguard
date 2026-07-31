<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\MarketIntelligence\EvidenceQuality;
use Carbon\CarbonImmutable;

/**
 * U-17.6 §4 — one canonical market's conservative-lowest-selected quote for
 * one specific outcome name, after cross-bookmaker selection.
 */
final readonly class SelectedOutcomeQuote
{
    public function __construct(
        public string $outcomeName,
        public string $price,
        public ?string $point,
        public string $bookmakerKey,
        public CarbonImmutable $retrievedAt,
        public ?CarbonImmutable $providerLastUpdate,
        public EvidenceQuality $evidenceQuality,
        public CarbonImmutable $cacheExpiresAt,
    ) {}
}
