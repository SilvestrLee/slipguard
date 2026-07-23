<?php

namespace Database\Factories;

use App\Models\BettingSlip;
use App\Models\BettingSlipLeg;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BettingSlipLeg>
 */
class BettingSlipLegFactory extends Factory
{
    protected $model = BettingSlipLeg::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'betting_slip_id' => BettingSlip::factory(),
            'sport' => fake()->randomElement(['Football', 'Basketball', 'Tennis']),
            'competition' => fake()->optional()->words(2, true),
            'event_name' => fake()->company().' vs '.fake()->company(),
            'market_name' => 'Match Result',
            'selection_name' => fake()->company(),
            'decimal_odds' => fake()->randomFloat(2, 1.1, 10),
            'display_order' => 0,
        ];
    }
}
