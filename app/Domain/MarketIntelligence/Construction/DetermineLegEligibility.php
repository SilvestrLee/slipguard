<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Domain\MarketIntelligence\MapOddsApiMarket;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;
use Brick\Math\BigDecimal;
use Carbon\CarbonImmutable;

/**
 * U-17.6 §3 — the eligibility gate catalogue, run in cheap-first order
 * (`CBE-001`-`004` against fixture/market facts alone, before ever
 * inspecting per-outcome quotes) exactly as §3's own instruction requires.
 *
 * `CBE-003` (Fixture Status — postponed/cancelled/completed) is a
 * documented, disclosed limitation: Phase 1's `AcquireMarketEvidence`
 * never captures fixture status at all (confirmed against the real
 * `market_intelligence_fixtures` migration — no such column exists), so
 * this gate cannot yet reject anything. It always passes rather than
 * guessing a status SlipGuard doesn't have — a real gap for a future
 * acquisition-layer enhancement, not invented here.
 */
final class DetermineLegEligibility
{
    public function __construct(
        private readonly SelectConservativeQuote $selectQuote = new SelectConservativeQuote,
        private readonly MapCanonicalMarketToNormalizedMarket $mapMarket = new MapCanonicalMarketToNormalizedMarket,
        private readonly MapOddsApiMarket $marketMapper = new MapOddsApiMarket,
    ) {}

    public function evaluate(PlanningBrief $brief, MarketIntelligenceFixture $fixture, MarketFamily $family): EligibilityEvaluation
    {
        $allowedCompetitions = array_keys(config('slipguard-market-intelligence.allowed_competitions'));

        if (! in_array($fixture->competition_key, $allowedCompetitions, true)
            || ! in_array($fixture->competition_key, $brief->competitions, true)
        ) {
            return $this->wholeSlotExcluded($fixture, $family, CandidateExclusionReason::CompetitionNotEnabled);
        }

        $windowEnd = CarbonImmutable::now()->addDays($brief->windowDays);

        if ($fixture->commence_time->isPast() || $fixture->commence_time->isAfter($windowEnd)) {
            return $this->wholeSlotExcluded($fixture, $family, CandidateExclusionReason::OutsideFixtureWindow);
        }

        // CBE-003: always passes — see class docblock. Not a real check yet.
        if (in_array($fixture->provider_event_id, $brief->excludedFixtureIds, true)) {
            return $this->wholeSlotExcluded($fixture, $family, CandidateExclusionReason::CustomerExcluded);
        }

        if (! $this->familyIsSupportedForConstruction($family)) {
            return $this->wholeSlotExcluded($fixture, $family, CandidateExclusionReason::MarketNotVerified);
        }

        $quotes = $this->selectQuote->forFixtureMarket($fixture, $family->value);

        if ($quotes === []) {
            return $this->wholeSlotExcluded($fixture, $family, CandidateExclusionReason::EvidenceNotFresh);
        }

        $options = [];
        $excluded = [];

        foreach ($quotes as $quote) {
            $fresh = $quote->evidenceQuality === EvidenceQuality::Fresh && $quote->cacheExpiresAt->isFuture();

            if (! $fresh) {
                $excluded[] = new ExcludedCandidateAttempt($fixture, $family, $quote->outcomeName, CandidateExclusionReason::EvidenceNotFresh);

                continue;
            }

            if (! $this->withinOddsBounds($quote->price)) {
                $excluded[] = new ExcludedCandidateAttempt($fixture, $family, $quote->outcomeName, CandidateExclusionReason::OddsOutOfBounds);

                continue;
            }

            $mapped = $this->mapMarket->map($family, $fixture, $quote);

            if ($mapped === null) {
                $excluded[] = new ExcludedCandidateAttempt($fixture, $family, $quote->outcomeName, CandidateExclusionReason::CanonicalMappingUnconfident);

                continue;
            }

            $options[] = new CandidateLeg(
                fixture: $fixture,
                marketFamily: $family,
                normalizedMarket: $mapped->normalizedMarket,
                selectionDescription: $mapped->selectionDescription,
                decimalOdds: $quote->price,
                bookmakerKey: $quote->bookmakerKey,
                evidenceFresh: true,
                retrievedAt: $quote->retrievedAt,
            );
        }

        return new EligibilityEvaluation($options, $excluded);
    }

    private function familyIsSupportedForConstruction(MarketFamily $family): bool
    {
        foreach ($this->marketMapper->supportedProviderKeys() as $key) {
            if ($key === 'correct_score') {
                continue;
            }

            if ($this->marketMapper->toCanonicalFamily($key) === $family) {
                return true;
            }
        }

        return false;
    }

    private function withinOddsBounds(string $price): bool
    {
        $decimal = BigDecimal::of($price);

        return $decimal->isGreaterThanOrEqualTo(BigDecimal::of(CandidateConstructionRuleSet2026_1::ODDS_MIN))
            && $decimal->isLessThanOrEqualTo(BigDecimal::of(CandidateConstructionRuleSet2026_1::ODDS_MAX));
    }

    private function wholeSlotExcluded(MarketIntelligenceFixture $fixture, MarketFamily $family, CandidateExclusionReason $reason): EligibilityEvaluation
    {
        return new EligibilityEvaluation(
            options: [],
            excluded: [new ExcludedCandidateAttempt($fixture, $family, null, $reason)],
        );
    }
}
