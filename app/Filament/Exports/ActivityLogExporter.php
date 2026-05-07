<?php

namespace App\Filament\Exports;

use App\Models\ActivityLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ActivityLogExporter extends Exporter
{
    protected static ?string $model = ActivityLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Data/Hora')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i:s')),
            ExportColumn::make('event')
                ->label('Tipo de Evento')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'created' => 'Criado',
                    'updated' => 'Atualizado',
                    'deleted' => 'Eliminado',
                    default => $state,
                }),
            ExportColumn::make('description')
                ->label('Ação'),
            ExportColumn::make('subject_type')
                ->label('Tipo de Assunto'),
            ExportColumn::make('causer_type')
                ->label('Tipo de Actor'),
            ExportColumn::make('log_name')
                ->label('Nome do Log'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your activity log export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
