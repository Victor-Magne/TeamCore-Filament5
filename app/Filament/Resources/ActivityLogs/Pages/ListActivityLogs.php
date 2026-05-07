<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Exports\ActivityLogExporter;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exportar')
                ->icon('heroicon-o-arrow-down-tray')
                ->exporter(ActivityLogExporter::class),
        ];
    }
}
