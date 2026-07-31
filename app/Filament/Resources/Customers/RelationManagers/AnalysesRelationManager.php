<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Domain\Risk\Results\RiskBand;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only. Shows only already-persisted SlipAnalysis facts — never
 * recalculated (ADR-007's read-only boundary, applied here identically to
 * the customer-facing Report screen).
 */
class AnalysesRelationManager extends RelationManager
{
    protected static string $relationship = 'slipAnalyses';

    protected static ?string $title = 'Analyses';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('bettingSlip.name')
                    ->label('Slip')
                    ->default('Untitled slip'),
                TextColumn::make('risk_band')
                    ->label('Band')
                    ->badge()
                    ->formatStateUsing(fn (?RiskBand $state) => $state?->label() ?? '—'),
                TextColumn::make('structural_score')
                    ->label('Score')
                    ->default('—'),
                TextColumn::make('availability')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Analysed')
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
