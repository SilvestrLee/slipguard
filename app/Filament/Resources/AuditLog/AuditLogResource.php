<?php

namespace App\Filament\Resources\AuditLog;

use App\Filament\Resources\AuditLog\Pages\ListAuditLogEntries;
use App\Filament\Resources\AuditLog\Tables\AuditLogEntriesTable;
use App\Models\OperationsAuditLogEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only, list-only — audit entries are only ever written by
 * App\Actions\Operations\RecordAuditEvent, never through this UI.
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = OperationsAuditLogEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Support';

    protected static ?string $navigationLabel = 'Audit Log';

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageCustomerData() ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->canManageCustomerData() ?? false;
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

    public static function table(Table $table): Table
    {
        return AuditLogEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogEntries::route('/'),
        ];
    }
}
