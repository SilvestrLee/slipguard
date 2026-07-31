<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\MarketIntelligence\MapOddsApiMarket;
use App\Models\MarketIntelligenceFixture;
use Carbon\CarbonImmutable;

/**
 * `U-17.6` §3/§8, resequenced by `U-17.6A`: the Builder's genuinely
 * deterministic responsibility — find every fixture+market opportunity
 * that survives every `CBE-00x` gate, rank the survivors, and enforce
 * `CBE-008` (one slot per fixture). Never chooses an outcome within a
 * slot — that is the customer's decision (`U-17.6A`), made downstream.
 */
final class DiscoverAccumulatorSlots
{
    public function __construct(
        private readonly DetermineLegEligibility $eligibility = new DetermineLegEligibility,
        private readonly RankEligibleLegs $rankSlots = new RankEligibleLegs,
        private readonly MapOddsApiMarket $marketMapper = new MapOddsApiMarket,
    ) {}

    /**
     * @return array{slots: array<int, CandidateSlot>, excluded: array<int, ExcludedCandidateAttempt>}
     */
    public function execute(PlanningBrief $brief): array
    {
        $fixtures = MarketIntelligenceFixture::query()
            ->whereIn('competition_key', $brief->competitions)
            ->where('commence_time', '>=', CarbonImmutable::now())
            ->where('commence_time', '<=', CarbonImmutable::now()->addDays($brief->windowDays))
            ->get();

        $families = collect($brief->requestedMarkets)
            ->map(fn (string $key) => $this->marketMapper->toCanonicalFamily($key))
            ->filter()
            ->unique()
            ->values();

        $rawSlots = [];
        $excluded = [];

        foreach ($fixtures as $fixture) {
            foreach ($families as $family) {
                $evaluation = $this->eligibility->evaluate($brief, $fixture, $family);
                array_push($excluded, ...$evaluation->excluded);

                if ($evaluation->options !== []) {
                    $rawSlots[] = new CandidateSlot($evaluation->options);
                }
            }
        }

        $ranked = $this->rankSlots->rank($rawSlots, $brief);

        // CBE-008: one leg per fixture — the ranked list already orders
        // opportunities best-first, so keeping only the first occurrence
        // of each fixture key is exactly "one slot per fixture, the
        // highest-ranked one," never an arbitrary pick.
        $usedFixtures = [];
        $deduped = [];

        foreach ($ranked as $slot) {
            $key = $slot->fixtureKey();

            if (isset($usedFixtures[$key])) {
                $first = $slot->options[0];
                $excluded[] = new ExcludedCandidateAttempt($first->fixture, $first->marketFamily, null, CandidateExclusionReason::FixtureAlreadyUsed);

                continue;
            }

            $usedFixtures[$key] = true;
            $deduped[] = $slot;
        }

        // Re-sequence ranks after dedup so positions stay contiguous
        // (1..n) rather than carrying gaps from the removed duplicates.
        $resequenced = [];

        foreach (array_values($deduped) as $position => $slot) {
            $resequenced[] = $slot->withRank($position + 1, $slot->rankingReason);
        }

        return ['slots' => $resequenced, 'excluded' => $excluded];
    }
}
