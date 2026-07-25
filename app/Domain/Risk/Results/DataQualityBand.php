<?php

namespace App\Domain\Risk\Results;

/**
 * Rule Set 2026.1 §16. Independent of structural risk — never derived from
 * it, never used to adjust it (§12 of the E-06B sprint: "neither determines
 * the other's score").
 */
enum DataQualityBand: string
{
    case Strong = 'strong';
    case Good = 'good';
    case Limited = 'limited';
    case Insufficient = 'insufficient';

    public static function forScore(int $score): self
    {
        return match (true) {
            $score >= 85 => self::Strong,
            $score >= 65 => self::Good,
            $score >= 40 => self::Limited,
            default => self::Insufficient,
        };
    }

    /**
     * Ordinal rank, best to worst — used only to resolve the Analysis
     * Availability Gate's Tier 2 cap (§17): the "worse of two bands" wins.
     */
    private function rank(): int
    {
        return match ($this) {
            self::Strong => 3,
            self::Good => 2,
            self::Limited => 1,
            self::Insufficient => 0,
        };
    }

    public function worseOf(self $other): self
    {
        return $this->rank() <= $other->rank() ? $this : $other;
    }
}
