<?php

namespace App\Actions\Journal;

use App\Domain\Journal\JournalEntryCategory;
use App\Models\JournalEntry;

/**
 * The optional analysis relationship and owner never appear here, preserving
 * the Sprint 12 immutable-link contract.
 */
class UpdateJournalEntry
{
    public function execute(
        JournalEntry $journalEntry,
        string $reflection,
        ?string $title = null,
        JournalEntryCategory $category = JournalEntryCategory::GeneralReflection,
        ?string $nextTimeNote = null,
    ): JournalEntry {
        $journalEntry->update([
            'title' => filled($title) ? trim($title) : null,
            'category' => $category,
            'reflection' => $reflection,
            'next_time_note' => filled($nextTimeNote) ? trim($nextTimeNote) : null,
        ]);

        return $journalEntry;
    }
}
