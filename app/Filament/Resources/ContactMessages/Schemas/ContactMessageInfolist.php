<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Domain\Contact\ContactMessageCategory;
use App\Domain\Contact\ContactMessageStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission')
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Name'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->copyable(),
                        TextEntry::make('category')
                            ->badge()
                            ->formatStateUsing(fn (?ContactMessageCategory $state) => $state?->label()),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?ContactMessageStatus $state) => $state?->color() ?? 'gray')
                            ->formatStateUsing(fn (?ContactMessageStatus $state) => $state?->label()),
                        TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Message')
                    ->schema([
                        TextEntry::make('subject'),
                        TextEntry::make('message')
                            ->label('Full message')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
