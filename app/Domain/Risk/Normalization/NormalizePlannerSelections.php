<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Models\PlannerSelection;
use Illuminate\Support\Collection;

/**
 * The Planner's own normalization entry point — deliberately a sibling of
 * `NormalizeBettingSlip`, not a modification of it (Foundation Freeze):
 * that class is coupled to a `Ready` `BettingSlip` and its persisted legs,
 * while a Planner session's current candidate set lives in `PlannerSelection`
 * rows that have no such lifecycle. Both classes reuse the exact same
 * underlying `NormalizeSport`/`NormalizeFootballMarket` services — the
 * normalization *rules* are shared and unchanged; only the input shape
 * differs.
 *
 * `NormalizedBettingSlipLeg::bettingSlipLegId` carries the source
 * `PlannerSelection`'s own id here (not a real `betting_slip_legs.id`) —
 * this is what lets `RankLegsByStructuralWeakness`'s output be correlated
 * back to the exact `PlannerSelection` row it describes.
 */
class NormalizePlannerSelections
{
    public function __construct(
        private readonly NormalizeSport $normalizeSport = new NormalizeSport,
        private readonly NormalizeFootballMarket $normalizeMarket = new NormalizeFootballMarket,
    ) {}

    /**
     * @param  Collection<int, PlannerSelection>  $selections
     */
    public function execute(int $plannerSessionId, Collection $selections): NormalizedBettingSlip
    {
        $legs = $selections->map(function (PlannerSelection $selection) {
            $sport = $this->normalizeSport->normalize($selection->sport);

            $market = $sport->sportCode === NormalizeSport::FOOTBALL_CODE
                ? $this->normalizeMarket->normalize($selection->market_name, $selection->selection_name)
                : NormalizedMarket::notClassifiedForSport($selection->market_name, $selection->selection_name, $sport->status, FootballMarketTaxonomyV1::VERSION);

            return new NormalizedBettingSlipLeg(
                bettingSlipLegId: $selection->id,
                displayOrder: $selection->display_order,
                sport: $sport,
                market: $market,
                decimalOdds: (string) $selection->decimal_odds,
            );
        })->values()->all();

        return new NormalizedBettingSlip(
            bettingSlipId: $plannerSessionId,
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
            legs: $legs,
        );
    }
}
