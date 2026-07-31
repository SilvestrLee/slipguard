<?php

namespace App\Domain\Planner;

/**
 * ADR-009 §Decision. Deliberately smaller than an earlier generic sketch
 * (U-06.1) — Capability A has no system-driven candidate-generation step,
 * so there is no async "Generating" state to model, and lock state lives
 * per-selection (PlannerSelection.lock_state), not as a session status.
 */
enum PlannerSessionStatus: string
{
    case Draft = 'draft';
    case Evaluated = 'evaluated';
    case Complete = 'complete';
    case Exported = 'exported';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Evaluated => 'Evaluated',
            self::Complete => 'Complete',
            self::Exported => 'Exported',
            self::Abandoned => 'Abandoned',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Evaluated, self::Abandoned],
            self::Evaluated => [self::Evaluated, self::Complete, self::Abandoned],
            self::Complete => [self::Evaluated, self::Exported, self::Abandoned],
            self::Exported => [],
            self::Abandoned => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * PD-008: a session locks its source slip for as long as it is not in
     * one of these terminal states — used by BettingSlip's own computed
     * lock check (ADR-009's source-slip lock representation), never stored.
     */
    public function isTerminal(): bool
    {
        return $this === self::Exported || $this === self::Abandoned;
    }
}
