<?php

namespace App\Models;

use Database\Factories\MarketIntelligenceFixtureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketIntelligenceFixture extends Model
{
    /** @use HasFactory<MarketIntelligenceFixtureFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_event_id',
        'competition_key',
        'home_team',
        'away_team',
        'commence_time',
        'retrieved_at',
        'cache_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'commence_time' => 'immutable_datetime',
            'retrieved_at' => 'immutable_datetime',
            'cache_expires_at' => 'immutable_datetime',
        ];
    }

    public function marketQuotes(): HasMany
    {
        return $this->hasMany(MarketIntelligenceMarketQuote::class);
    }
}
