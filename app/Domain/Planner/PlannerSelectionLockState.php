<?php

namespace App\Domain\Planner;

/**
 * A per-selection flag, not a session-level state (U-07.1 §5) — the
 * customer may lock/unlock any candidate leg at any time; locking never
 * triggers re-evaluation, since MSC's mathematics are uniform over the
 * whole candidate set regardless of which legs are locked.
 */
enum PlannerSelectionLockState: string
{
    case Locked = 'locked';
    case Unlocked = 'unlocked';
}
