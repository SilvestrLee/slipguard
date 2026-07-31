<?php

namespace App\Models;

use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use Database\Factories\PlannerRegenerationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable row per evaluation cycle (ADR-009: "no event is ever
 * mutated or deleted"). `attributions` stores each leg's MSC facts as
 * plain, JSON-safe arrays — never Eloquent/domain objects — mirroring how
 * SlipAnalysis/LegAnalysis already store the Risk Engine's own output
 * (ADR-007).
 */
class PlannerRegenerationEvent extends Model
{
    /** @use HasFactory<PlannerRegenerationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'sequence_number',
        'availability',
        'structural_score',
        'risk_band',
        'attributions',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'availability' => AnalysisAvailability::class,
            'structural_score' => 'integer',
            'risk_band' => RiskBand::class,
            'attributions' => 'array',
        ];
    }

    public function plannerSession(): BelongsTo
    {
        return $this->belongsTo(PlannerSession::class);
    }
}
