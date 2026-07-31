<?php

namespace App\Actions\Journal;

use App\Domain\Journal\JournalEntryCategory;
use App\Models\JournalEntry;
use App\Models\SlipAnalysis;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Sprint 12: the optional analysis relationship is chosen once here and
 * never appears in the update action.
 */
class CreateJournalEntry
{
    public function execute(
        User $user,
        ?SlipAnalysis $slipAnalysis,
        string $reflection,
        ?string $title = null,
        JournalEntryCategory $category = JournalEntryCategory::GeneralReflection,
        ?string $nextTimeNote = null,
    ): JournalEntry {
        if ($slipAnalysis && $slipAnalysis->user_id !== $user->id) {
            throw new AuthorizationException('The selected analysis does not belong to this customer.');
        }

        return JournalEntry::create([
            'user_id' => $user->id,
            'slip_analysis_id' => $slipAnalysis?->id,
            'analysis_was_linked' => $slipAnalysis !== null,
            'title' => filled($title) ? trim($title) : null,
            'category' => $category,
            'reflection' => $reflection,
            'next_time_note' => filled($nextTimeNote) ? trim($nextTimeNote) : null,
        ]);
    }
}
