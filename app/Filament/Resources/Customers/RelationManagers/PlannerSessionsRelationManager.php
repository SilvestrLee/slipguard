<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Domain\Planner\PlannerSessionStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only. Sessions remain exactly as the customer left them — no
 * status change, no revision, no export is possible from here.
 */
class PlannerSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'plannerSessions';

    protected static ?string $title = 'Planner Sessions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('sourceBettingSlip.name')
                    ->label('Source slip')
                    ->default('Untitled slip'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PlannerSessionStatus $state) => $state->label()),
                TextColumn::make('exportedBettingSlip.name')
                    ->label('Exported to')
                    ->default('—'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
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
