<?php

namespace App\Models;

use App\Domain\BettingSlip\AnalysisEligibility;
use App\Domain\BettingSlip\AnalysisIneligibilityReason;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Exceptions\InvalidBettingSlipTransitionException;
use Database\Factories\BettingSlipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BettingSlip extends Model
{
    /** @use HasFactory<BettingSlipFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return [
            'status' => BettingSlipStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(BettingSlipLeg::class)->orderBy('display_order');
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(SlipAnalysis::class);
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Lock this slip for analysis. Only a Draft slip with at least one
     * complete leg may become Ready.
     */
    public function markReady(): void
    {
        if ($this->legs->isEmpty() || $this->legs->contains(fn (BettingSlipLeg $leg) => ! $leg->isComplete())) {
            throw new InvalidBettingSlipTransitionException($this->status, BettingSlipStatus::Ready);
        }

        $this->transitionTo(BettingSlipStatus::Ready);
    }

    /**
     * Unlock a Ready slip so it can be edited again.
     */
    public function returnToDraft(): void
    {
        $this->transitionTo(BettingSlipStatus::Draft);
    }

    /**
     * Record that the (future) Risk Engine has produced a result for this
     * slip. Nothing in this sprint calls this — it exists so the domain
     * contract is ready for E-04's deterministic analysis without further
     * structural change.
     */
    public function markAnalysed(): void
    {
        $this->transitionTo(BettingSlipStatus::Analysed);
    }

    public function archive(): void
    {
        $this->transitionTo(BettingSlipStatus::Archived);
    }

    private function transitionTo(BettingSlipStatus $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new InvalidBettingSlipTransitionException($this->status, $target);
        }

        $this->status = $target;
        $this->save();
    }

    /**
     * Can this slip be analysed right now, and if not, why?
     */
    public function analysisEligibility(): AnalysisEligibility
    {
        if ($this->status === BettingSlipStatus::Archived) {
            return AnalysisEligibility::ineligible(AnalysisIneligibilityReason::Archived);
        }

        if ($this->status === BettingSlipStatus::Analysed) {
            return AnalysisEligibility::ineligible(AnalysisIneligibilityReason::AlreadyAnalysed);
        }

        if ($this->status !== BettingSlipStatus::Ready) {
            return AnalysisEligibility::ineligible(AnalysisIneligibilityReason::NotReady);
        }

        if ($this->legs->isEmpty()) {
            return AnalysisEligibility::ineligible(AnalysisIneligibilityReason::Empty);
        }

        $incomplete = $this->legs->contains(fn (BettingSlipLeg $leg) => ! $leg->isComplete());

        if ($incomplete) {
            return AnalysisEligibility::ineligible(AnalysisIneligibilityReason::Incomplete);
        }

        return AnalysisEligibility::eligible();
    }

    public function isAnalysisEligible(): bool
    {
        return $this->analysisEligibility()->eligible;
    }
}
