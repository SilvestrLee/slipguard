<?php

namespace App\Domain\MarketIntelligence;

use Carbon\CarbonImmutable;

/**
 * U-17.5 §5/§6 — one bookmaker's quote for one canonical market on one
 * fixture. `outcomes` matches the real shape confirmed in `U-17.3`:
 * `[{name: string, price: string, point?: string}]` — `point` (the Total
 * Goals line value) is the one field present on `totals` and absent
 * everywhere else, per the real trial payloads.
 *
 * @param  array<int, array{name: string, price: string, point?: string}>  $outcomes
 */
final readonly class CanonicalMarketQuote
{
    public function __construct(
        public string $providerEventId,
        public string $canonicalMarket,
        public string $providerMarketKey,
        public string $bookmakerKey,
        public array $outcomes,
        public ?CarbonImmutable $providerLastUpdate,
        public CarbonImmutable $retrievedAt,
        public EvidenceQuality $evidenceQuality,
    ) {}
}
