<?php

namespace App\Domain\Risk\Results;

/**
 * The complete, approved reason-code catalogue for Rule Set 2026.1
 * (docs/03-data-science/RISK_RULE_SET_2026_1.md §18). No code may be added,
 * removed, or renamed here without a rule-set version change.
 */
enum ReasonCode: string
{
    case LegCountModerate = 'LEG_COUNT_MODERATE';
    case LegCountHigh = 'LEG_COUNT_HIGH';
    case LegCountExtreme = 'LEG_COUNT_EXTREME';

    case CombinedOddsModerate = 'COMBINED_ODDS_MODERATE';
    case CombinedOddsHigh = 'COMBINED_ODDS_HIGH';
    case CombinedOddsExtreme = 'COMBINED_ODDS_EXTREME';

    case LegOddsElevated = 'LEG_ODDS_ELEVATED';
    case LegOddsOutlier = 'LEG_ODDS_OUTLIER';

    case RiskConcentrated = 'RISK_CONCENTRATED';
    case RiskHighlyConcentrated = 'RISK_HIGHLY_CONCENTRATED';

    case MarketComplexityPresent = 'MARKET_COMPLEXITY_PRESENT';
    case MarketComplexityHigh = 'MARKET_COMPLEXITY_HIGH';

    case RelationshipFactorNotEvaluated = 'RELATIONSHIP_FACTOR_NOT_EVALUATED';

    case MarketUnrecognized = 'MARKET_UNRECOGNIZED';
    case SportUnsupported = 'SPORT_UNSUPPORTED';
    case NormalizationPartial = 'NORMALIZATION_PARTIAL';

    case AnalysisLimited = 'ANALYSIS_LIMITED';
    case AnalysisUnavailable = 'ANALYSIS_UNAVAILABLE';
}
