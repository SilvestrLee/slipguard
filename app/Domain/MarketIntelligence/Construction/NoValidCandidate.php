<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * U-17.6 §15/§17 — a real, specific reason, never a generic "something went
 * wrong." `detail` is a plain-language sentence naming the actual binding
 * constraint (e.g. which competitions/window were insufficient), composed
 * by the caller that detected the rejection — this object itself only
 * carries the already-decided reason and detail.
 */
final readonly class NoValidCandidate
{
    public function __construct(
        public CandidateRejectionReason $reason,
        public string $detail,
    ) {}
}
