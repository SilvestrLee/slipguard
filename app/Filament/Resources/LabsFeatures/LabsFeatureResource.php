<?php

namespace App\Filament\Resources\LabsFeatures;

use App\Filament\Resources\LabsFeatures\Pages\CreateLabsFeature;
use App\Filament\Resources\LabsFeatures\Pages\EditLabsFeature;
use App\Filament\Resources\LabsFeatures\Pages\ListLabsFeatures;
use App\Filament\Resources\LabsFeatures\Schemas\LabsFeatureForm;
use App\Filament\Resources\LabsFeatures\Tables\LabsFeaturesTable;
use App\Models\LabsFeature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LabsFeatureResource extends Resource
{
    protected static ?string $model = LabsFeature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LabsFeatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabsFeaturesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabsFeatures::route('/'),
            'create' => CreateLabsFeature::route('/create'),
            'edit' => EditLabsFeature::route('/{record}/edit'),
        ];
    }
}
