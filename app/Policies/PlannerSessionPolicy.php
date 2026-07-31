<?php

namespace App\Policies;

use App\Models\PlannerSession;
use App\Models\User;

class PlannerSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlannerSession $plannerSession): bool
    {
        return $user->id === $plannerSession->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlannerSession $plannerSession): bool
    {
        return $user->id === $plannerSession->user_id;
    }
}
