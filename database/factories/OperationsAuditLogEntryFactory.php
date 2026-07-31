<?php

namespace Database\Factories;

use App\Domain\Operations\OperationsAuditAction;
use App\Models\OperationsAuditLogEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationsAuditLogEntry>
 */
class OperationsAuditLogEntryFactory extends Factory
{
    protected $model = OperationsAuditLogEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operator_id' => User::factory()->operationsStaff(),
            'customer_id' => User::factory(),
            'action' => OperationsAuditAction::CustomerViewed->value,
            'context' => null,
        ];
    }
}
