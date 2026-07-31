<?php

namespace App\Models;

use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Results\RiskBand;
use Database\Factories\SlipAnalysisFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The persisted, immutable record of one completed risk analysis — created
 * once by App\Actions\Analysis\AnalyzeBettingSlip and never updated
 * afterward. `factor_results` and `interaction_adjustments` are stored as
 * plain arrays (every BigDecimal and ReasonCode already reduced to a
 * string by the orchestration action) so this model has no dependency on
 * the engine's value objects — persistence wraps the engine, it does not
 * extend it.
 */
class SlipAnalysis extends Model
{
    /** @use HasFactory<SlipAnalysisFactory> */
    use HasFactory;

    /**
     * `user_id` is deliberately excluded — always set explicitly from the
     * betting slip's own owner inside AnalyzeBettingSlip, never from a
     * mass-assigned array (security-authorization convention).
     */
    protected $fillable = [
        'betting_slip_id',
        'availability',
        'structural_score',
        'risk_band',
        'data_quality_score',
        'data_quality_band',
        'limited_analysis',
        'factor_results',
        'interaction_adjustments',
        'data_quality_deductions',
        'factors_not_evaluated',
        'reason_codes',
        'engine_version',
        'rule_set_version',
        'input_schema_version',
        'market_taxonomy_version',
    ];

    protected function casts(): array
    {
        return [
            'availability' => AnalysisAvailability::class,
            'risk_band' => RiskBand::class,
            'data_quality_band' => DataQualityBand::class,
            'limited_analysis' => 'boolean',
            'factor_results' => 'array',
            'interaction_adjustments' => 'array',
            'data_quality_deductions' => 'array',
            'factors_not_evaluated' => 'array',
            'reason_codes' => AsEnumCollection::of(ReasonCode::class),
        ];
    }

    public function bettingSlip(): BelongsTo
    {
        return $this->belongsTo(BettingSlip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function legAnalyses(): HasMany
    {
        return $this->hasMany(LegAnalysis::class)->orderBy('display_order');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class)->latest();
    }
}
