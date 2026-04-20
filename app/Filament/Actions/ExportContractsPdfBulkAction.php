<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class ExportContractsPdfBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'export_pdf';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Exportar PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->tooltip('Exportar contratos selecionados em PDF')
            ->url(fn (Collection $records) => route('contracts.pdf.bulk', ['ids' => $records->pluck('id')->implode(',')]))
            ->openUrlInNewTab()
            ->deselectRecordsAfterCompletion();
    }
}
