<?php

use App\Actions\Operations\CreateSupportNote;
use App\Actions\Operations\RecordAuditEvent;
use App\Domain\Operations\OperationsAuditAction;
use App\Models\OperationsAuditLogEntry;
use App\Models\SupportNote;
use App\Models\User;

test('CreateSupportNote links a note to its author and customer', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();

    $note = (new CreateSupportNote)->execute($operationsStaff, $customer, 'Customer reported a slow page load; advised to refresh.');

    expect($note->author_id)->toBe($operationsStaff->id);
    expect($note->customer_id)->toBe($customer->id);
    expect($note->note)->toBe('Customer reported a slow page load; advised to refresh.');
});

test('the Customer overview page renders the Support Notes panel without error', function () {
    // Filament v4 lazily loads relation manager tab content — only the tab
    // label is present in the initial response; the note text itself
    // loads via a follow-up request when the tab is activated. This test
    // confirms the page (and the Support Notes tab) renders without error.
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();
    (new CreateSupportNote)->execute($operationsStaff, $customer, 'An existing note on file.');

    $this->actingAs($operationsStaff)
        ->get("/operations/customers/{$customer->id}")
        ->assertOk()
        ->assertSee('Support Notes');
});

test('a support note cannot be edited or deleted through any authorization check', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();
    $note = SupportNote::factory()->create([
        'author_id' => $operationsStaff->id,
        'customer_id' => $customer->id,
    ]);

    expect($operationsStaff->can('update', $note))->toBeFalse();
    expect($operationsStaff->can('delete', $note))->toBeFalse();
});

test('an internal user without can_manage_customer_data cannot create a support note', function () {
    $staffWithoutGrant = User::factory()->internal()->create();

    expect($staffWithoutGrant->can('create', SupportNote::class))->toBeFalse();
});

test('recording a support-note-created audit event links operator and customer correctly', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();

    (new RecordAuditEvent)->execute($operationsStaff, OperationsAuditAction::SupportNoteCreated, $customer);

    expect(OperationsAuditLogEntry::where('action', OperationsAuditAction::SupportNoteCreated->value)
        ->where('operator_id', $operationsStaff->id)
        ->where('customer_id', $customer->id)
        ->count())->toBe(1);
});
