<?php

namespace App\Actions\Planner;

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\BettingSlipNotReadyException;
use App\Models\BettingSlip;
use App\Models\PlannerSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ADR-009 §Decision: a session may only be seeded from a Ready,
 * not-yet-Analysed slip (PD-007: the customer always supplies the slip;
 * the Planner never generates candidates itself). Snapshots the slip's
 * current legs into PlannerSelection rows — the source slip is never
 * mutated by this step (PD-008).
 */
class StartPlannerSession
{
    public function __construct(
        private readonly EvaluatePlannerSession $evaluate = new EvaluatePlannerSession,
    ) {}

    public function execute(User $user, BettingSlip $sourceSlip): PlannerSession
    {
        if ($sourceSlip->status !== BettingSlipStatus::Ready) {
            throw new BettingSlipNotReadyException;
        }

        return DB::transaction(function () use ($user, $sourceSlip) {
            $session = PlannerSession::create([
                'user_id' => $user->id,
                'source_betting_slip_id' => $sourceSlip->id,
                'status' => PlannerSessionStatus::Draft,
            ]);

            foreach ($sourceSlip->legs as $leg) {
                $session->selections()->create([
                    'sport' => $leg->sport,
                    'competition' => $leg->competition,
                    'event_name' => $leg->event_name,
                    'market_name' => $leg->market_name,
                    'selection_name' => $leg->selection_name,
                    'decimal_odds' => $leg->decimal_odds,
                    'display_order' => $leg->display_order,
                ]);
            }

            $this->evaluate->execute($session);

            return $session->fresh(['selections', 'regenerationEvents']);
        });
    }
}
