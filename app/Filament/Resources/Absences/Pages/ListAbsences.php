<?php

namespace App\Filament\Resources\Absences\Pages;

use App\Filament\Resources\Absences\AbsenceResource;
use Filament\Resources\Pages\ListRecords;

class ListAbsences extends ListRecords
{
    protected static string $resource = AbsenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('checkAttendance')
                ->label('Verificar Presenças')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date')
                        ->label('Data para Verificação')
                        ->default(now()->subDay())
                        ->native(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    \Illuminate\Support\Facades\Artisan::call('app:check-daily-attendance', [
                        'date' => $data['date'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Verificação Concluída')
                        ->success()
                        ->send();
                }),
        ];
    }
}
