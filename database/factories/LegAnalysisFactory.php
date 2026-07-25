<?php

namespace Database\Factories;

use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Models\BettingSlipLeg;
use App\Models\LegAnalysis;
use App\Models\SlipAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegAnalysis>
 */
class LegAnalysisFactory extends Factory
{
    protected $model = LegAnalysis::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slip_analysis_id' => SlipAnalysis::factory(),
            'betting_slip_leg_id' => BettingSlipLeg::factory(),
            'display_order' => 0,
            'sport_code' => 'football',
            'sport_status' => NormalizationStatus::Complete,
            'market_code' => 'football.match_result.1x2',
            'market_family' => MarketFamily::MatchResult,
            'market_complexity' => MarketComplexity::Simple,
            'market_status' => NormalizationStatus::Complete,
            'decimal_odds' => fake()->randomFloat(2, 1.1, 10),
            'raw_market_input' => 'Match Result',
            'raw_selection_input' => 'Home',
        ];
    }
}
