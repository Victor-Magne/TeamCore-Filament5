<?php

namespace App\Filament\Resources\HourBanks\Pages;

use App\Filament\Resources\HourBanks\HourBankResource;
use Filament\Resources\Pages\ListRecords;

class ListHourBanks extends ListRecords
{
    protected static string $resource = HourBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // HourBanks são criados automaticamente pelo sistema
            // Não há ação de Create manual
        ];
    }
}
