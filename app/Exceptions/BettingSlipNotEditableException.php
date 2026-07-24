<?php

namespace App\Exceptions;

use RuntimeException;

class BettingSlipNotEditableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This betting slip is locked and can no longer be edited.');
    }
}
