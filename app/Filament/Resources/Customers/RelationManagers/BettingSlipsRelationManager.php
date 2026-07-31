<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Domain\BettingSlip\BettingSlipStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only — U-10.2 §3: structural facts, no editing/deleting from the
 * Operations Console.
 */
class BettingSlipsRelationManager extends RelationManager
{
    protected static string $relationship = 'bettingSlips';

    protected static ?string $title = 'Betting Slips';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->default('Untitled slip'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BettingSlipStatus $state) => $state->label()),
                TextColumn::make('legs_count')
                    ->label('Legs')
                    ->counts('legs'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
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
