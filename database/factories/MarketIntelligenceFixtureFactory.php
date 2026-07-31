<?php

namespace Database\Factories;

use App\Models\MarketIntelligenceFixture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketIntelligenceFixture>
 */
class MarketIntelligenceFixtureFactory extends Factory
{
    protected $model = MarketIntelligenceFixture::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_event_id' => $this->faker->unique()->uuid(),
            'competition_key' => 'soccer_epl',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'commence_time' => now()->addDays(2),
            'retrieved_at' => now(),
            'cache_expires_at' => now()->addHours(2),
        ];
    }
}
