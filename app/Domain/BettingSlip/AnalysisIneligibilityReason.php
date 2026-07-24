<?php

namespace App\Domain\BettingSlip;

enum AnalysisIneligibilityReason: string
{
    case NotReady = 'not_ready';
    case Empty = 'empty';
    case Incomplete = 'incomplete';
    case AlreadyAnalysed = 'already_analysed';
    case Archived = 'archived';

    public function message(): string
    {
        return match ($this) {
            self::NotReady => 'This slip must be marked Ready before it can be analysed.',
            self::Empty => 'This slip has no legs yet.',
            self::Incomplete => 'Every leg needs a sport, event, market, selection, and valid odds before analysis.',
            self::AlreadyAnalysed => 'This slip has already been analysed.',
            self::Archived => 'This slip is archived and cannot be analysed.',
        };
    }
}
