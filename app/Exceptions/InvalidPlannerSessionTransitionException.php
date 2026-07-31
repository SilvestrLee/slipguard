<?php

namespace App\Exceptions;

use App\Domain\Planner\PlannerSessionStatus;
use RuntimeException;

class InvalidPlannerSessionTransitionException extends RuntimeException
{
    public function __construct(PlannerSessionStatus $from, PlannerSessionStatus $to)
    {
        parent::__construct("Cannot transition a planner session from [{$from->value}] to [{$to->value}].");
    }
}
