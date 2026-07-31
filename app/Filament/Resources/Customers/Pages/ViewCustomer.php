<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Actions\Operations\RecordAuditEvent;
use App\Domain\Operations\OperationsAuditAction;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

/**
 * U-10.2 §3/§5: opening a customer's record is itself the auditable
 * "customer_viewed" event, logged once per page load — not per field
 * subsequently looked at. Journal Entries are the one deliberate
 * exception: they never load with the rest of this page and are only
 * ever revealed by the explicit action below, which logs its own,
 * separate "journal_viewed" event.
 */
class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        (new RecordAuditEvent)->execute(Auth::user(), OperationsAuditAction::CustomerViewed, $this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewJournalEntries')
                ->label('View Journal Entries')
                ->color('gray')
                ->modalHeading('Journal Entries')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(function () {
                    // U-10.2 §3/§5: the audit event is recorded at the exact
                    // moment Journal content is actually generated for
                    // display, not merely when the button is clicked.
                    (new RecordAuditEvent)->execute(Auth::user(), OperationsAuditAction::JournalViewed, $this->getRecord());

                    return view('filament.customer-journal-entries', [
                        'entries' => $this->getRecord()->journalEntries()->with('slipAnalysis.bettingSlip')->get(),
                    ]);
                }),
        ];
    }
}
