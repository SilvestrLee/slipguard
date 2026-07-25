<?php

namespace App\Domain\Risk\Factors;

use App\Domain\Risk\Contracts\RiskFactor;
use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Results\FactorResult;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Trace\FactorTrace;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * RF-005 — Market Complexity. Rule Set 2026.1 §10.
 *
 * A leg with `unknown` complexity is excluded from the average entirely,
 * never treated as `complex` — unknown information is not evidence of
 * structural complexity (§10's critical rule). This includes every
 * non-football leg by construction: NormalizeBettingSlip (E-05A, corrected
 * in E-06A) already guarantees a non-football leg always has `unknown`
 * complexity, so this factor never needs to check sport itself — it can
 * simply trust `market->complexity`.
 */
final class MarketComplexityFactor implements RiskFactor
{
    private const POINTS = [
        'simple' => 0,
        'moderate' => 1,
        'complex' => 2,
    ];

    public function code(): string
    {
        return 'RF-005';
    }

    public function maximumContribution(): int
    {
        return 10;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $knownPoints = [];

        foreach ($input->legs as $leg) {
            if ($leg->market->complexity !== MarketComplexity::Unknown) {
                $knownPoints[] = self::POINTS[$leg->market->complexity->value];
            }
        }

        if (count($knownPoints) === 0) {
            $contribution = BigDecimal::zero()->toScale(4, RoundingMode::HalfUp);

            return new FactorResult(
                factorCode: $this->code(),
                baseContribution: $contribution,
                cap: BigDecimal::of($this->maximumContribution()),
                adjustedContribution: $contribution,
                reasonCodes: [],
                trace: new FactorTrace(
                    rawInputs: ['known_complexity_legs' => 0],
                    normalizedValues: [],
                    notes: ['No leg had a recognized market complexity; contributes exactly 0, not an invented default (§10).'],
                ),
            );
        }

        $average = BigDecimal::of(array_sum($knownPoints))
            ->dividedBy(count($knownPoints), 10, RoundingMode::HalfUp);

        $contribution = $average
            ->dividedBy(2, 10, RoundingMode::HalfUp)
            ->multipliedBy($this->maximumContribution())
            ->toScale(4, RoundingMode::HalfUp);

        $reasonCodes = match (true) {
            $average->isGreaterThanOrEqualTo('1.5') => [ReasonCode::MarketComplexityHigh],
            $average->isGreaterThanOrEqualTo('1') => [ReasonCode::MarketComplexityPresent],
            default => [],
        };

        return new FactorResult(
            factorCode: $this->code(),
            baseContribution: $contribution,
            cap: BigDecimal::of($this->maximumContribution()),
            adjustedContribution: $contribution,
            reasonCodes: $reasonCodes,
            trace: new FactorTrace(
                rawInputs: ['known_complexity_legs' => count($knownPoints)],
                normalizedValues: ['average_complexity_points' => (string) $average],
                notes: ['Average of per-leg complexity points (simple=0, moderate=1, complex=2) over recognized legs only (§10).'],
            ),
        );
    }
}
