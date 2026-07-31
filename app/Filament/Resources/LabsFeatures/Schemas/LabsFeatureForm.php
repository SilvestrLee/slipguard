<?php

namespace App\Filament\Resources\LabsFeatures\Schemas;

use App\Domain\Labs\LabsFeatureStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LabsFeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('summary')
                    ->required()
                    ->maxLength(255),
                Textarea::make('why_it_matters')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(LabsFeatureStatus::class)
                    ->default('planned')
                    ->required(),
                Toggle::make('notify_enabled')
                    ->required(),
                Toggle::make('beta_enabled')
                    ->required(),
                Toggle::make('is_published')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
