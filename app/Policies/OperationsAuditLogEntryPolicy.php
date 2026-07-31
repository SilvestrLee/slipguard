<?php

namespace App\Policies;

use App\Models\OperationsAuditLogEntry;
use App\Models\User;

/**
 * No update, create-via-UI, or delete ability is defined — audit entries
 * are only ever written by App\Actions\Operations\RecordAuditEvent, never
 * through a Filament form, and are immutable once created (U-10.2 §5).
 */
class OperationsAuditLogEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCustomerData();
    }

    public function view(User $user, OperationsAuditLogEntry $operationsAuditLogEntry): bool
    {
        return $user->canManageCustomerData();
    }
}
