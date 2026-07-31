<?php

namespace App\Domain\Planner\Presentation;

use App\Domain\Planner\PlannerSessionStatus;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;

/**
 * `PO-U07.X.1-001` §8/§9/§12 — a read-only presentation helper, not a new
 * audit/event-sourcing subsystem. Every entry is derived from data the
 * Planner already persists (`PlannerRegenerationEvent`, `PlannerSelection`,
 * `PlannerSession`'s own timestamps) — nothing is invented, and an event
 * this class cannot honestly derive (e.g. a precise "confirmed at" moment
 * once a later write has overwritten `updated_at`) is omitted rather than
 * guessed. Never re-invokes the Risk Engine or mutates anything (ADR-008's
 * discipline applies here just as much as to the engine itself).
 *
 * @see docs/05-ux/U-06/U-06.5-PLANNER-EXPERIENCE-MODERNISATION-REVIEW.md
 */
final class DerivePlannerTimeline
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function derive(PlannerSession $plannerSession): array
    {
        $entries = [
            [
                'type' => 'started',
                'label' => 'Planning session started',
                'detail' => null,
                'occurred_at' => $plannerSession->created_at,
            ],
        ];

        $previous = null;

        foreach ($plannerSession->regenerationEvents as $event) {
            $entries[] = [
                'type' => 'revision',
                'sequence_number' => $event->sequence_number,
                'label' => "Revision {$event->sequence_number} created",
                'detail' => $this->describeDiff($previous, $event, $plannerSession),
                'structural_score' => $event->structural_score,
                'risk_band' => $event->risk_band,
                'occurred_at' => $event->created_at,
            ];

            $previous = $event;
        }

        // Only the current terminal-ish state is derivable — a session
        // confirmed, then edited, then confirmed again leaves no persisted
        // record of the earlier confirmation, so only the latest is shown.
        if ($plannerSession->status === PlannerSessionStatus::Complete) {
            $entries[] = [
                'type' => 'confirmed',
                'label' => 'Selection confirmed',
                'detail' => null,
                'occurred_at' => $plannerSession->updated_at,
            ];
        }

        if ($plannerSession->status === PlannerSessionStatus::Exported) {
            $entries[] = [
                'type' => 'exported',
                'label' => 'Exported as a new betting slip',
                'detail' => null,
                'occurred_at' => $plannerSession->updated_at,
            ];
        }

        return $entries;
    }

    /**
     * @return array{added: array<int, string>, removed: array<int, string>, kept: int}
     */
    private function describeDiff(?PlannerRegenerationEvent $previous, PlannerRegenerationEvent $current, PlannerSession $plannerSession): array
    {
        $currentIds = collect($current->attributions)->pluck('planner_selection_id')->all();

        if (! $previous) {
            return ['added' => [], 'removed' => [], 'kept' => count($currentIds)];
        }

        $previousIds = collect($previous->attributions)->pluck('planner_selection_id')->all();

        $addedIds = array_values(array_diff($currentIds, $previousIds));
        $removedIds = array_values(array_diff($previousIds, $currentIds));

        return [
            'added' => $this->describeSelections($addedIds, $plannerSession),
            'removed' => $this->describeSelections($removedIds, $plannerSession),
            'kept' => count(array_intersect($currentIds, $previousIds)),
        ];
    }

    /**
     * @param  array<int, int>  $selectionIds
     * @return array<int, string>
     */
    private function describeSelections(array $selectionIds, PlannerSession $plannerSession): array
    {
        return collect($selectionIds)
            ->map(function (int $id) use ($plannerSession) {
                $selection = $plannerSession->selections->firstWhere('id', $id);

                return $selection
                    ? "{$selection->event_name} — {$selection->selection_name}"
                    : 'A selection that was later removed';
            })
            ->all();
    }
}
