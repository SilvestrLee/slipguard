<?php

namespace App\Actions\Planner;

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\InvalidPlannerSessionTransitionException;
use App\Models\BettingSlip;
use App\Models\PlannerSession;
use Illuminate\Support\Facades\DB;

/**
 * PD-009 / ADR-009 Export Option 2: the source slip is never transitioned
 * out of Ready — a new BettingSlip is created from the session's final
 * selections via the existing SaveBettingSlip action. The original slip
 * remains the customer's initial submission, permanently; the new slip
 * represents the approved Planner outcome. At most once per session
 * (Complete -> Exported is the only legal transition into this state).
 */
class ExportPlannerSession
{
    public function __construct(
        private readonly SaveBettingSlip $saveBettingSlip = new SaveBettingSlip,
    ) {}

    public function execute(PlannerSession $session): BettingSlip
    {
        if (! $session->status->canTransitionTo(PlannerSessionStatus::Exported)) {
            throw new InvalidPlannerSessionTransitionException($session->status, PlannerSessionStatus::Exported);
        }

        return DB::transaction(function () use ($session) {
            $legs = $session->selections()->orderBy('display_order')->get()
                ->map(fn ($selection) => [
                    'sport' => $selection->sport,
                    'competition' => $selection->competition,
                    'event_name' => $selection->event_name,
                    'market_name' => $selection->market_name,
                    'selection_name' => $selection->selection_name,
                    'decimal_odds' => $selection->decimal_odds,
                ])
                ->all();

            $exportedSlip = $this->saveBettingSlip->execute($session->user, null, [], $legs);

            $session->exported_betting_slip_id = $exportedSlip->id;
            $session->save();

            $session->markExported();

            return $exportedSlip;
        });
    }
}
