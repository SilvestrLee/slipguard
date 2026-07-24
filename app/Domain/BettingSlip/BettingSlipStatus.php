<?php

namespace App\Domain\BettingSlip;

enum BettingSlipStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Analysed = 'analysed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Ready => 'Ready',
            self::Analysed => 'Analysed',
            self::Archived => 'Archived',
        };
    }

    /**
     * Only a Draft slip may have its legs added, removed, reordered, or edited.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * The set of statuses this status may transition into. Anything not
     * listed here is an illegal transition and must be rejected.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Ready, self::Archived],
            self::Ready => [self::Draft, self::Analysed, self::Archived],
            self::Analysed => [self::Archived],
            self::Archived => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
