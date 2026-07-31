<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * `U-17.6A` §4/§7 — a direct, load-bearing consequence of Customer Outcome
 * Sovereignty: when a risk-ceiling breach (U-17.6 §6) is found after the
 * customer's own choices are evaluated, the Builder cannot silently swap in
 * a replacement slot's outcome for them — a replacement slot is itself a
 * market the customer hasn't chosen an outcome for yet. This result asks
 * for exactly one more customer decision (never re-opening a choice
 * already made for a surviving slot), bounded by
 * CandidateConstructionRuleSet2026_1::MAX_RISK_REPLACEMENT_ATTEMPTS.
 */
final readonly class ReplacementNeeded
{
    public function __construct(
        public CandidateSlot $replacementSlot,
        public string $removedFixtureKey,
        public int $attemptsUsedSoFar,
        public CandidateRejectionReason $reason,
    ) {}
}
