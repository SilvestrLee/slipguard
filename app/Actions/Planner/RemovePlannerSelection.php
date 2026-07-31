<?php

namespace App\Actions\Planner;

use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\InvalidPlannerSessionTransitionException;
use App\Models\PlannerSelection;
use Illuminate\Support\Facades\DB;

/**
 * ADR-009: removing a leg changes the candidate set, so it triggers
 * re-evaluation. Deliberately does not forbid removing a locked selection
 * at the domain layer — U-07.1's own interaction model has "lock" mean
 * "don't touch," which is a presentation/interaction concern (UX Studio's
 * domain per ADR-009's Topic B distinction), not a rule this pure action
 * enforces itself.
 */
class RemovePlannerSelection
{
    public function __construct(
        private readonly EvaluatePlannerSession $evaluate = new EvaluatePlannerSession,
    ) {}

    public function execute(PlannerSelection $selection): void
    {
        $session = $selection->plannerSession;

        if (! $session->status->canTransitionTo(PlannerSessionStatus::Evaluated)) {
            throw new InvalidPlannerSessionTransitionException($session->status, PlannerSessionStatus::Evaluated);
        }

        DB::transaction(function () use ($selection, $session) {
            $selection->delete();

            $this->evaluate->execute($session);
        });
    }
}
