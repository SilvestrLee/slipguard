<?php

namespace App\Exceptions;

use App\Domain\BettingSlip\AnalysisEligibility;
use RuntimeException;

/**
 * Thrown by App\Actions\Analysis\AnalyzeBettingSlip when a slip fails its
 * own AnalysisEligibility check (not ready, empty, incomplete, already
 * analysed, or archived) — carries the specific reasons rather than a
 * generic message, since the caller (a future UI) needs to explain why.
 */
class BettingSlipNotAnalysableException extends RuntimeException
{
    public function __construct(public readonly AnalysisEligibility $eligibility)
    {
        parent::__construct(implode(' ', $eligibility->messages()));
    }
}
