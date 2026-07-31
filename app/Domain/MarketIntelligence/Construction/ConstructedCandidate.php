<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Engine\CalculateStructuralRisk;
use App\Domain\Risk\Results\LegAttributionRanking;
use App\Domain\Risk\Results\RiskAnalysisResult;
use App\Domain\Risk\RuleSets\RuleSet2026_1;
use Brick\Math\BigDecimal;
use Carbon\CarbonImmutable;

/**
 * U-17.6 §5/§6 — a real, accepted-or-reviewable candidate. Records both
 * rule-set versions separately (this framework's own, and the structural-
 * risk rule set the unmodified engine used) per the deterministic-risk-
 * mathematics discipline's "four independent axes, never conflated" rule.
 */
final readonly class ConstructedCandidate
{
    /**
     * @param  array<int, CandidateLeg>  $legs  The customer's own chosen outcome for each included slot (U-17.6A — never Builder-chosen).
     * @param  array<int, ExcludedCandidateAttempt>  $excludedAttempts  Considered but not included — for the explainability contract (U-17.6 §14).
     */
    public function __construct(
        public array $legs,
        public BigDecimal $combinedOdds,
        public RiskAnalysisResult $riskAnalysis,
        public ?LegAttributionRanking $legAttributionRanking,
        public array $excludedAttempts,
        public int $replacementAttemptsUsed,
        public int $oddsAdjustmentAttemptsUsed,
        public CarbonImmutable $generatedAt,
        public string $constructionRuleSetVersion = CandidateConstructionRuleSet2026_1::VERSION,
        public string $structuralRuleSetVersion = RuleSet2026_1::VERSION,
        public string $structuralEngineVersion = CalculateStructuralRisk::ENGINE_VERSION,
    ) {}
}
