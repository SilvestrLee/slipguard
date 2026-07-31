<?php

namespace App\Domain\MarketIntelligence;

use Carbon\CarbonImmutable;

/**
 * U-17.5 §5 — the canonical fixture shape, confirmed against real
 * `U-17.3` payloads. Framework-agnostic (no Eloquent), mirroring
 * `NormalizedBettingSlipLeg`'s own plain-value-object discipline —
 * `MarketIntelligenceFixture` (the Eloquent model) is a separate,
 * persistence-layer concern.
 */
final readonly class CanonicalFixture
{
    public function __construct(
        public string $providerEventId,
        public string $competitionKey,
        public string $homeTeam,
        public string $awayTeam,
        public CarbonImmutable $commenceTime,
        public CarbonImmutable $retrievedAt,
    ) {}
}
