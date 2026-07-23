<?php

namespace App\Policies;

use App\Models\BettingSlip;
use App\Models\User;

class BettingSlipPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BettingSlip $bettingSlip): bool
    {
        return $user->id === $bettingSlip->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BettingSlip $bettingSlip): bool
    {
        return $user->id === $bettingSlip->user_id;
    }

    public function delete(User $user, BettingSlip $bettingSlip): bool
    {
        return $user->id === $bettingSlip->user_id;
    }
}
