<?php

namespace App\Domain\Journal;

enum JournalEntryCategory: string
{
    case SlipConstruction = 'slip_construction';
    case TeamOrFixtureContext = 'team_or_fixture_context';
    case OddsAndMarketChoice = 'odds_and_market_choice';
    case ResearchGap = 'research_gap';
    case DecisionDiscipline = 'decision_discipline';
    case OutcomeReflection = 'outcome_reflection';
    case GeneralReflection = 'general_reflection';

    public function label(): string
    {
        return match ($this) {
            self::SlipConstruction => 'Slip construction',
            self::TeamOrFixtureContext => 'Team or fixture context',
            self::OddsAndMarketChoice => 'Odds and market choice',
            self::ResearchGap => 'Research gap',
            self::DecisionDiscipline => 'Decision discipline',
            self::OutcomeReflection => 'Outcome reflection',
            self::GeneralReflection => 'General reflection',
        };
    }
}
