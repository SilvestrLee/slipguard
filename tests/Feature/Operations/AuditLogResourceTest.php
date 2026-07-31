<?php

use App\Actions\Operations\RecordAuditEvent;
use App\Domain\Operations\OperationsAuditAction;
use App\Models\User;

test('a customer cannot access the Audit Log', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/operations/audit-log/audit-logs')
        ->assertForbidden();
});

test('an internal user without can_manage_customer_data cannot view the Audit Log', function () {
    $staffWithoutGrant = User::factory()->internal()->create();

    $this->actingAs($staffWithoutGrant)
        ->get('/operations/audit-log/audit-logs')
        ->assertForbidden();
});

test('operations staff can view the audit log listing recorded events', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();
    (new RecordAuditEvent)->execute($operationsStaff, OperationsAuditAction::CustomerViewed, $customer);

    $this->actingAs($operationsStaff)
        ->get('/operations/audit-log/audit-logs')
        ->assertOk()
        ->assertSee('Customer record viewed');
});
