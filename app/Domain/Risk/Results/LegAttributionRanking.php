<?php

namespace App\Domain\Risk\Results;

/**
 * Whole-slip output of RankLegsByStructuralWeakness — the Marginal
 * Structural Contribution (MSC) model, docs/03-data-science/
 * WEAKEST_LEG_ATTRIBUTION_MODEL.md, accepted PO-U06.2A-AC-001.
 *
 * `attributions` is empty exactly when ranking does not apply (specification
 * §2/§3.4): a single-leg slip (nothing to compare against — check
 * `attributions === []` alongside the original leg count), or a baseline
 * that is itself Unavailable (no score to take a marginal difference
 * against — check `baselineAvailability`). No separate reason enum is
 * needed since both underlying facts are already exposed here.
 *
 * When non-empty, `attributions` is ordered: every rankable leg first
 * (weakest to strongest, `rank` 1..n), then every gate-dependent leg
 * (specification §4, `rank` null) in original slip order.
 */
final readonly class LegAttributionRanking
{
    /**
     * @param  array<int, LegAttribution>  $attributions
     */
    public function __construct(
        public AnalysisAvailability $baselineAvailability,
        public ?int $baselineScore,
        public ?RiskBand $baselineBand,
        public array $attributions,
    ) {}

    /**
     * The single weakest leg (rank 1) — specification §3.4's "Weakest Leg."
     * Null when ranking does not apply, or every leg is gate-dependent.
     */
    public function weakestLeg(): ?LegAttribution
    {
        foreach ($this->attributions as $attribution) {
            if (! $attribution->gateDependent) {
                return $attribution;
            }
        }

        return null;
    }

    /**
     * @return array<int, LegAttribution>
     */
    public function gateDependentLegs(): array
    {
        return array_values(array_filter(
            $this->attributions,
            fn (LegAttribution $attribution) => $attribution->gateDependent,
        ));
    }
}
