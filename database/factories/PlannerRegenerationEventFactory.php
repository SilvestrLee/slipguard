<?php

namespace Database\Factories;

use App\Domain\Risk\Results\AnalysisAvailability;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannerRegenerationEvent>
 */
class PlannerRegenerationEventFactory extends Factory
{
    protected $model = PlannerRegenerationEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planner_session_id' => PlannerSession::factory(),
            'sequence_number' => 1,
            'availability' => AnalysisAvailability::Full,
            'structural_score' => 0,
            'risk_band' => null,
            'attributions' => [],
        ];
    }
}
