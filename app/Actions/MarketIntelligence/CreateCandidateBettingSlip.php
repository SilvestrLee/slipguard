<?php

namespace App\Actions\MarketIntelligence;

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Actions\Planner\StartPlannerSession;
use App\Domain\MarketIntelligence\Construction\CandidateLeg;
use App\Domain\MarketIntelligence\Construction\ConstructedCandidate;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Models\PlannerSession;
use App\Models\User;

/**
 * `ADR-013`'s own named handoff action: the *only* point Capability B ever
 * touches Programme U-07. Converts an accepted candidate's legs into the
 * exact attribute array `SaveBettingSlip` already accepts, then hands off
 * to `SaveBettingSlip` and `StartPlannerSession` completely unmodified —
 * zero new Planner domain concepts, exactly as `ADR-013` requires.
 */
class CreateCandidateBettingSlip
{
    public function __construct(
        private readonly SaveBettingSlip $saveBettingSlip = new SaveBettingSlip,
        private readonly StartPlannerSession $startPlannerSession = new StartPlannerSession,
        private readonly FootballMarketTaxonomyV1 $taxonomy = new FootballMarketTaxonomyV1,
    ) {}

    public function execute(User $user, ConstructedCandidate $candidate): PlannerSession
    {
        $legs = array_map(fn (CandidateLeg $leg) => $this->toLegAttributes($leg), $candidate->legs);

        $bettingSlip = $this->saveBettingSlip->execute($user, null, [], $legs);
        $bettingSlip->markReady();

        return $this->startPlannerSession->execute($user, $bettingSlip);
    }

    /**
     * @return array<string, mixed>
     */
    private function toLegAttributes(CandidateLeg $leg): array
    {
        $competitions = config('slipguard-market-intelligence.allowed_competitions');
        $competitionName = $competitions[$leg->fixture->competition_key] ?? $leg->fixture->competition_key;
        $definition = $this->taxonomy->findByFamily($leg->marketFamily);

        return [
            'sport' => 'Football',
            'competition' => $competitionName,
            'event_name' => "{$leg->fixture->home_team} vs {$leg->fixture->away_team}",
            'market_name' => $definition->displayName,
            'selection_name' => $leg->selectionDescription,
            'decimal_odds' => $leg->decimalOdds,
        ];
    }
}
