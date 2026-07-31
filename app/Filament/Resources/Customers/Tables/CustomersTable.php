<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['bettingSlips', 'slipAnalyses', 'plannerSessions']))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('betting_slips_count')
                    ->label('Slips')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('slip_analyses_count')
                    ->label('Analyses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('planner_sessions_count')
                    ->label('Planner sessions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // Read-only resource — no bulk actions.
            ]);
    }
}
