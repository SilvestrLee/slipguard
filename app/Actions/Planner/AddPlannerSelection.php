<?php

namespace App\Actions\Planner;

use App\Domain\BettingSlip\BettingSlipValidationRules;
use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\InvalidPlannerSessionTransitionException;
use App\Models\PlannerSelection;
use App\Models\PlannerSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * PD-007: the customer sources every candidate themselves, including a
 * replacement — this action never invents or suggests a leg. Reuses the
 * exact same leg validation as the E-03 slip builder
 * (BettingSlipValidationRules), rather than re-deriving it. Triggers
 * re-evaluation (ADR-009): adding a leg changes the candidate set.
 *
 * @param  array<string, mixed>  $attributes
 */
class AddPlannerSelection
{
    public function __construct(
        private readonly EvaluatePlannerSession $evaluate = new EvaluatePlannerSession,
    ) {}

    public function execute(PlannerSession $session, array $attributes): PlannerSelection
    {
        if (! $session->status->canTransitionTo(PlannerSessionStatus::Evaluated)) {
            throw new InvalidPlannerSessionTransitionException($session->status, PlannerSessionStatus::Evaluated);
        }

        Validator::make($attributes, BettingSlipValidationRules::legRules())->validate();

        return DB::transaction(function () use ($session, $attributes) {
            $nextOrder = ($session->selections()->max('display_order') ?? -1) + 1;

            $selection = $session->selections()->create([...$attributes, 'display_order' => $nextOrder]);

            $this->evaluate->execute($session);

            return $selection->fresh();
        });
    }
}
