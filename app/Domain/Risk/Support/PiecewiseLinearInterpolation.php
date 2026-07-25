<?php

namespace App\Domain\Risk\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use LogicException;

/**
 * Shared by RF-002 (combined odds) and RF-003 (individual odds elevation +
 * outlier) — used in three places, per this repo's domain-modelling
 * convention of centralizing a formula once it appears in more than one.
 *
 * Deliberately not a logarithm-based curve: Rule Set 2026.1 rejected every
 * logarithmic candidate formula because Brick\Math\BigDecimal has no ln/log
 * operation (confirmed by direct reflection during design). This uses only
 * +, -, ×, ÷ with an explicit scale — fully brick/math-safe.
 */
final class PiecewiseLinearInterpolation
{
    /**
     * @param  array<int, array{0: string|int|float, 1: string|int|float}>  $anchors  Ascending by anchor point.
     */
    public static function evaluate(BigDecimal $x, array $anchors): BigDecimal
    {
        $count = count($anchors);
        $first = BigDecimal::of($anchors[0][0]);
        $last = BigDecimal::of($anchors[$count - 1][0]);

        if ($x->isLessThanOrEqualTo($first)) {
            return BigDecimal::of($anchors[0][1])->toScale(4, RoundingMode::HalfUp);
        }

        if ($x->isGreaterThanOrEqualTo($last)) {
            return BigDecimal::of($anchors[$count - 1][1])->toScale(4, RoundingMode::HalfUp);
        }

        for ($i = 0; $i < $count - 1; $i++) {
            [$lowerAnchor, $lowerContribution] = $anchors[$i];
            [$upperAnchor, $upperContribution] = $anchors[$i + 1];
            $lowerAnchorValue = BigDecimal::of($lowerAnchor);
            $upperAnchorValue = BigDecimal::of($upperAnchor);

            if ($x->isGreaterThanOrEqualTo($lowerAnchorValue) && $x->isLessThanOrEqualTo($upperAnchorValue)) {
                $fraction = $x->minus($lowerAnchorValue)
                    ->toScale(10, RoundingMode::HalfUp)
                    ->dividedBy($upperAnchorValue->minus($lowerAnchorValue), 10, RoundingMode::HalfUp);

                $delta = BigDecimal::of($upperContribution)->minus(BigDecimal::of($lowerContribution));

                return BigDecimal::of($lowerContribution)
                    ->plus($delta->multipliedBy($fraction))
                    ->toScale(4, RoundingMode::HalfUp);
            }
        }

        throw new LogicException('Unreachable: anchors must be sorted ascending and must cover the given value.');
    }
}
