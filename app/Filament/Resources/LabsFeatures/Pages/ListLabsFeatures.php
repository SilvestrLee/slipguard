<?php

namespace App\Filament\Resources\LabsFeatures\Pages;

use App\Filament\Resources\LabsFeatures\LabsFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabsFeatures extends ListRecords
{
    protected static string $resource = LabsFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
