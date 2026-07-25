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
 * RF-001 — Leg Count Risk. Rule Set 2026.1 §6.
 *
 * A fixed threshold table, not a formula — chosen because a logarithmic
 * curve (the rejected alternative) cannot be computed with brick/math's
 * BigDecimal (no ln/log operation exists on it). Monotonic by construction:
 * every table value is >= the previous.
 */
final class LegCountFactor implements RiskFactor
{
    /** @var array<int, int> */
    private const CONTRIBUTION_TABLE = [
        1 => 0, 2 => 2, 3 => 5, 4 => 8, 5 => 11, 6 => 13, 7 => 15, 8 => 17,
        9 => 18, 10 => 19, 11 => 20, 12 => 21, 13 => 21, 14 => 22, 15 => 22,
        16 => 23, 17 => 23, 18 => 24, 19 => 24, 20 => 25,
    ];

    public function code(): string
    {
        return 'RF-001';
    }

    public function maximumContribution(): int
    {
        return 25;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $legCount = count($input->legs);

        $contribution = BigDecimal::of(self::CONTRIBUTION_TABLE[$legCount] ?? 25)
            ->toScale(4, RoundingMode::HalfUp);

        // Exact leg-count boundaries per Rule Set 2026.1 §6.
        $reasonCodes = match (true) {
            $legCount >= 13 => [ReasonCode::LegCountExtreme],
            $legCount >= 7 => [ReasonCode::LegCountHigh],
            $legCount >= 3 => [ReasonCode::LegCountModerate],
            default => [],
        };

        return new FactorResult(
            factorCode: $this->code(),
            baseContribution: $contribution,
            cap: BigDecimal::of($this->maximumContribution()),
            adjustedContribution: $contribution,
            reasonCodes: $reasonCodes,
            trace: new FactorTrace(
                rawInputs: ['leg_count' => $legCount],
                normalizedValues: [],
                notes: ["Threshold table lookup for {$legCount} legs."],
            ),
        );
    }
}
