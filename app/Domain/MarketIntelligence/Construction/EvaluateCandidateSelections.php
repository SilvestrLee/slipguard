<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Engine\CalculateStructuralRisk;
use App\Domain\Risk\Engine\RankLegsByStructuralWeakness;
use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Normalization\NormalizedBettingSlipLeg;
use App\Domain\Risk\Normalization\NormalizedSport;
use App\Domain\Risk\Normalization\NormalizeSport;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use Brick\Math\BigDecimal;
use Carbon\CarbonImmutable;

/**
 * `U-17.6` §6/§11, resequenced by `U-17.6A`: this is the point whole-slip
 * structural risk and the target-odds check run, now that the customer's
 * own outcome choices are known (never before — combined odds and every
 * risk factor are odds-dependent, and odds vary by outcome).
 *
 * A ceiling breach or a missed odds target cannot be silently repaired by
 * the Builder swapping in a replacement slot's outcome, since that
 * replacement slot is itself a market the customer hasn't chosen an
 * outcome for yet (`U-17.6A` §4/§7) — both cases return `ReplacementNeeded`
 * for exactly one more customer decision, never re-opening a slot the
 * customer already chose.
 */
final class EvaluateCandidateSelections
{
    public function __construct(
        private readonly CalculateStructuralRisk $calculateStructuralRisk = new CalculateStructuralRisk,
        private readonly RankLegsByStructuralWeakness $rankByWeakness = new RankLegsByStructuralWeakness,
    ) {}

    /**
     * @param  array<int, ChosenSlot>  $chosenSlots  The customer's own choices so far.
     * @param  array<int, CandidateSlot>  $spareSlots  Ranked, eligible, not-yet-included slots — replacement candidates.
     */
    public function execute(
        array $chosenSlots,
        array $spareSlots,
        PlanningBrief $brief,
        int $riskAttemptsUsed = 0,
        int $oddsAttemptsUsed = 0,
    ): ConstructedCandidate|NoValidCandidate|ReplacementNeeded {
        // Re-indexed once, up front — bettingSlipLegId below is assigned as
        // 0..n against this exact order, so every later lookup by that ID
        // must use the same reindexed array, never the caller's own keys.
        $chosenSlots = array_values($chosenSlots);
        $normalizedSlip = $this->toNormalizedBettingSlip($chosenSlots);
        $riskResult = $this->calculateStructuralRisk->calculate($normalizedSlip);

        if ($riskResult->availability === AnalysisAvailability::Unavailable) {
            return new NoValidCandidate(
                CandidateRejectionReason::InsufficientEligibleLegs,
                CandidateRejectionReason::InsufficientEligibleLegs->label(),
            );
        }

        $ceilingLimit = CandidateConstructionRuleSet2026_1::ceilingScoreLimit($brief->riskCeiling);

        if ($riskResult->structuralScore > $ceilingLimit) {
            if ($riskAttemptsUsed >= CandidateConstructionRuleSet2026_1::MAX_RISK_REPLACEMENT_ATTEMPTS) {
                return new NoValidCandidate(
                    CandidateRejectionReason::RiskCeilingUnreachable,
                    CandidateRejectionReason::RiskCeilingUnreachable->label(),
                );
            }

            return $this->replaceWeakestContributor($chosenSlots, $spareSlots, $normalizedSlip, $riskAttemptsUsed)
                ?? new NoValidCandidate(
                    CandidateRejectionReason::RiskCeilingUnreachable,
                    CandidateRejectionReason::RiskCeilingUnreachable->label(),
                );
        }

        $combinedOdds = $this->combinedOdds($chosenSlots);

        if ($brief->hasTargetOdds() && ! $this->withinTargetRange($combinedOdds, $brief)) {
            if ($oddsAttemptsUsed >= CandidateConstructionRuleSet2026_1::MAX_ODDS_ADJUSTMENT_ATTEMPTS) {
                return new NoValidCandidate(
                    CandidateRejectionReason::TargetOddsUnreachable,
                    CandidateRejectionReason::TargetOddsUnreachable->label(),
                );
            }

            return $this->replaceLowestRankedSlot($chosenSlots, $spareSlots, $oddsAttemptsUsed)
                ?? new NoValidCandidate(
                    CandidateRejectionReason::TargetOddsUnreachable,
                    CandidateRejectionReason::TargetOddsUnreachable->label(),
                );
        }

        $attribution = $this->rankByWeakness->calculate($normalizedSlip);

        return new ConstructedCandidate(
            legs: array_map(fn (ChosenSlot $c) => $c->chosenLeg, $chosenSlots),
            combinedOdds: $combinedOdds,
            riskAnalysis: $riskResult,
            legAttributionRanking: $attribution,
            excludedAttempts: [],
            replacementAttemptsUsed: $riskAttemptsUsed,
            oddsAdjustmentAttemptsUsed: $oddsAttemptsUsed,
            generatedAt: CarbonImmutable::now(),
        );
    }

    /**
     * @param  array<int, ChosenSlot>  $chosenSlots
     * @param  array<int, CandidateSlot>  $spareSlots
     */
    private function replaceWeakestContributor(array $chosenSlots, array $spareSlots, NormalizedBettingSlip $normalizedSlip, int $riskAttemptsUsed): ?ReplacementNeeded
    {
        $attribution = $this->rankByWeakness->calculate($normalizedSlip);
        $weakest = $attribution->weakestLeg();

        if ($weakest === null) {
            return null;
        }

        $removedFixtureKey = $chosenSlots[$weakest->bettingSlipLegId]->fixtureKey();

        $usedFixtures = array_map(fn (ChosenSlot $c) => $c->fixtureKey(), $chosenSlots);
        $usedFixtures = array_diff($usedFixtures, [$removedFixtureKey]);

        $replacement = $this->firstUnusedSpare($spareSlots, $usedFixtures);

        if ($replacement === null) {
            return null;
        }

        return new ReplacementNeeded($replacement, $removedFixtureKey, $riskAttemptsUsed + 1, CandidateRejectionReason::RiskCeilingUnreachable);
    }

    /**
     * @param  array<int, ChosenSlot>  $chosenSlots
     * @param  array<int, CandidateSlot>  $spareSlots
     */
    private function replaceLowestRankedSlot(array $chosenSlots, array $spareSlots, int $oddsAttemptsUsed): ?ReplacementNeeded
    {
        $lowestRanked = null;

        foreach ($chosenSlots as $chosen) {
            if ($lowestRanked === null || $chosen->slot->rank > $lowestRanked->slot->rank) {
                $lowestRanked = $chosen;
            }
        }

        if ($lowestRanked === null) {
            return null;
        }

        $usedFixtures = array_map(fn (ChosenSlot $c) => $c->fixtureKey(), $chosenSlots);
        $usedFixtures = array_diff($usedFixtures, [$lowestRanked->fixtureKey()]);

        $replacement = $this->firstUnusedSpare($spareSlots, $usedFixtures);

        if ($replacement === null) {
            return null;
        }

        return new ReplacementNeeded($replacement, $lowestRanked->fixtureKey(), $oddsAttemptsUsed + 1, CandidateRejectionReason::TargetOddsUnreachable);
    }

    /**
     * @param  array<int, CandidateSlot>  $spareSlots
     * @param  array<int, string>  $usedFixtures
     */
    private function firstUnusedSpare(array $spareSlots, array $usedFixtures): ?CandidateSlot
    {
        foreach ($spareSlots as $spare) {
            if (! in_array($spare->fixtureKey(), $usedFixtures, true)) {
                return $spare;
            }
        }

        return null;
    }

    /**
     * @param  array<int, ChosenSlot>  $chosenSlots
     */
    private function toNormalizedBettingSlip(array $chosenSlots): NormalizedBettingSlip
    {
        $legs = [];

        foreach (array_values($chosenSlots) as $index => $chosen) {
            $legs[] = new NormalizedBettingSlipLeg(
                bettingSlipLegId: $index,
                displayOrder: $index,
                sport: new NormalizedSport(NormalizeSport::FOOTBALL_CODE, NormalizationStatus::Complete, 'football'),
                market: $chosen->chosenLeg->normalizedMarket,
                decimalOdds: $chosen->chosenLeg->decimalOdds,
            );
        }

        return new NormalizedBettingSlip(
            bettingSlipId: 0,
            taxonomyVersion: $legs === [] ? FootballMarketTaxonomyV1::VERSION : $legs[0]->market->taxonomyVersion,
            legs: $legs,
        );
    }

    /**
     * @param  array<int, ChosenSlot>  $chosenSlots
     */
    private function combinedOdds(array $chosenSlots): BigDecimal
    {
        $product = BigDecimal::one();

        foreach ($chosenSlots as $chosen) {
            $product = $product->multipliedBy(BigDecimal::of($chosen->chosenLeg->decimalOdds));
        }

        return $product;
    }

    private function withinTargetRange(BigDecimal $combinedOdds, PlanningBrief $brief): bool
    {
        return $combinedOdds->isGreaterThanOrEqualTo(BigDecimal::of($brief->targetOddsMin))
            && $combinedOdds->isLessThanOrEqualTo(BigDecimal::of($brief->targetOddsMax));
    }
}
