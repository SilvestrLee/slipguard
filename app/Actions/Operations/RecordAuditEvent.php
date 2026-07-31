<?php

namespace App\Actions\Operations;

use App\Domain\Operations\OperationsAuditAction;
use App\Models\OperationsAuditLogEntry;
use App\Models\User;

/**
 * The only write path for an OperationsAuditLogEntry — U-10.2 §5:
 * immutable once created, retained indefinitely. Never invoked by
 * anything customer-facing; every call site is inside an Operations
 * Console resource/page.
 *
 * @param  array<string, mixed>|null  $context
 */
class RecordAuditEvent
{
    public function execute(User $operator, OperationsAuditAction $action, ?User $customer = null, ?array $context = null): OperationsAuditLogEntry
    {
        return OperationsAuditLogEntry::create([
            'operator_id' => $operator->id,
            'customer_id' => $customer?->id,
            'action' => $action->value,
            'context' => $context,
        ]);
    }
}
