<?php

namespace App\Filament\Actions;

use App\Models\Contract;
use Filament\Actions\Action;

class DownloadContractPdfAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'download_pdf';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Descarregar PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->tooltip('Descarregar contrato em PDF')
            ->url(fn (Contract $record) => route('contracts.pdf.single', $record));
    }
}
