<?php

namespace App\Models;

use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\InvalidPlannerSessionTransitionException;
use Database\Factories\PlannerSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlannerSession extends Model
{
    /** @use HasFactory<PlannerSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_betting_slip_id',
        'exported_betting_slip_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlannerSessionStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceBettingSlip(): BelongsTo
    {
        return $this->belongsTo(BettingSlip::class, 'source_betting_slip_id');
    }

    public function exportedBettingSlip(): BelongsTo
    {
        return $this->belongsTo(BettingSlip::class, 'exported_betting_slip_id');
    }

    public function selections(): HasMany
    {
        return $this->hasMany(PlannerSelection::class)->orderBy('display_order');
    }

    public function regenerationEvents(): HasMany
    {
        return $this->hasMany(PlannerRegenerationEvent::class)->orderBy('sequence_number');
    }

    public function latestRegenerationEvent(): HasOne
    {
        return $this->hasOne(PlannerRegenerationEvent::class)->latestOfMany('sequence_number');
    }

    public function markEvaluated(): void
    {
        $this->transitionTo(PlannerSessionStatus::Evaluated);
    }

    public function markComplete(): void
    {
        $this->transitionTo(PlannerSessionStatus::Complete);
    }

    public function markExported(): void
    {
        $this->transitionTo(PlannerSessionStatus::Exported);
    }

    public function abandon(): void
    {
        $this->transitionTo(PlannerSessionStatus::Abandoned);
    }

    private function transitionTo(PlannerSessionStatus $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new InvalidPlannerSessionTransitionException($this->status, $target);
        }

        $this->status = $target;
        $this->save();
    }
}
