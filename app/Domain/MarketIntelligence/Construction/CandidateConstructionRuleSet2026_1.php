<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Results\RiskBand;

/**
 * Candidate Construction Rule Set 2026.1 — U-17.6, a registry of the real,
 * disclosed-as-arbitrary MVP bounds, not a calculator (mirrors
 * App\Domain\Risk\RuleSets\RuleSet2026_1's own pattern exactly). A fourth,
 * independent version axis alongside the structural-risk rule set, the
 * engine version, and the market-taxonomy version — never conflated with
 * App\Domain\Risk\RuleSets\RuleSet2026_1, a different document entirely.
 */
final class CandidateConstructionRuleSet2026_1
{
    public const VERSION = '2026.1';

    /** U-17.6 §12 — deliberately smaller than the Builder's own 20-leg maximum. */
    public const LEG_COUNT_MIN = 2;

    public const LEG_COUNT_MAX = 8;

    public const LEG_COUNT_DEFAULT = 5;

    /** U-17.6 §9 — excludes near-certain legs and extreme long-shots at construction time. */
    public const ODDS_MIN = '1.10';

    public const ODDS_MAX = '15.00';

    /** U-17.6 §6 — bounded, not exhaustive search. */
    public const MAX_RISK_REPLACEMENT_ATTEMPTS = 3;

    /** U-17.6 §11 — one deterministic adjustment, never an unbounded search. */
    public const MAX_ODDS_ADJUSTMENT_ATTEMPTS = 1;

    /** U-17.6 §2 — never silently "no ceiling"; an omitted brief field defaults here. */
    public const DEFAULT_RISK_CEILING = RiskBand::High;

    /**
     * The real score thresholds RiskBand::forScore() already uses (24/49/74)
     * — reused here for ceiling-vs-score comparison rather than inventing a
     * second boundary set for the same three numbers.
     */
    public static function ceilingScoreLimit(RiskBand $ceiling): int
    {
        return match ($ceiling) {
            RiskBand::Low => 24,
            RiskBand::Moderate => 49,
            RiskBand::High => 74,
            RiskBand::VeryHigh => 100,
        };
    }
}
