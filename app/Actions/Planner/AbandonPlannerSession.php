<?php

namespace App\Actions\Planner;

use App\Models\PlannerSession;

/**
 * PD-008: abandoning a session never touches the source slip — it was
 * never mutated in the first place, so there is nothing to undo.
 */
class AbandonPlannerSession
{
    public function execute(PlannerSession $session): void
    {
        $session->abandon();
    }
}
