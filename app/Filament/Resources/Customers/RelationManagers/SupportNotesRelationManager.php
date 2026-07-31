<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Actions\Operations\RecordAuditEvent;
use App\Domain\Operations\OperationsAuditAction;
use App\Models\SupportNote;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * U-10.2 §4: create-only. No EditAction or DeleteAction is registered
 * anywhere on this table — append-only is enforced by omission here and
 * by SupportNotePolicy's own absence of update/delete abilities.
 */
class SupportNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'supportNotes';

    protected static ?string $title = 'Support Notes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('note')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->columns([
                TextColumn::make('author.name')
                    ->label('Author'),
                TextColumn::make('note')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['author_id'] = Auth::id();

                        return $data;
                    })
                    ->after(function (SupportNote $record) {
                        (new RecordAuditEvent)->execute(Auth::user(), OperationsAuditAction::SupportNoteCreated, $record->customer);
                    }),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
