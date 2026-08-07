<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageInfolist;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * `PO-U22-001A` — a read-plus-status support inbox, mirroring
 * `CustomerResource`'s own established pattern for this panel (explicit
 * `can*()` hooks on the Resource itself, not left to panel-level
 * `is_internal` gating alone; no Create/Edit — staff triage what a
 * visitor submitted, they never author or rewrite it). No relationship to
 * `CustomerResource`'s narrower `canManageCustomerData()` grant: a
 * contact message is a general inbound enquiry, not customer analysis
 * data, so any internal staff member may triage it — the same boundary
 * this panel's own login already draws for every other non-customer-data
 * section.
 */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Support';

    protected static ?string $navigationLabel = 'Contact Messages';

    public static function canViewAny(): bool
    {
        return auth()->user()?->is_internal ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->is_internal ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
