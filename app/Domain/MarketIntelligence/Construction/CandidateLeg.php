<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Normalization\NormalizedMarket;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;
use Carbon\CarbonImmutable;

/**
 * U-17.6 §14 / `U-17.6A` — one concrete, fully-priced outcome option within
 * a `CandidateSlot`. Passed every eligibility gate (`CBE-001`-`009` all pass
 * by construction; a leg failing any one never becomes a `CandidateLeg` at
 * all, so there is no separate "which gates passed" list to store — it is
 * always all nine). Ranking (`U-17.6` §8) applies to the *slot* (which
 * fixture+market opportunity), never to which outcome option within it is
 * "better" — per `U-17.6A`, that choice belongs to the customer alone, so
 * this value object carries no rank of its own.
 */
final readonly class CandidateLeg
{
    public function __construct(
        public MarketIntelligenceFixture $fixture,
        public MarketFamily $marketFamily,
        public NormalizedMarket $normalizedMarket,
        public string $selectionDescription,
        public string $decimalOdds,
        public string $bookmakerKey,
        public bool $evidenceFresh,
        public CarbonImmutable $retrievedAt,
    ) {}

    /** A stable identity for "one leg per fixture" (CBE-008) bookkeeping. */
    public function fixtureKey(): string
    {
        return $this->fixture->provider_event_id;
    }
}
