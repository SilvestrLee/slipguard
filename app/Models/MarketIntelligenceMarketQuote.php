<?php

namespace App\Models;

use App\Domain\MarketIntelligence\EvidenceQuality;
use Database\Factories\MarketIntelligenceMarketQuoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketIntelligenceMarketQuote extends Model
{
    /** @use HasFactory<MarketIntelligenceMarketQuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'market_intelligence_fixture_id',
        'canonical_market',
        'provider_market_key',
        'bookmaker_key',
        'outcomes',
        'provider_last_update',
        'retrieved_at',
        'cache_expires_at',
        'evidence_quality',
    ];

    protected function casts(): array
    {
        return [
            'outcomes' => 'array',
            'provider_last_update' => 'immutable_datetime',
            'retrieved_at' => 'immutable_datetime',
            'cache_expires_at' => 'immutable_datetime',
            'evidence_quality' => EvidenceQuality::class,
        ];
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(MarketIntelligenceFixture::class, 'market_intelligence_fixture_id');
    }
}
