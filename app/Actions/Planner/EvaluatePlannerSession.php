<?php

namespace App\Actions\Planner;

use App\Domain\Risk\Engine\RankLegsByStructuralWeakness;
use App\Domain\Risk\Normalization\NormalizePlannerSelections;
use App\Domain\Risk\Results\LegAttribution;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;

/**
 * ADR-009's required call flow: normalize the session's current
 * PlannerSelection set, run the unmodified MSC engine (read-only), persist
 * one new immutable PlannerRegenerationEvent, transition the session to
 * Evaluated. Never invoked by a presentation surface directly — only by
 * the mutating Planner actions (start, lock/unlock, remove, replace),
 * mirroring ADR-007/ADR-008's forbidden-call-flow discipline exactly.
 */
class EvaluatePlannerSession
{
    public function __construct(
        private readonly NormalizePlannerSelections $normalize = new NormalizePlannerSelections,
        private readonly RankLegsByStructuralWeakness $rankLegs = new RankLegsByStructuralWeakness,
    ) {}

    public function execute(PlannerSession $session): PlannerRegenerationEvent
    {
        $selections = $session->selections()->get();

        $normalized = $this->normalize->execute($session->id, $selections);

        $ranking = $this->rankLegs->calculate($normalized);

        $event = $session->regenerationEvents()->create([
            'sequence_number' => ($session->regenerationEvents()->max('sequence_number') ?? 0) + 1,
            'availability' => $ranking->baselineAvailability,
            'structural_score' => $ranking->baselineScore,
            'risk_band' => $ranking->baselineBand,
            'attributions' => array_map($this->attributionToArray(...), $ranking->attributions),
        ]);

        $session->markEvaluated();

        return $event;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributionToArray(LegAttribution $attribution): array
    {
        return [
            'planner_selection_id' => $attribution->bettingSlipLegId,
            'display_order' => $attribution->displayOrder,
            'decimal_odds' => $attribution->decimalOdds,
            'market_complexity' => $attribution->marketComplexity->value,
            'gate_dependent' => $attribution->gateDependent,
            'rank' => $attribution->rank,
            'msc' => $attribution->msc,
            'msc_precise' => $attribution->mscPrecise?->__toString(),
            'score_without_leg' => $attribution->scoreWithoutLeg,
            'band_without_leg' => $attribution->bandWithoutLeg?->value,
            'availability_without_leg' => $attribution->availabilityWithoutLeg->value,
            'reason_codes_gained_without_leg' => array_map(fn ($code) => $code->value, $attribution->reasonCodesGainedWithoutLeg),
            'reason_codes_lost_without_leg' => array_map(fn ($code) => $code->value, $attribution->reasonCodesLostWithoutLeg),
        ];
    }
}
