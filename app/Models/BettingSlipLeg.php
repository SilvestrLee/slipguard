<?php

namespace App\Models;

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
}
