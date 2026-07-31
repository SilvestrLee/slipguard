<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Results\RiskBand;

/**
 * U-17.6 §2 — the planning-brief input contract. A plain value object, no
 * Livewire/HTTP/persistence concern — the Builder UI (Increment 2, not this
 * pass) is responsible for validating raw form input into this shape.
 */
final readonly class PlanningBrief
{
    /**
     * @param  array<int, string>  $competitions  Configured competition keys (config('slipguard-market-intelligence.allowed_competitions')), non-empty.
     * @param  int  $windowDays  1-7 (U-17.1's own MVP planning-window boundary).
     * @param  int  $legCountTarget  2-8, per CandidateConstructionRuleSet2026_1::LEG_COUNT_MIN/MAX.
     * @param  array<int, string>  $requestedMarkets  Provider market keys (MapOddsApiMarket::supportedProviderKeys()), non-empty, Correct Score never included (U-17.6 §7).
     * @param  string|null  $targetOddsMin  Decimal odds string, or null if no target set.
     * @param  array<int, string>  $excludedFixtureIds  Provider event IDs to exclude (e.g. from a just-rejected candidate, U-17.6 §16).
     */
    public function __construct(
        public array $competitions,
        public int $windowDays,
        public int $legCountTarget,
        public array $requestedMarkets,
        public ?string $targetOddsMin,
        public ?string $targetOddsMax,
        public RiskBand $riskCeiling = RiskBand::High,
        public array $excludedFixtureIds = [],
    ) {}

    public function hasTargetOdds(): bool
    {
        return $this->targetOddsMin !== null && $this->targetOddsMax !== null;
    }
}
