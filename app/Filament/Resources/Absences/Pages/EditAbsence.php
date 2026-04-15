<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Filament\Resources\Pages\EditRecord;

class EditAbsence extends EditRecord
{
    protected static string $resource = AbsenceResource::class;

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
