<?php

namespace App\Exceptions;

use App\Domain\BettingSlip\BettingSlipStatus;
use RuntimeException;

class InvalidBettingSlipTransitionException extends RuntimeException
{
    public function __construct(BettingSlipStatus $from, BettingSlipStatus $to)
    {
        parent::__construct("Cannot transition a betting slip from [{$from->value}] to [{$to->value}].");
    }
}
