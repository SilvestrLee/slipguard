<?php

namespace App\Models;

use App\Domain\Journal\JournalEntryCategory;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sprint 12: a customer-owned decision reflection which may optionally
 * reference one analysis. A selected relationship remains immutable.
 * `analysis_was_linked` distinguishes an intentionally independent entry
 * from one whose former analysis became unavailable through an exceptional
 * external or administrative deletion.
 */
class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slip_analysis_id',
        'analysis_was_linked',
        'title',
        'category',
        'reflection',
        'next_time_note',
    ];

    protected function casts(): array
    {
        return [
            'analysis_was_linked' => 'boolean',
            'category' => JournalEntryCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function slipAnalysis(): BelongsTo
    {
        return $this->belongsTo(SlipAnalysis::class);
    }

    public function displayTitle(): string
    {
        if (filled(trim((string) $this->title))) {
            return trim((string) $this->title);
        }

        if ($this->slipAnalysis?->bettingSlip) {
            return $this->slipAnalysis->bettingSlip->displayLabel();
        }

        if ($this->category) {
            return $this->category->label().' · '.$this->created_at->format('j M Y');
        }

        return 'Decision note · '.$this->created_at->format('j M Y');
    }

    public function analysisLinkState(): string
    {
        if ($this->slip_analysis_id && $this->slipAnalysis) {
            return 'linked';
        }

        return $this->analysis_was_linked ? 'unavailable' : 'unlinked';
    }
}
