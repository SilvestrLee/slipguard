<?php

namespace App\Filament\Resources\AuditLog\Pages;

use App\Filament\Resources\AuditLog\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogEntries extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only — audit entries are never created through this UI.
        ];
    }
}
