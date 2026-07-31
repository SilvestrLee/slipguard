<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\Operations\OperationsAuditAction;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\BettingSlip;
use App\Models\JournalEntry;
use App\Models\OperationsAuditLogEntry;
use App\Models\User;
use Livewire\Livewire;

test('explicitly opening the Journal Entries action reveals content and logs its own dedicated audit event', function () {
    $operationsStaff = User::factory()->operationsStaff()->create();
    $customer = User::factory()->create();

    $slip = BettingSlip::factory()->for($customer)->create();
    $slip->legs()->create([
        'sport' => 'Football', 'event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result',
        'selection_name' => 'Arsenal', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);
    $slip->markReady();
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    JournalEntry::factory()->create([
        'user_id' => $customer->id,
        'slip_analysis_id' => $analysis->id,
        'reflection' => 'A note the customer chose to write down.',
    ]);

    Livewire::actingAs($operationsStaff)
        ->test(ViewCustomer::class, ['record' => $customer->getKey()])
        ->mountAction('viewJournalEntries')
        ->assertActionMounted('viewJournalEntries');

    // The customer_viewed event (from mount) plus the journal_viewed event (from opening the action) — both present, distinctly.
    expect(OperationsAuditLogEntry::where('action', OperationsAuditAction::CustomerViewed->value)->count())->toBe(1);
    expect(OperationsAuditLogEntry::where('action', OperationsAuditAction::JournalViewed->value)
        ->where('operator_id', $operationsStaff->id)
        ->where('customer_id', $customer->id)
        ->count())->toBe(1);
});
