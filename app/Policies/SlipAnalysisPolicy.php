<?php

namespace App\Policies;

use App\Models\SlipAnalysis;
use App\Models\User;

/**
 * A SlipAnalysis is never created or edited through a user-facing request —
 * only App\Actions\Analysis\AnalyzeBettingSlip produces one, from an
 * already-owned, already-authorized BettingSlip. Only viewing needs a
 * policy here.
 */
class SlipAnalysisPolicy
{
    public function view(User $user, SlipAnalysis $slipAnalysis): bool
    {
        return $user->id === $slipAnalysis->user_id;
    }
}
