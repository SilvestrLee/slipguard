<?php

namespace App\Domain\MarketIntelligence;

use App\Domain\Risk\Taxonomy\MarketFamily;

/**
 * U-17.5 §6/U-17.6 §7 — maps The Odds API's own confirmed market keys to
 * `FootballMarketTaxonomyV1`'s existing family vocabulary. Never a second
 * taxonomy: this class only translates a provider key to the SAME
 * `MarketFamily` enum `FootballMarketTaxonomyV1`/`NormalizeFootballMarket`
 * already use for customer-entered slips.
 *
 * Every mapping below was confirmed against a real, authenticated request
 * during `U-17.3` — nothing here is guessed. `correct_score`'s real
 * outcome-string shape was never inspected in that pass (only that the
 * key itself returns real, priced data) — callers must treat its
 * `outcomes` defensively, never assume a specific string format.
 */
final class MapOddsApiMarket
{
    /**
     * @return array<string, MarketFamily>
     */
    private function map(): array
    {
        return [
            'h2h' => MarketFamily::MatchResult,
            'h2h_h1' => MarketFamily::HalfTimeResult,
            'totals' => MarketFamily::TotalGoals,
            'double_chance' => MarketFamily::DoubleChance,
            'draw_no_bet' => MarketFamily::DrawNoBet,
            'btts' => MarketFamily::BothTeamsToScore,
            'correct_score' => MarketFamily::CorrectScore,
        ];
    }

    public function toCanonicalFamily(string $providerMarketKey): ?MarketFamily
    {
        return $this->map()[$providerMarketKey] ?? null;
    }

    /**
     * @return array<int, string> The confirmed provider keys this class knows how to map.
     */
    public function supportedProviderKeys(): array
    {
        return array_keys($this->map());
    }
}
