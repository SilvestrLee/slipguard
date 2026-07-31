<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;

/**
 * U-17.6 §14 — "for every leg considered but excluded: the specific rule ID
 * and plain-language reason." `outcomeDescription` is null when the whole
 * fixture+market slot was excluded before any individual outcome could be
 * evaluated (e.g. `CBE-001`-`CBE-004`); it names the specific outcome when
 * only that outcome failed while its slot otherwise survives (e.g. one
 * outcome's price falls outside the odds bounds while the others remain
 * eligible — `U-17.6A` §4's "a market is offered only if at least one of
 * its outcomes independently passes").
 */
final readonly class ExcludedCandidateAttempt
{
    public function __construct(
        public MarketIntelligenceFixture $fixture,
        public MarketFamily $marketFamily,
        public ?string $outcomeDescription,
        public CandidateExclusionReason $reason,
    ) {}
}
