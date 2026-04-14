<?php

namespace App\Filament\Resources\LeavesAndAbsences\Pages;

use App\Filament\Resources\LeavesAndAbsences\LeaveAndAbsenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeaveAndAbsence extends EditRecord
{
    protected static string $resource = LeaveAndAbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
