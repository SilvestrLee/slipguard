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
 * RF-003 — Individual Odds Elevation and Outlier Risk. Rule Set 2026.1 §8.
 *
 * One factor, two internal sub-components sharing one 20-point cap:
 * absolute elevation (is the slip's highest leg odds itself aggressive,
 * sub-cap 12) and relative outlier (is it disproportionate vs. the slip's
 * own median, sub-cap 8). A single-leg slip cannot be a relative outlier
 * against itself — median == max, so the ratio is exactly 1.00 and the
 * relative component is exactly 0, with no special-case branch needed for
 * the ratio itself (only to avoid a redundant division at n=1).
 *
 * Reason-code boundaries (§8 lists the two codes without pinning exact
 * numbers) are implemented using the approved anchor points themselves
 * (odds 3.00 for elevation, ratio 2.0 for outlier) — the same
 * least-arbitrary-choice approach as CombinedOddsFactor.
 */
final class IndividualOddsFactor implements RiskFactor
{
    /**
     * Anchor x-values are strings, never float literals. A float literal
     * here (e.g. 1.5) is silently coerced to an int by BigDecimal::of(),
     * which only accepts BigNumber|int|string — 1.5 truncates to 1 with no
     * error, colliding with the 1.0 anchor and corrupting the whole table.
     * The exact same defect was caught by a failing test in
     * MarketComplexityFactor; see Rule Set 2026.1's decimal precision
     * policy (no float/double, ever).
     *
     * @var array<int, array{0: string, 1: int}>
     */
    private const ABSOLUTE_ANCHORS = [
        ['1.00', 0], ['2.00', 2], ['3.00', 5], ['5.00', 8], ['8.00', 10], ['15.00', 12],
    ];

    /**
     * §8's own five-point table, exactly as approved. RF-003A (Product
     * Office review, 2026-07-24) investigated why this table appeared not
     * to reproduce §20's canonical TV-003/004/009/021: the reference script
     * that generated those specific vectors carried the identical
     * float-to-BigDecimal-int truncation defect fixed above and in
     * MarketComplexityFactor, which silently collapsed this table's `1.5`
     * breakpoint into `1.0` when *that script* ran — corrupting its output,
     * not this specification. Per Product Office's ruling ("if a script
     * disagrees with the approved mathematics, the Rule Set wins"), §8's
     * formula is authoritative and unchanged; the affected §20 vectors are
     * corrected instead (DECISION_LOG.md, RF-003A).
     *
     * @var array<int, array{0: string, 1: int}>
     */
    private const RELATIVE_ANCHORS = [
        ['1.0', 0], ['1.5', 2], ['2.0', 4], ['3.0', 6], ['5.0', 8],
    ];

    public function code(): string
    {
        return 'RF-003';
    }

    public function maximumContribution(): int
    {
        return 20;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $legOdds = array_map(fn ($leg) => BigDecimal::of($leg->decimalOdds), $input->legs);

        $max = $legOdds[0];
        foreach ($legOdds as $odds) {
            if ($odds->isGreaterThan($max)) {
                $max = $odds;
            }
        }

        $absoluteElevation = PiecewiseLinearInterpolation::evaluate($max, self::ABSOLUTE_ANCHORS);

        $median = $this->deterministicMedian($legOdds);

        if (count($legOdds) === 1) {
            $ratio = BigDecimal::one();
            $relativeOutlier = BigDecimal::zero()->toScale(4, RoundingMode::HalfUp);
        } else {
            $ratio = $max->toScale(10, RoundingMode::HalfUp)->dividedBy($median, 10, RoundingMode::HalfUp);
            $relativeOutlier = PiecewiseLinearInterpolation::evaluate($ratio, self::RELATIVE_ANCHORS);
        }

        $cap = BigDecimal::of($this->maximumContribution());
        $total = $absoluteElevation->plus($relativeOutlier);
        $contribution = $total->isGreaterThan($cap) ? $cap : $total;

        $reasonCodes = [
            ...($max->isGreaterThanOrEqualTo('3.00') ? [ReasonCode::LegOddsElevated] : []),
            ...($ratio->isGreaterThanOrEqualTo('2.0') ? [ReasonCode::LegOddsOutlier] : []),
        ];

        return new FactorResult(
            factorCode: $this->code(),
            baseContribution: $contribution,
            cap: $cap,
            adjustedContribution: $contribution,
            reasonCodes: $reasonCodes,
            trace: new FactorTrace(
                rawInputs: [],
                normalizedValues: [
                    'max_leg_odds' => (string) $max,
                    'median_leg_odds' => (string) $median->toScale(4, RoundingMode::HalfUp),
                    'max_to_median_ratio' => (string) $ratio->toScale(4, RoundingMode::HalfUp),
                    'absolute_elevation' => (string) $absoluteElevation,
                    'relative_outlier' => (string) $relativeOutlier,
                ],
                notes: ['Absolute elevation (sub-cap 12) + relative outlier (sub-cap 8), clamped at 20 (§8).'],
            ),
        );
    }

    /**
     * @param  array<int, BigDecimal>  $legOdds
     */
    private function deterministicMedian(array $legOdds): BigDecimal
    {
        $sorted = $legOdds;
        usort($sorted, fn (BigDecimal $a, BigDecimal $b) => $a->compareTo($b));

        $count = count($sorted);

        if ($count % 2 === 1) {
            return $sorted[intdiv($count, 2)];
        }

        return $sorted[$count / 2 - 1]
            ->plus($sorted[$count / 2])
            ->dividedBy(2, 10, RoundingMode::HalfUp);
    }
}
