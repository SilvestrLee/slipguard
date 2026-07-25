<?php

namespace App\Domain\Risk\Factors;

use App\Domain\Risk\Contracts\RiskFactor;
use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Results\FactorResult;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Support\PiecewiseLinearInterpolation;
use App\Domain\Risk\Trace\FactorTrace;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * RF-002 — Combined Odds Risk. Rule Set 2026.1 §7.
 *
 * Combined odds is a continuous quantity (unlike leg count's small integer
 * domain), so a step table would create the boundary instability §6.6 of
 * the rule set warns against. Piecewise linear interpolation between the
 * approved anchor points avoids that while still needing only +, -, x, /.
 *
 * Reason-code boundaries (§7 lists the three codes without pinning exact
 * numbers) are implemented here using the approved anchor points
 * themselves (2.00, 10.00, 50.00) rather than an invented fraction — the
 * least arbitrary choice available. Flagged for Data Science confirmation;
 * does not affect the score itself.
 */
final class CombinedOddsFactor implements RiskFactor
{
    /**
     * Anchor x-values are strings, never float literals — brick/math's
     * BigDecimal::of() accepts only BigNumber|int|string, and a float here
     * would silently truncate to an int via PHP's implicit coercion (e.g.
     * 1.5 -> 1), corrupting the anchor table with no visible error. This bit
     * a sibling factor once (RELATIVE_ANCHORS in IndividualOddsFactor);
     * fixed there and defended against here too.
     *
     * @var array<int, array{0: string, 1: int}>
     */
    private const ANCHORS = [
        ['1.00', 0], ['2.00', 3], ['4.00', 7], ['7.00', 10], ['10.00', 14], ['20.00', 17], ['50.00', 20],
    ];

    public function code(): string
    {
        return 'RF-002';
    }

    public function maximumContribution(): int
    {
        return 20;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $combined = BigDecimal::one();
        foreach ($input->legs as $leg) {
            $combined = $combined->multipliedBy(BigDecimal::of($leg->decimalOdds));
        }
        $combined = $combined->toScale(4, RoundingMode::HalfUp);

        $contribution = PiecewiseLinearInterpolation::evaluate($combined, self::ANCHORS);

        $reasonCodes = match (true) {
            $combined->isGreaterThanOrEqualTo('50.00') => [ReasonCode::CombinedOddsExtreme],
            $combined->isGreaterThanOrEqualTo('10.00') => [ReasonCode::CombinedOddsHigh],
            $combined->isGreaterThanOrEqualTo('2.00') => [ReasonCode::CombinedOddsModerate],
            default => [],
        };

        return new FactorResult(
            factorCode: $this->code(),
            baseContribution: $contribution,
            cap: BigDecimal::of($this->maximumContribution()),
            adjustedContribution: $contribution,
            reasonCodes: $reasonCodes,
            trace: new FactorTrace(
                rawInputs: [],
                normalizedValues: ['combined_decimal_odds' => (string) $combined],
                notes: ['Piecewise linear interpolation over the approved anchor points (§7).'],
            ),
        );
    }
}
