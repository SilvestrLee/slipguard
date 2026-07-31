<?php

namespace App\Actions\Planner;

use App\Domain\Planner\PlannerSelectionLockState;
use App\Models\PlannerSelection;

/**
 * U-07.1 §6 / ADR-009: locking or unlocking a selection never triggers
 * re-evaluation — MSC's mathematics are computed over the whole candidate
 * set regardless of which legs are locked, so nothing downstream of a
 * lock-state change actually changes. (ADR-009's own prose listed
 * lock/unlock alongside remove/replace as re-evaluation triggers; this is
 * a reconciliation of that minor inconsistency in favour of the
 * mathematically correct behaviour — flagged, not silently picked.)
 */
class ToggleSelectionLock
{
    public function execute(PlannerSelection $selection): PlannerSelection
    {
        $selection->lock_state = $selection->isLocked()
            ? PlannerSelectionLockState::Unlocked
            : PlannerSelectionLockState::Locked;

        $selection->save();

        return $selection;
    }
}
