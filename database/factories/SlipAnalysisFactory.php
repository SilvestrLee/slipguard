<?php

namespace Database\Factories;

use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\RiskBand;
use App\Models\BettingSlip;
use App\Models\SlipAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlipAnalysis>
 */
class SlipAnalysisFactory extends Factory
{
    protected $model = SlipAnalysis::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'betting_slip_id' => BettingSlip::factory()->analysed(),
            'user_id' => fn (array $attributes) => BettingSlip::find($attributes['betting_slip_id'])->user_id,
            'availability' => AnalysisAvailability::Full,
            'structural_score' => fake()->numberBetween(0, 100),
            'risk_band' => fake()->randomElement(RiskBand::cases()),
            'data_quality_score' => 100,
            'data_quality_band' => DataQualityBand::Strong,
            'limited_analysis' => false,
            'factor_results' => [],
            'interaction_adjustments' => [],
            'data_quality_deductions' => [],
            'factors_not_evaluated' => [],
            'reason_codes' => [],
            'engine_version' => '1.0',
            'rule_set_version' => '2026.1',
            'input_schema_version' => '1.0',
            'market_taxonomy_version' => '1.0',
        ];
    }
}
