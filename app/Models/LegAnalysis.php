<?php

namespace App\Models;

use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use Database\Factories\LegAnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable, per-leg snapshot of how a leg was normalized at the moment
 * its slip was analysed — preserved independently of any future taxonomy
 * version, since a later taxonomy could reclassify the same raw text
 * differently (four independent version axes: engine, rule set, taxonomy,
 * input schema — this row anchors the taxonomy axis for one leg in time).
 */
class LegAnalysis extends Model
{
    /** @use HasFactory<LegAnalysisFactory> */
    use HasFactory;

    protected $fillable = [
        'slip_analysis_id',
        'betting_slip_leg_id',
        'display_order',
        'sport_code',
        'sport_status',
        'market_code',
        'market_family',
        'market_complexity',
        'market_status',
        'decimal_odds',
        'raw_market_input',
        'raw_selection_input',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'sport_status' => NormalizationStatus::class,
            'market_family' => MarketFamily::class,
            'market_complexity' => MarketComplexity::class,
            'market_status' => NormalizationStatus::class,
            'decimal_odds' => 'decimal:2',
        ];
    }

    public function slipAnalysis(): BelongsTo
    {
        return $this->belongsTo(SlipAnalysis::class);
    }

    public function bettingSlipLeg(): BelongsTo
    {
        return $this->belongsTo(BettingSlipLeg::class);
    }
}
