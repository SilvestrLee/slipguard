<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\Operations\OperationsAuditAction;
use App\Models\BettingSlip;
use App\Models\JournalEntry;
use App\Models\OperationsAuditLogEntry;
use App\Models\User;

function readySlipForOperations(User $user): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => 'Premier League', 'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);
    $slip->markReady();

    return $slip->fresh('legs');
}

test('a customer (non-internal user) cannot access the Operations panel at all', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/operations/customers')
        ->assertForbidden();
});

test('an internal user without can_manage_customer_data cannot view the Customers resource', function () {
    $staffWithoutGrant = User::factory()->internal()->create();

    $this->actingAs($staffWithoutGrant)
        ->get('/operations/customers')
        ->assertForbidden();
});

test('operations staff with can_manage_customer_data can view the Customers list', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create(['name' => 'Jordan Example']);

    $this->actingAs($operationsStaff)
        ->get('/operations/customers')
        ->assertOk()
        ->assertSee('Jordan Example');
});

test('the Customers list never shows internal staff accounts', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $otherStaff = User::factory()->internal()->create(['name' => 'Internal Colleague']);

    $this->actingAs($operationsStaff)
        ->get('/operations/customers')
        ->assertOk()
        ->assertDontSee('Internal Colleague');
});

test('viewing a customer record logs a customer_viewed audit event exactly once', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();

    $this->actingAs($operationsStaff)
        ->get("/operations/customers/{$customer->id}")
        ->assertOk();

    expect(OperationsAuditLogEntry::where('action', OperationsAuditAction::CustomerViewed->value)
        ->where('operator_id', $operationsStaff->id)
        ->where('customer_id', $customer->id)
        ->count())->toBe(1);
});

test('a customer overview page never shows Journal entry content by default', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();
    $slip = readySlipForOperations($customer);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    JournalEntry::factory()->create([
        'user_id' => $customer->id,
        'slip_analysis_id' => $analysis->id,
        'reflection' => 'A very private reflection only the customer should have chosen to write.',
    ]);

    $this->actingAs($operationsStaff)
        ->get("/operations/customers/{$customer->id}")
        ->assertOk()
        ->assertDontSee('A very private reflection only the customer should have chosen to write.');

    // Merely opening the customer's page must not have logged a journal_viewed event.
    expect(OperationsAuditLogEntry::where('action', OperationsAuditAction::JournalViewed->value)->count())->toBe(0);
});
