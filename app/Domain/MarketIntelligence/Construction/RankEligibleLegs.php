<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\MarketIntelligence\MapOddsApiMarket;
use Carbon\CarbonImmutable;

/**
 * U-17.6 §8 — ranks eligible fixture+market slots against each other (never
 * outcomes within the same slot — per `U-17.6A`, choosing between a slot's
 * own outcome options is the customer's decision, not a ranking question).
 *
 * Dimension 2 ("canonical mapping confidence") is a documented no-op here:
 * every option that reaches a `CandidateSlot` at all already passed
 * `CBE-005` (Complete/unambiguous mapping, never a `Partial` guess) by
 * construction, so it can never actually differentiate two already-eligible
 * slots — kept in the comparator for fidelity to §8's own four-key order,
 * not because it does anything yet.
 */
final class RankEligibleLegs
{
    public function __construct(
        private readonly MapOddsApiMarket $marketMapper = new MapOddsApiMarket,
    ) {}

    /**
     * @param  array<int, CandidateSlot>  $slots
     * @return array<int, CandidateSlot> Ranked, rank/rankingReason assigned.
     */
    public function rank(array $slots, PlanningBrief $brief): array
    {
        $decorated = array_map(fn (CandidateSlot $slot) => [
            'slot' => $slot,
            'freshness' => $this->mostRecentRetrieval($slot),
            'preferenceIndex' => $this->marketPreferenceIndex($slot, $brief),
            'kickoff' => $slot->options[0]->fixture->commence_time,
            'fixtureId' => $slot->options[0]->fixture->id,
        ], $slots);

        usort($decorated, function (array $a, array $b) {
            return $b['freshness']->timestamp <=> $a['freshness']->timestamp
                ?: $a['preferenceIndex'] <=> $b['preferenceIndex']
                ?: $a['kickoff']->timestamp <=> $b['kickoff']->timestamp
                ?: $a['fixtureId'] <=> $b['fixtureId'];
        });

        $ranked = [];

        foreach ($decorated as $position => $entry) {
            $ranked[] = $entry['slot']->withRank($position + 1, $this->rankingReason($entry));
        }

        return $ranked;
    }

    private function mostRecentRetrieval(CandidateSlot $slot): CarbonImmutable
    {
        $latest = $slot->options[0]->retrievedAt;

        foreach ($slot->options as $option) {
            if ($option->retrievedAt->isAfter($latest)) {
                $latest = $option->retrievedAt;
            }
        }

        return $latest;
    }

    private function marketPreferenceIndex(CandidateSlot $slot, PlanningBrief $brief): int
    {
        $family = $slot->options[0]->marketFamily;

        foreach ($brief->requestedMarkets as $index => $providerKey) {
            if ($this->marketMapper->toCanonicalFamily($providerKey) === $family) {
                return $index;
            }
        }

        return count($brief->requestedMarkets);
    }

    /**
     * @param  array{freshness: CarbonImmutable, preferenceIndex: int}  $entry
     */
    private function rankingReason(array $entry): string
    {
        return $entry['preferenceIndex'] === 0
            ? 'Matches your first-listed market preference'
            : 'Evidence freshness and planning-brief market order';
    }
}
