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
 * RF-004 — Risk Concentration. Rule Set 2026.1 §9.
 *
 * A Herfindahl-style concentration index over LINEAR (odds - 1) shares —
 * deliberately not log-odds shares, despite odds multiplying rather than
 * adding: brick/math's BigDecimal has no ln/log operation, so a log-odds
 * formula would require binary floating point, which the rule set's
 * decimal policy and ADR-002 both forbid.
 *
 * A single-leg slip contributes exactly 0 — concentration has no meaning
 * for one leg (nothing to be concentrated relative to), and the
 * normalization denominator (1 - 1/n) would otherwise divide by zero at
 * n=1. This is a required special case, not an oversight, and also avoids
 * double-counting a single aggressive leg (already fully captured by
 * IndividualOddsFactor's absolute elevation).
 */
final class RiskConcentrationFactor implements RiskFactor
{
    public function code(): string
    {
        return 'RF-004';
    }

    public function maximumContribution(): int
    {
        return 15;
    }

    public function calculate(NormalizedBettingSlip $input): FactorResult
    {
        $legCount = count($input->legs);

        if ($legCount === 1) {
            return new FactorResult(
                factorCode: $this->code(),
                baseContribution: BigDecimal::zero()->toScale(4, RoundingMode::HalfUp),
                cap: BigDecimal::of($this->maximumContribution()),
                adjustedContribution: BigDecimal::zero()->toScale(4, RoundingMode::HalfUp),
                reasonCodes: [],
                trace: new FactorTrace(
                    rawInputs: ['leg_count' => 1],
                    normalizedValues: [],
                    notes: ['Single-leg slip: concentration is not meaningful for one leg (§9); contributes exactly 0.'],
                ),
            );
        }

        $weights = array_map(fn ($leg) => BigDecimal::of($leg->decimalOdds)->minus(1), $input->legs);

        $totalWeight = BigDecimal::zero();
        foreach ($weights as $weight) {
            $totalWeight = $totalWeight->plus($weight);
        }

        $herfindahlIndex = BigDecimal::zero();
        foreach ($weights as $weight) {
            $share = $weight->toScale(12, RoundingMode::HalfUp)->dividedBy($totalWeight, 12, RoundingMode::HalfUp);
            $herfindahlIndex = $herfindahlIndex->plus($share->power(2));
        }
        $herfindahlIndex = $herfindahlIndex->toScale(8, RoundingMode::HalfUp);

        $balancedBaseline = BigDecimal::one()->dividedBy($legCount, 12, RoundingMode::HalfUp);

        $normalizedConcentration = $herfindahlIndex->minus($balancedBaseline)
            ->dividedBy(BigDecimal::one()->minus($balancedBaseline), 12, RoundingMode::HalfUp);

        if ($normalizedConcentration->isNegative()) {
            $normalizedConcentration = BigDecimal::zero();
        }

        $contribution = $normalizedConcentration->multipliedBy($this->maximumContribution())
            ->toScale(4, RoundingMode::HalfUp);

        // Exact thresholds per Rule Set 2026.1 §9.
        $reasonCodes = match (true) {
            $normalizedConcentration->isGreaterThanOrEqualTo('0.7') => [ReasonCode::RiskHighlyConcentrated],
            $normalizedConcentration->isGreaterThanOrEqualTo('0.4') => [ReasonCode::RiskConcentrated],
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
                normalizedValues: [
                    'herfindahl_index' => (string) $herfindahlIndex,
                    'balanced_baseline' => (string) $balancedBaseline->toScale(8, RoundingMode::HalfUp),
                    'normalized_concentration' => (string) $normalizedConcentration->toScale(6, RoundingMode::HalfUp),
                ],
                notes: ['Herfindahl index over linear (odds - 1) shares, normalized against the balanced baseline for this leg count (§9).'],
            ),
        );
    }
}
