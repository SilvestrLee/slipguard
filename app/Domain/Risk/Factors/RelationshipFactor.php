<?php

namespace App\Domain\Risk\Factors;

use App\Domain\Risk\Contracts\RiskFactor;
use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Results\FactorResult;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Trace\FactorTrace;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * RF-006 — Relationship/Correlation. Rule Set 2026.1 §11.
 *
 * Defined but inactive in Rule Set 2026.1. Maximum active contribution: 0.
 * Reason: no authoritative event identity exists for manually entered
 * slips, and no sufficiently reliable deterministic relationship-detection
 * method was found that avoids guessing.
 *
 * Still computed for every slip and always appears in the trace with
 * contribution 0 and reason code RelationshipFactorNotEvaluated — visible
 * and explicit, never silently omitted. Do not remove this class or make
 * it return anything other than 0 without a new rule-set version.
 */
final class RelationshipFactor implements RiskFactor
{
    public function code(): string
    {
        return 'RF-006';
    }

    public function maximumContribution(): int
    {
        return 0;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $zero = BigDecimal::zero()->toScale(4, RoundingMode::HalfUp);

        return new FactorResult(
            factorCode: $this->code(),
            baseContribution: $zero,
            cap: $zero,
            adjustedContribution: $zero,
            reasonCodes: [ReasonCode::RelationshipFactorNotEvaluated],
            trace: new FactorTrace(
                rawInputs: [],
                normalizedValues: [],
                notes: ['Inactive in Rule Set 2026.1 — no authoritative event identity exists for manual entry (§11).'],
            ),
        );
    }
}
