<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * PD-008 / ADR-009's source-slip lock: thrown when an edit is attempted on
 * a slip that a non-terminal PlannerSession is using as its source.
 */
class BettingSlipLockedByPlannerException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This betting slip is being used by an in-progress planning session and cannot be edited until that session ends.');
    }
}
