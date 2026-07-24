<?php

namespace App\Domain\Risk\Taxonomy;

/**
 * Football market families supported by taxonomy version 1.0.
 *
 * Handicap markets are deliberately not included in v1: alias text alone
 * cannot reliably distinguish Asian from European handicaps, and this
 * taxonomy does not guess. See docs/03-data-science/FOOTBALL_MARKET_TAXONOMY_V1.md.
 */
enum MarketFamily: string
{
    case MatchResult = 'match_result';
    case DoubleChance = 'double_chance';
    case DrawNoBet = 'draw_no_bet';
    case TotalGoals = 'total_goals';
    case BothTeamsToScore = 'both_teams_to_score';
    case TeamTotalGoals = 'team_total_goals';
    case CorrectScore = 'correct_score';
    case HalfTimeResult = 'half_time_result';
    case HalfTimeFullTime = 'half_time_full_time';
}
