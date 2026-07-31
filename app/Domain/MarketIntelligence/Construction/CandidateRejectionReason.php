<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * U-17.6 §15 — the no-valid-candidate reason register. Every reason maps to
 * a specific, real gate elsewhere in this namespace — none is a catch-all
 * "something went wrong."
 */
enum CandidateRejectionReason: string
{
    case InsufficientFixtures = 'CBR-001';
    case TargetOddsUnreachable = 'CBR-002';
    case InsufficientEligibleLegs = 'CBR-003';
    case RiskCeilingUnreachable = 'CBR-004';
    case MarketsUnavailable = 'CBR-005';

    public function label(): string
    {
        return match ($this) {
            self::InsufficientFixtures => 'Not enough supported fixtures were found within your planning window and competitions.',
            self::TargetOddsUnreachable => 'The target odds could not be reached without exceeding your risk limit.',
            self::InsufficientEligibleLegs => 'Not enough eligible selections remained after checking evidence freshness and supported markets.',
            self::RiskCeilingUnreachable => 'No combination of eligible selections stayed within your risk limit.',
            self::MarketsUnavailable => 'The requested markets are not available for the selected competitions and window.',
        };
    }
}
