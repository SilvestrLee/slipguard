<?php

namespace Database\Factories;

use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketIntelligenceMarketQuote>
 */
class MarketIntelligenceMarketQuoteFactory extends Factory
{
    protected $model = MarketIntelligenceMarketQuote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'market_intelligence_fixture_id' => MarketIntelligenceFixture::factory(),
            'canonical_market' => 'match_result',
            'provider_market_key' => 'h2h',
            'bookmaker_key' => 'pinnacle',
            'outcomes' => [
                ['name' => 'Arsenal', 'price' => '1.85'],
                ['name' => 'Draw', 'price' => '3.60'],
                ['name' => 'Chelsea', 'price' => '4.50'],
            ],
            'provider_last_update' => now(),
            'retrieved_at' => now(),
            'cache_expires_at' => now()->addHours(2),
            'evidence_quality' => EvidenceQuality::Fresh,
        ];
    }
}
