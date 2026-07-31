<?php

namespace Database\Factories;

use App\Models\PlannerSelection;
use App\Models\PlannerSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannerSelection>
 */
class PlannerSelectionFactory extends Factory
{
    protected $model = PlannerSelection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planner_session_id' => PlannerSession::factory(),
            'sport' => 'Football',
            'competition' => 'Premier League',
            'event_name' => fake()->words(3, true),
            'market_name' => 'Match Result',
            'selection_name' => 'Home',
            'decimal_odds' => fake()->randomFloat(2, 1.10, 5.00),
            'display_order' => 0,
        ];
    }
}
