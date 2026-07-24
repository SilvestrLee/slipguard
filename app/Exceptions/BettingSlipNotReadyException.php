<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when normalization is attempted on a slip that is not in the
 * Ready state — only a Ready slip has completed domain validation and is
 * locked, which is what makes its input trustworthy to normalize.
 */
class BettingSlipNotReadyException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This betting slip is not Ready and cannot be normalized for analysis.');
    }
}
