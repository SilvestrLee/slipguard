<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * The result of running every `CBE-00x` gate for one fixture+market pair.
 * `options` may legitimately be empty (every outcome failed some gate, or
 * no evidence existed at all) — the caller simply builds no slot for this
 * fixture+market in that case, per `excluded`'s own recorded reasons.
 */
final readonly class EligibilityEvaluation
{
    /**
     * @param  array<int, CandidateLeg>  $options
     * @param  array<int, ExcludedCandidateAttempt>  $excluded
     */
    public function __construct(
        public array $options,
        public array $excluded,
    ) {}
}
