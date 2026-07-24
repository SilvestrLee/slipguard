<?php

namespace App\Models;

use App\Domain\BettingSlip\BettingSlipValidationRules;
use Database\Factories\BettingSlipLegFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BettingSlipLeg extends Model
{
    /** @use HasFactory<BettingSlipLegFactory> */
    use HasFactory;

    protected $fillable = [
        'sport',
        'competition',
        'event_name',
        'market_name',
        'selection_name',
        'decimal_odds',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'decimal_odds' => 'decimal:2',
            'display_order' => 'integer',
        ];
    }

    public function bettingSlip(): BelongsTo
    {
        return $this->belongsTo(BettingSlip::class);
    }

    /**
     * Defense in depth: even though the builder validates every leg before
     * saving, the domain re-checks completeness itself rather than trusting
     * the caller — the Risk Engine must never have to wonder.
     */
    public function isComplete(): bool
    {
        return BettingSlipValidationRules::legIsComplete([
            'sport' => $this->sport,
            'event_name' => $this->event_name,
            'market_name' => $this->market_name,
            'selection_name' => $this->selection_name,
            'decimal_odds' => $this->decimal_odds,
        ]);
    }
}
