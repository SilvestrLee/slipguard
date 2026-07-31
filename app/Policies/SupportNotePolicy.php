<?php

namespace App\Policies;

use App\Models\SupportNote;
use App\Models\User;

/**
 * No update or delete ability is defined — U-10.2 §4's append-only rule
 * enforced at the authorization layer, not merely by UI omission.
 */
class SupportNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCustomerData();
    }

    public function view(User $user, SupportNote $supportNote): bool
    {
        return $user->canManageCustomerData();
    }

    public function create(User $user): bool
    {
        return $user->canManageCustomerData();
    }
}
