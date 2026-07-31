<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\AnalysesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\BettingSlipsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\PlannerSessionsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\SupportNotesRelationManager;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * U-10.1/U-10.2: a read-only support-visibility surface over customer
 * data. Authorization is defined directly on this Resource — deliberately
 * not via an Eloquent Policy on `User` — per U-10.1 §4's independent-
 * authorization architecture: this must never share a code path with any
 * customer-facing authorization concern.
 */
class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Support';

    protected static ?string $navigationLabel = 'Customers';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_internal', false);
    }

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
        return CustomersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            BettingSlipsRelationManager::class,
            AnalysesRelationManager::class,
            PlannerSessionsRelationManager::class,
            SupportNotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
