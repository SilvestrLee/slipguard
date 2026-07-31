<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * `U-17.6A` — a ranked fixture+market opportunity: the Builder's actual
 * deterministic unit of discovery/ranking/construction, per Customer
 * Outcome Sovereignty (`PO-U17.6A-AC-001`). `options` holds every outcome
 * that independently survived every eligibility gate (2-3 for the
 * default-enabled families) — the customer picks exactly one; the Builder
 * never does. `rank`/`rankingReason` are attached by `RankEligibleLegs` via
 * `withRank()`, mirroring `LegAttribution::withRank()`'s own pattern.
 */
final readonly class CandidateSlot
{
    /**
     * @param  array<int, CandidateLeg>  $options  Every surviving outcome option for this fixture+market, never empty.
     */
    public function __construct(
        public array $options,
        public ?int $rank = null,
        public ?string $rankingReason = null,
    ) {}

    public function withRank(int $rank, string $rankingReason): self
    {
        return new self($this->options, $rank, $rankingReason);
    }

    /** A stable identity for "one leg per fixture" (CBE-008) bookkeeping — every option shares the same fixture. */
    public function fixtureKey(): string
    {
        return $this->options[0]->fixtureKey();
    }
}
