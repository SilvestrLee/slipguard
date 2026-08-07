<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Domain\Contact\ContactMessageCategory;
use App\Domain\Contact\ContactMessageStatus;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * `PO-U22-001A` — read-only browse/search/filter, matching
 * `CustomersTable`'s own "read-only resource — no bulk actions" comment
 * and pattern exactly. The one mutation this resource permits (status)
 * is a single scoped row action, never a general edit form.
 */
class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (?ContactMessageCategory $state) => $state?->label())
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?ContactMessageStatus $state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn (?ContactMessageStatus $state) => $state?->label())
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ContactMessageStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('category')
                    ->options(collect(ContactMessageCategory::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markResolved')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContactMessage $record) => $record->status !== ContactMessageStatus::Resolved)
                    ->action(fn (ContactMessage $record) => $record->update(['status' => ContactMessageStatus::Resolved])),
            ])
            ->toolbarActions([
                // Read-only resource (`PO-U22-001A`) — no bulk actions, matching CustomersTable.
            ]);
    }
}
