<?php

namespace App\Filament\Resources\HourBanks\Pages;

use App\Filament\Resources\HourBanks\HourBankResource;
use Filament\Resources\Pages\EditRecord;

class EditHourBank extends EditRecord
{
    protected static string $resource = HourBankResource::class;

    // Desabilitar edição - é apenas visualização
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Impedir alterações
        return $this->record->getAttributes();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Sem ações de delete - é um registo de auditoria
        ];
    }
}
