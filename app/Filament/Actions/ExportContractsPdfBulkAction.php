<?php

namespace App\Filament\Actions;

use App\Services\ContractPdfService;
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
            ->action(fn (Collection $records) => (new ContractPdfService)->downloadMultipleContractsPdf($records))
            ->deselectRecordsAfterCompletion();
    }
}
