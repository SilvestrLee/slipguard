<?php

namespace App\Domain\Risk\Results;

/**
 * Rule Set 2026.1 §14 — validated against a 20-vector distribution review,
 * not retained merely because the thresholds are round.
 */
enum RiskBand: string
{
    case Low = 'low';
    case Moderate = 'moderate';
    case High = 'high';
    case VeryHigh = 'very_high';

    public static function forScore(int $score): self
    {
        return match (true) {
            $score <= 24 => self::Low,
            $score <= 49 => self::Moderate,
            $score <= 74 => self::High,
            default => self::VeryHigh,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Moderate => 'Moderate',
            self::High => 'High',
            self::VeryHigh => 'Very High',
        };
    }
}
