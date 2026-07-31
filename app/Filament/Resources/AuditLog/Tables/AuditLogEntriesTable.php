<?php

namespace App\Filament\Resources\AuditLog\Tables;

use App\Domain\Operations\OperationsAuditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operator.name')
                    ->label('Operator'),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->default('—'),
                TextColumn::make('action')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => OperationsAuditAction::from($state)->label()),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
