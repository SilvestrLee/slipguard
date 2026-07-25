<?php

namespace App\Domain\Risk\Contracts;

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Results\FactorResult;

interface RiskFactor
{
    /**
     * The factor's stable identifier, e.g. "RF-001", matching
     * docs/03-data-science/RISK_RULE_SET_2026_1.md exactly.
     */
    public function code(): string;

    /**
     * The factor's approved maximum contribution (its own cap, before any
     * cross-factor group cap is applied).
     */
    public function maximumContribution(): int;

    public function calculate(NormalizedBettingSlip $input): FactorResult;
}
