<?php

namespace App\Models;

use App\Domain\Planner\PlannerSelectionLockState;
use Database\Factories\PlannerSelectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannerSelection extends Model
{
    /** @use HasFactory<PlannerSelectionFactory> */
    use HasFactory;

    protected $fillable = [
        'sport',
        'competition',
        'event_name',
        'market_name',
        'selection_name',
        'decimal_odds',
        'display_order',
        'lock_state',
    ];

    protected function casts(): array
    {
        return [
            'decimal_odds' => 'decimal:2',
            'display_order' => 'integer',
            'lock_state' => PlannerSelectionLockState::class,
        ];
    }

    public function plannerSession(): BelongsTo
    {
        return $this->belongsTo(PlannerSession::class);
    }

    public function isLocked(): bool
    {
        return $this->lock_state === PlannerSelectionLockState::Locked;
    }
}
