<?php

namespace App\Actions\MarketIntelligence;

use App\Domain\MarketIntelligence\AcquireMarketEvidence;
use App\Domain\MarketIntelligence\Construction\CandidateRejectionReason;
use App\Domain\MarketIntelligence\Construction\DiscoverAccumulatorSlots;
use App\Domain\MarketIntelligence\Construction\DiscoveredSlots;
use App\Domain\MarketIntelligence\Construction\NoValidCandidate;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Models\MarketIntelligenceFixture;
use Carbon\CarbonImmutable;

/**
 * The one method the Builder UI (Increment 2, not this pass) calls to turn
 * a planning brief into ranked, eligible slots. Ensures evidence is fresh
 * (acquiring it via the existing, unmodified `AcquireMarketEvidence` if
 * not), then delegates discovery/ranking/dedup entirely to
 * `DiscoverAccumulatorSlots`. Never chooses an outcome — see `U-17.6A`.
 */
class BuildAccumulatorCandidate
{
    public function __construct(
        private readonly AcquireMarketEvidence $acquireEvidence = new AcquireMarketEvidence,
        private readonly DiscoverAccumulatorSlots $discoverSlots = new DiscoverAccumulatorSlots,
    ) {}

    public function execute(PlanningBrief $brief): DiscoveredSlots|NoValidCandidate
    {
        foreach ($brief->competitions as $competition) {
            if (! $this->hasFreshEvidence($competition)) {
                $this->acquireEvidence->execute($competition, $brief->requestedMarkets);
            }
        }

        $discovery = $this->discoverSlots->execute($brief);
        $slots = $discovery['slots'];

        if ($slots === []) {
            return new NoValidCandidate(
                CandidateRejectionReason::InsufficientFixtures,
                CandidateRejectionReason::InsufficientFixtures->label(),
            );
        }

        if (count($slots) < $brief->legCountTarget) {
            return new NoValidCandidate(
                CandidateRejectionReason::InsufficientEligibleLegs,
                CandidateRejectionReason::InsufficientEligibleLegs->label(),
            );
        }

        return new DiscoveredSlots(
            proposedSlots: array_slice($slots, 0, $brief->legCountTarget),
            spareSlots: array_slice($slots, $brief->legCountTarget),
            excluded: $discovery['excluded'],
        );
    }

    private function hasFreshEvidence(string $competitionKey): bool
    {
        return MarketIntelligenceFixture::query()
            ->where('competition_key', $competitionKey)
            ->where('cache_expires_at', '>', CarbonImmutable::now())
            ->exists();
    }
}
