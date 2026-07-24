<?php

namespace App\Policies;

use App\Domain\BettingSlip\BettingSlipStatus;
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

    /**
     * Draft and Ready slips may be deleted. Analysed slips must be archived
     * instead — once a SlipAnalysis exists, deleting the slip would either
     * cascade-delete or orphan that historical result. Archived slips are
     * already the "remove from view" action, so there is nothing further
     * to delete through the normal workflow.
     */
    public function delete(User $user, BettingSlip $bettingSlip): bool
    {
        if ($user->id !== $bettingSlip->user_id) {
            return false;
        }

        return in_array($bettingSlip->status, [BettingSlipStatus::Draft, BettingSlipStatus::Ready], true);
    }
}
