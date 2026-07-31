<?php

namespace Database\Factories;

use App\Domain\Planner\PlannerSessionStatus;
use App\Models\BettingSlip;
use App\Models\PlannerSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannerSession>
 */
class PlannerSessionFactory extends Factory
{
    protected $model = PlannerSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_betting_slip_id' => BettingSlip::factory(),
            'status' => PlannerSessionStatus::Draft,
        ];
    }
}
