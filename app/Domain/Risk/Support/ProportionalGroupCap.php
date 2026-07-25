<?php

namespace App\Domain\Risk\Support;

use App\Domain\Risk\Results\FactorResult;
use App\Domain\Risk\Results\InteractionAdjustment;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * Rule Set 2026.1 §12 — the one approved interaction-adjustment method
 * (proportional scaling), applied identically to Group A and Group B.
 * Centralized here rather than duplicated per group, since the algorithm
 * itself does not vary — only which factors and which cap are passed in.
 */
final class ProportionalGroupCap
{
    /**
     * @param  array<string, FactorResult>  $factorResults  Keyed by factor code.
     * @param  array<int, string>  $factorCodes  The two factor codes in this group.
     * @return array{0: array<string, FactorResult>, 1: InteractionAdjustment}
     */
    public static function apply(array $factorResults, array $factorCodes, int $cap, string $groupName): array
    {
        $capValue = BigDecimal::of($cap);

        $total = BigDecimal::zero();
        foreach ($factorCodes as $code) {
            $total = $total->plus($factorResults[$code]->adjustedContribution);
        }

        if ($total->isLessThanOrEqualTo($capValue)) {
            $postCap = [];
            foreach ($factorCodes as $code) {
                $postCap[$code] = $factorResults[$code]->adjustedContribution;
            }

            return [$factorResults, new InteractionAdjustment(
                groupName: $groupName,
                factorCodes: $factorCodes,
                preCapTotal: $total,
                cap: $capValue,
                wasCapped: false,
                adjustmentRatio: BigDecimal::one(),
                postCapContributions: $postCap,
            )];
        }

        $ratio = $capValue->toScale(10, RoundingMode::HalfUp)->dividedBy($total, 10, RoundingMode::HalfUp);
        $postCap = [];

        foreach ($factorCodes as $code) {
            $adjusted = $factorResults[$code]->adjustedContribution
                ->multipliedBy($ratio)
                ->toScale(4, RoundingMode::HalfUp);

            $factorResults[$code] = $factorResults[$code]->withAdjustedContribution($adjusted);
            $postCap[$code] = $adjusted;
        }

        return [$factorResults, new InteractionAdjustment(
            groupName: $groupName,
            factorCodes: $factorCodes,
            preCapTotal: $total,
            cap: $capValue,
            wasCapped: true,
            adjustmentRatio: $ratio,
            postCapContributions: $postCap,
        )];
    }
}
