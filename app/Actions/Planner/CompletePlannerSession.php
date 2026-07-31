<?php

namespace App\Actions\Planner;

use App\Models\PlannerSession;

class CompletePlannerSession
{
    public function execute(PlannerSession $session): void
    {
        $session->markComplete();
    }
}
