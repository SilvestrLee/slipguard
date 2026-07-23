<?php

namespace Database\Factories;

use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BettingSlip>
 */
class BettingSlipFactory extends Factory
{
    protected $model = BettingSlip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->optional()->words(3, true),
            'status' => BettingSlip::STATUS_DRAFT,
        ];
    }
}
