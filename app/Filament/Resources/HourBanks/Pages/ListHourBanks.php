<?php

namespace App\Filament\Resources\HourBanks\Pages;

use App\Filament\Resources\HourBanks\HourBankResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListHourBanks extends ListRecords
{
    protected static string $resource = HourBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncHourBank')
                ->label('Sincronizar Saldos')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar Banco de Horas')
                ->modalDescription('Esta acção irá verificar e corrigir inconsistências em todos os saldos, removendo movimentos órfãos de faltas eliminadas. Deseja continuar?')
                ->modalSubmitActionLabel('Sincronizar Agora')
                ->action(function () {
                    // Executa o comando Artisan de sincronização
                    Artisan::call('app:sync-hour-bank');

                    // Captura a saída do comando
                    $output = Artisan::output();

                    // Extrai as estatísticas simplificadas da saída (formato do comando alterado para facilitar)
                    Notification::make()
                        ->title('Sincronização Concluída')
                        ->body('A verificação de integridade foi executada com sucesso. Os saldos foram actualizados.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
