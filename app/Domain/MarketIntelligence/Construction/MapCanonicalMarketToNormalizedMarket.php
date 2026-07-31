<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Domain\Risk\Normalization\NormalizedMarket;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Models\MarketIntelligenceFixture;

/**
 * `U-17.6` §7/§14, `CBE-005` — translates a known `MarketFamily` plus one
 * provider outcome into the exact `NormalizedMarket` shape
 * `NormalizeFootballMarket` already produces for Capability A, using the
 * SAME `FootballMarketTaxonomyV1` vocabulary (never a second taxonomy).
 *
 * Returns `null` when an outcome doesn't map unambiguously — this *is*
 * `CBE-005` (Canonical Mapping Confidence): excluded here, never included
 * with a guessed fact. Only `h2h`/`totals`' real outcome shapes were
 * directly confirmed against live payloads during `U-17.3`; the other
 * families' patterns below are a defensible best-effort reading of The
 * Odds API's documented conventions, not independently trial-confirmed —
 * treated with the same defensive posture regardless: an unrecognized
 * shape is excluded, never guessed at. `correct_score` never reaches this
 * class at all (excluded from construction entirely, `U-17.6` §7).
 */
final class MapCanonicalMarketToNormalizedMarket
{
    public function __construct(
        private readonly FootballMarketTaxonomyV1 $taxonomy = new FootballMarketTaxonomyV1,
    ) {}

    public function map(MarketFamily $family, MarketIntelligenceFixture $fixture, SelectedOutcomeQuote $quote): ?MappedSelection
    {
        return match ($family) {
            MarketFamily::MatchResult => $this->mapMatchResult($family, $fixture, $quote),
            MarketFamily::HalfTimeResult => $this->mapMatchResult($family, $fixture, $quote, halfTime: true),
            MarketFamily::DrawNoBet => $this->mapDrawNoBet($family, $fixture, $quote),
            MarketFamily::DoubleChance => $this->mapDoubleChance($family, $fixture, $quote),
            MarketFamily::TotalGoals => $this->mapTotalGoals($family, $quote),
            MarketFamily::BothTeamsToScore => $this->mapBothTeamsToScore($family, $quote),
            default => null,
        };
    }

    private function mapMatchResult(MarketFamily $family, MarketIntelligenceFixture $fixture, SelectedOutcomeQuote $quote, bool $halfTime = false): ?MappedSelection
    {
        $suffix = $halfTime ? ' at Half-Time' : '';

        $description = match ($quote->outcomeName) {
            $fixture->home_team => "{$fixture->home_team} to Win{$suffix}",
            $fixture->away_team => "{$fixture->away_team} to Win{$suffix}",
            'Draw' => "Draw{$suffix}",
            default => null,
        };

        if ($description === null) {
            return null;
        }

        return $this->buildMappedSelection($family, $description, [], "match.{$quote->outcomeName}");
    }

    private function mapDrawNoBet(MarketFamily $family, MarketIntelligenceFixture $fixture, SelectedOutcomeQuote $quote): ?MappedSelection
    {
        $description = match ($quote->outcomeName) {
            $fixture->home_team => "{$fixture->home_team} (Draw No Bet)",
            $fixture->away_team => "{$fixture->away_team} (Draw No Bet)",
            default => null,
        };

        if ($description === null) {
            return null;
        }

        return $this->buildMappedSelection($family, $description, [], "dnb.{$quote->outcomeName}");
    }

    private function mapDoubleChance(MarketFamily $family, MarketIntelligenceFixture $fixture, SelectedOutcomeQuote $quote): ?MappedSelection
    {
        $name = mb_strtolower($quote->outcomeName);
        $home = mb_strtolower($fixture->home_team);
        $away = mb_strtolower($fixture->away_team);

        $description = match (true) {
            str_contains($name, $home) && str_contains($name, 'draw') => "{$fixture->home_team} or Draw",
            str_contains($name, $away) && str_contains($name, 'draw') => "Draw or {$fixture->away_team}",
            str_contains($name, $home) && str_contains($name, $away) => "{$fixture->home_team} or {$fixture->away_team}",
            default => null,
        };

        if ($description === null) {
            return null;
        }

        return $this->buildMappedSelection($family, $description, [], "double_chance.{$quote->outcomeName}");
    }

    private function mapTotalGoals(MarketFamily $family, SelectedOutcomeQuote $quote): ?MappedSelection
    {
        $direction = match (mb_strtolower($quote->outcomeName)) {
            'over' => 'over',
            'under' => 'under',
            default => null,
        };

        if ($direction === null || $quote->point === null) {
            return null;
        }

        $description = ucfirst($direction)." {$quote->point} Goals";

        return $this->buildMappedSelection($family, $description, ['direction' => $direction, 'line' => $quote->point], "totals.{$quote->outcomeName}.{$quote->point}");
    }

    private function mapBothTeamsToScore(MarketFamily $family, SelectedOutcomeQuote $quote): ?MappedSelection
    {
        $code = match (mb_strtolower($quote->outcomeName)) {
            'yes' => 'yes',
            'no' => 'no',
            default => null,
        };

        if ($code === null) {
            return null;
        }

        $description = 'Both Teams to Score — '.ucfirst($code);

        return $this->buildMappedSelection($family, $description, ['selection_code' => $code], "btts.{$quote->outcomeName}");
    }

    /**
     * @param  array<string, string|null>  $selectionFacts
     */
    private function buildMappedSelection(MarketFamily $family, string $description, array $selectionFacts, string $rawSelectionInput): MappedSelection
    {
        $definition = $this->taxonomy->findByFamily($family);

        $normalizedMarket = new NormalizedMarket(
            marketCode: $definition->marketCode,
            marketFamily: $family,
            complexity: $definition->complexity,
            status: NormalizationStatus::Complete,
            selectionFacts: $selectionFacts,
            rawMarketInput: $definition->displayName,
            rawSelectionInput: $rawSelectionInput,
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
        );

        return new MappedSelection($normalizedMarket, $description);
    }
}
