<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Domain\Contact\ContactMessageStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

/**
 * `PO-U22-001A` — no `EditAction` here (staff triage, never rewrite, a
 * customer's submission — `ContactMessageResource::canEdit()` already
 * returns `false`, this page has no edit form to route to regardless).
 * Status-only header actions instead.
 */
class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    /**
     * A New message a staff member opens to read is, by definition, no
     * longer New — mirrors an ordinary inbox's own "opening marks read"
     * behaviour rather than requiring a separate manual step for the
     * most common transition.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record instanceof ContactMessage && $this->record->status === ContactMessageStatus::New) {
            $this->record->update(['status' => ContactMessageStatus::Read]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markResolved')
                ->label('Mark Resolved')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (ContactMessage $record) => $record->status !== ContactMessageStatus::Resolved)
                ->action(fn (ContactMessage $record) => $record->update(['status' => ContactMessageStatus::Resolved])),
            Action::make('reopen')
                ->label('Reopen')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->visible(fn (ContactMessage $record) => $record->status === ContactMessageStatus::Resolved)
                ->action(fn (ContactMessage $record) => $record->update(['status' => ContactMessageStatus::Read])),
        ];
    }
}
