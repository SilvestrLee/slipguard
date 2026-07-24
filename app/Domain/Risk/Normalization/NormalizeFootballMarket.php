<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

/**
 * Deterministic football market normalizer: exact alias lookup first,
 * controlled pattern extraction second. No fuzzy matching, no ML, no AI.
 */
class NormalizeFootballMarket
{
    public function __construct(
        private readonly FootballMarketTaxonomyV1 $taxonomy = new FootballMarketTaxonomyV1,
    ) {}

    public function normalize(string $rawMarket, string $rawSelection): NormalizedMarket
    {
        $normalizedMarket = $this->normalizeMarketText($rawMarket);
        $normalizedSelection = $this->normalizeSelectionText($rawSelection);

        $definition = $this->taxonomy->findByAlias($normalizedMarket);
        $inlineTotalGoals = null;

        if (! $definition) {
            $inlineTotalGoals = $this->matchInlineTotalGoals($normalizedMarket);

            if ($inlineTotalGoals !== null) {
                $definition = $this->taxonomy->findByFamily(MarketFamily::TotalGoals);
            }
        }

        if (! $definition) {
            return new NormalizedMarket(
                marketCode: null,
                marketFamily: null,
                complexity: MarketComplexity::Unknown,
                status: NormalizationStatus::Unrecognized,
                selectionFacts: [],
                rawMarketInput: $rawMarket,
                rawSelectionInput: $rawSelection,
                taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
            );
        }

        [$facts, $status] = match ($definition->family) {
            MarketFamily::TotalGoals => $this->extractTotalGoalsFacts($normalizedMarket, $normalizedSelection, $inlineTotalGoals),
            MarketFamily::BothTeamsToScore => $this->extractBothTeamsToScoreFacts($normalizedSelection),
            MarketFamily::CorrectScore => $this->extractCorrectScoreFacts($normalizedSelection),
            default => [[], NormalizationStatus::Complete],
        };

        return new NormalizedMarket(
            marketCode: $definition->marketCode,
            marketFamily: $definition->family,
            complexity: $definition->complexity,
            status: $status,
            selectionFacts: $facts,
            rawMarketInput: $rawMarket,
            rawSelectionInput: $rawSelection,
            taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
        );
    }

    /**
     * @return array{0: array<string, string|null>, 1: NormalizationStatus}
     */
    private function extractTotalGoalsFacts(string $normalizedMarket, string $normalizedSelection, ?array $inline): array
    {
        if ($inline !== null) {
            return [['direction' => $inline['direction'], 'line' => $inline['line']], NormalizationStatus::Complete];
        }

        // The selection is what the user actually chose — prefer it over the
        // market text, which may itself contain both words (e.g. an alias
        // like "Over Under Goals") and would otherwise always resolve to
        // whichever word appears first, regardless of the real selection.
        $direction = $this->firstMatch('/(over|under)/', $normalizedSelection)
            ?? $this->firstMatch('/(over|under)/', $normalizedMarket);

        $line = $this->firstMatch('/(\d+(?:\.\d+)?)/', $normalizedSelection)
            ?? $this->firstMatch('/(\d+(?:\.\d+)?)/', $normalizedMarket);

        if ($direction !== null && $line !== null) {
            return [['direction' => $direction, 'line' => $line], NormalizationStatus::Complete];
        }

        return [['direction' => $direction, 'line' => null], NormalizationStatus::Partial];
    }

    private function firstMatch(string $pattern, string $subject): ?string
    {
        return preg_match($pattern, $subject, $matches) ? $matches[1] : null;
    }

    /**
     * @return array{0: array<string, string|null>, 1: NormalizationStatus}
     */
    private function extractBothTeamsToScoreFacts(string $normalizedSelection): array
    {
        return match ($normalizedSelection) {
            'yes', 'y' => [['selection_code' => 'yes'], NormalizationStatus::Complete],
            'no', 'n' => [['selection_code' => 'no'], NormalizationStatus::Complete],
            default => [['selection_code' => null], NormalizationStatus::Partial],
        };
    }

    /**
     * @return array{0: array<string, string|null>, 1: NormalizationStatus}
     */
    private function extractCorrectScoreFacts(string $normalizedSelection): array
    {
        if (preg_match('/^(\d{1,2})\s*-\s*(\d{1,2})$/', $normalizedSelection, $matches)) {
            return [['score_home' => $matches[1], 'score_away' => $matches[2]], NormalizationStatus::Complete];
        }

        return [['score_home' => null, 'score_away' => null], NormalizationStatus::Partial];
    }

    /**
     * @return array{direction: string, line: string}|null
     */
    private function matchInlineTotalGoals(string $normalizedMarket): ?array
    {
        if (preg_match('/^(over|under)\s+(\d+(?:\.\d+)?)\s+goals?$/', $normalizedMarket, $matches)) {
            return ['direction' => $matches[1], 'line' => $matches[2]];
        }

        return null;
    }

    private function normalizeMarketText(string $raw): string
    {
        $text = mb_strtolower(trim($raw));
        $text = str_replace('-', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function normalizeSelectionText(string $raw): string
    {
        $text = mb_strtolower(trim($raw));

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
