<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * `U-17.6A` — a ranked slot paired with the customer's own chosen outcome
 * option for it. This pairing only exists once the customer has acted;
 * `EvaluateCandidateSelections` never chooses `chosenLeg` itself.
 */
final readonly class ChosenSlot
{
    public function __construct(
        public CandidateSlot $slot,
        public CandidateLeg $chosenLeg,
    ) {}

    public function fixtureKey(): string
    {
        return $this->chosenLeg->fixtureKey();
    }
}
