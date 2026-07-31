<?php

namespace App\Domain\Labs;

/**
 * The only two customer interactions SD-002 approves for MVP (docs/00-
 * governance/DECISION_LOG.md) — no voting, no feature-request submission.
 */
enum LabsInterestType: string
{
    case Notify = 'notify';
    case Beta = 'beta';

    public function label(): string
    {
        return match ($this) {
            self::Notify => 'Notify Me',
            self::Beta => 'Join Beta',
        };
    }

    public function activeLabel(): string
    {
        return match ($this) {
            self::Notify => 'Notified',
            self::Beta => 'Joined Beta',
        };
    }
}
