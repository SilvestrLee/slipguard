<?php

namespace App\Filament\Resources\LabsFeatures\Pages;

use App\Filament\Resources\LabsFeatures\LabsFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLabsFeature extends EditRecord
{
    protected static string $resource = LabsFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
