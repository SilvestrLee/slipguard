<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * `BuildAccumulatorCandidate`'s successful result — enough ranked, eligible
 * slots existed to propose a full candidate. `proposedSlots` is exactly
 * `PlanningBrief::$legCountTarget` slots, best-ranked first; `spareSlots`
 * are the remaining ranked, eligible slots, held in reserve for
 * `EvaluateCandidateSelections`' own bounded replacement mechanism.
 */
final readonly class DiscoveredSlots
{
    /**
     * @param  array<int, CandidateSlot>  $proposedSlots
     * @param  array<int, CandidateSlot>  $spareSlots
     * @param  array<int, ExcludedCandidateAttempt>  $excluded
     */
    public function __construct(
        public array $proposedSlots,
        public array $spareSlots,
        public array $excluded,
    ) {}
}
