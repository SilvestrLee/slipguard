<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

/**
 * No delete ability is defined — Product Office (`PO-U08.1-AC-001`)
 * resolved U-08.1's OQ-1 by directing entries be treated as immutable
 * historical records until formally decided otherwise.
 */
class JournalEntryPolicy
{
    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->id === $journalEntry->user_id;
    }
}
