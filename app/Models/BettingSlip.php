<?php

namespace App\Models;

use App\Domain\BettingSlip\AnalysisEligibility;
use App\Domain\BettingSlip\AnalysisIneligibilityReason;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Exceptions\BettingSlipLockedByPlannerException;
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

    protected $fillable = ['name', 'source_screenshot_path'];

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
     * A repository-backed label for slips whose optional customer name is
     * empty. It uses only recorded intake facts and never invents a fixture,
     * competition, result, or analytical conclusion.
     */
    public function displayLabel(): string
    {
        if (filled(trim((string) $this->name))) {
            return trim((string) $this->name);
        }

        $legs = $this->relationLoaded('legs')
            ? $this->legs
            : $this->legs()->get();

        $firstEvent = trim((string) $legs->first()?->event_name);

        if ($firstEvent !== '') {
            $remaining = max($legs->count() - 1, 0);

            return $remaining > 0
                ? $firstEvent.' + '.$remaining.' more '.str('selection')->plural($remaining)
                : $firstEvent;
        }

        if ($this->source_screenshot_path) {
            return 'Screenshot slip';
        }

        return 'Slip from '.$this->created_at->format('j M Y');
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
     * Unlock a Ready slip so it can be edited again. PD-008/ADR-009: refused
     * while a non-terminal PlannerSession is using this slip as its source
     * — the source slip is locked for the duration of an active planning
     * session, released automatically once that session reaches a
     * terminal status (Exported/Abandoned).
     */
    public function returnToDraft(): void
    {
        if ($this->isLockedByPlanner()) {
            throw new BettingSlipLockedByPlannerException;
        }

        $this->transitionTo(BettingSlipStatus::Draft);
    }

    /**
     * A computed fact, never a stored flag (ADR-009's source-slip lock
     * representation) — always re-evaluated against current state, so it
     * can never silently desync from the sessions that actually exist.
     */
    public function isLockedByPlanner(): bool
    {
        return $this->plannerSessions()
            ->whereNotIn('status', [PlannerSessionStatus::Exported->value, PlannerSessionStatus::Abandoned->value])
            ->exists();
    }

    /**
     * U-07.8 validation finding: distinct from isLockedByPlanner() above.
     * A PlannerSession row is never deleted regardless of its status
     * (Abandoned/Exported are terminal, not removed) — and
     * source_betting_slip_id's restrictOnDelete constraint (PD-008/PD-009's
     * "permanently preserved" principle) therefore blocks deletion of a
     * slip that was EVER used as a Planner source, not only while a
     * session is still open. Deletion UIs must check this, not
     * isLockedByPlanner(), which correctly excludes terminal sessions for
     * its own (editing-lock) purpose but would wrongly imply deletion
     * becomes safe again once a session ends.
     */
    public function hasPlannerHistory(): bool
    {
        return $this->plannerSessions()->exists();
    }

    public function plannerSessions(): HasMany
    {
        return $this->hasMany(PlannerSession::class, 'source_betting_slip_id');
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
