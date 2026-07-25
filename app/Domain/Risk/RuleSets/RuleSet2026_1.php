<?php

namespace App\Domain\Risk\RuleSets;

use App\Domain\Risk\Contracts\RiskFactor;
use App\Domain\Risk\Factors\CombinedOddsFactor;
use App\Domain\Risk\Factors\IndividualOddsFactor;
use App\Domain\Risk\Factors\LegCountFactor;
use App\Domain\Risk\Factors\MarketComplexityFactor;
use App\Domain\Risk\Factors\RelationshipFactor;
use App\Domain\Risk\Factors\RiskConcentrationFactor;

/**
 * Rule Set 2026.1 — docs/03-data-science/RISK_RULE_SET_2026_1.md.
 *
 * A registry, not a calculator: exposes the approved factor list, group
 * caps, and achievable ceiling. Contains no mathematics of its own — every
 * formula lives in its own Factor class or in CalculateStructuralRisk's
 * orchestration. If this class ever needs an `if` that changes a score,
 * that logic belongs in a Factor, not here.
 */
final class RuleSet2026_1
{
    public const VERSION = '2026.1';

    public const INPUT_SCHEMA_VERSION = '1.0';

    /**
     * Group A (Accumulator Scale) — RF-001 + RF-002 <= 40.
     */
    public const GROUP_A_CAP = 40;

    public const GROUP_A_FACTOR_CODES = ['RF-001', 'RF-002'];

    /**
     * Group B (Odds Distribution) — RF-003 + RF-004 <= 28.
     */
    public const GROUP_B_CAP = 28;

    public const GROUP_B_FACTOR_CODES = ['RF-003', 'RF-004'];

    /**
     * Group A cap + Group B cap + RF-005 max + RF-006 max (0) = 78.
     * Proven exactly reachable, not merely nominal — see the rule set's
     * TV-019 (20 legs, one concentrated outlier, every market complex).
     */
    public const ACHIEVABLE_CEILING = 78;

    /**
     * @return array<int, RiskFactor>
     */
    public function factors(): array
    {
        return [
            new LegCountFactor,
            new CombinedOddsFactor,
            new IndividualOddsFactor,
            new RiskConcentrationFactor,
            new MarketComplexityFactor,
            new RelationshipFactor,
        ];
    }
}
