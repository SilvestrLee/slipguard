<?php

namespace App\Domain\Risk\Taxonomy;

use LogicException;

/**
 * The approved football market taxonomy, version 1.0.
 *
 * Aliases below are stored already in normalized form (lowercase, single
 * space, hyphens replaced with spaces) — see NormalizeFootballMarket for the
 * matching normalizer these must agree with.
 *
 * Handicap markets are intentionally not defined here: alias text alone
 * cannot reliably distinguish Asian from European handicaps, so this
 * taxonomy does not guess (see docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md).
 */
final class FootballMarketTaxonomyV1
{
    public const VERSION = '1.0';

    /**
     * @return array<int, MarketDefinition>
     */
    public function definitions(): array
    {
        return [
            new MarketDefinition(
                marketCode: 'football.match_result.1x2',
                family: MarketFamily::MatchResult,
                displayName: 'Match Result',
                aliases: ['match result', 'full time result', '1x2', 'win draw win'],
                complexity: MarketComplexity::Simple,
            ),
            new MarketDefinition(
                marketCode: 'football.match_result.double_chance',
                family: MarketFamily::DoubleChance,
                displayName: 'Double Chance',
                aliases: ['double chance', '1x', 'x2', '12', 'home or draw', 'draw or away', 'home or away'],
                complexity: MarketComplexity::Simple,
            ),
            new MarketDefinition(
                marketCode: 'football.match_result.draw_no_bet',
                family: MarketFamily::DrawNoBet,
                displayName: 'Draw No Bet',
                aliases: ['draw no bet', 'dnb'],
                complexity: MarketComplexity::Simple,
            ),
            new MarketDefinition(
                marketCode: 'football.total_goals.over_under',
                family: MarketFamily::TotalGoals,
                displayName: 'Total Goals',
                aliases: ['over under goals', 'total goals', 'match goals', 'goals over under', 'over 2.5 goals', 'under 2.5 goals'],
                complexity: MarketComplexity::Simple,
            ),
            new MarketDefinition(
                marketCode: 'football.goals.both_teams_to_score',
                family: MarketFamily::BothTeamsToScore,
                displayName: 'Both Teams to Score',
                aliases: ['both teams to score', 'btts', 'gg', 'both teams score'],
                complexity: MarketComplexity::Moderate,
            ),
            new MarketDefinition(
                marketCode: 'football.team_goals.over_under',
                family: MarketFamily::TeamTotalGoals,
                displayName: 'Team Total Goals',
                aliases: ['team total goals', 'home team total goals', 'away team total goals', 'team goals'],
                complexity: MarketComplexity::Moderate,
            ),
            new MarketDefinition(
                marketCode: 'football.score.correct_score',
                family: MarketFamily::CorrectScore,
                displayName: 'Correct Score',
                aliases: ['correct score', 'exact score'],
                complexity: MarketComplexity::Complex,
            ),
            new MarketDefinition(
                marketCode: 'football.half_time.result',
                family: MarketFamily::HalfTimeResult,
                displayName: 'Half-Time Result',
                aliases: ['half time result', '1st half result', 'first half result', 'half time 1x2'],
                complexity: MarketComplexity::Moderate,
            ),
            new MarketDefinition(
                marketCode: 'football.match_result.half_time_full_time',
                family: MarketFamily::HalfTimeFullTime,
                displayName: 'Half-Time / Full-Time',
                aliases: ['half time full time', 'ht ft', 'ht/ft', 'half time/full time'],
                complexity: MarketComplexity::Complex,
            ),
        ];
    }

    public function findByAlias(string $normalizedMarketText): ?MarketDefinition
    {
        foreach ($this->definitions() as $definition) {
            if (in_array($normalizedMarketText, $definition->aliases, true)) {
                return $definition;
            }
        }

        return null;
    }

    public function findByFamily(MarketFamily $family): MarketDefinition
    {
        foreach ($this->definitions() as $definition) {
            if ($definition->family === $family) {
                return $definition;
            }
        }

        throw new LogicException("No market definition registered for family [{$family->value}].");
    }
}
