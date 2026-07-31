<?php

namespace App\Actions\Operations;

use App\Models\SupportNote;
use App\Models\User;

/**
 * The only write path for a SupportNote — U-10.2 §4: append-only, so
 * there is deliberately no corresponding UpdateSupportNote or
 * DeleteSupportNote action anywhere in this codebase.
 */
class CreateSupportNote
{
    public function execute(User $author, User $customer, string $note): SupportNote
    {
        return SupportNote::create([
            'author_id' => $author->id,
            'customer_id' => $customer->id,
            'note' => $note,
        ]);
    }
}
