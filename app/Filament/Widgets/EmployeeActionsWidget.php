<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeActionsWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.employee-actions-widget';

    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeeActionsWidget') ?? false;
    }

    protected int|string|array $columnSpan = 1;

    public function requestVacationAction(): Action
    {
        return Action::make('requestVacation')
            ->label('Solicitar Férias')
            ->icon('heroicon-m-sun')
            ->color('primary')
            ->form([
                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->native(false),
                DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date'),
            ])
            ->action(function (array $data): void {
                $employee = Auth::user()->employee;

                if (! $employee) {
                    Notification::make()
                        ->title('Erro')
                        ->body('Não foi encontrado um perfil de funcionário associado ao seu utilizador.')
                        ->danger()
                        ->send();

                    return;
                }

                Vacation::create([
                    'employee_id' => $employee->id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => 'pending',
                    'year_reference' => Carbon::parse($data['start_date'])->year,
                ]);

                Notification::make()
                    ->title('Pedido de Férias Submetido')
                    ->body('O seu pedido de férias foi registado e aguarda aprovação.')
                    ->success()
                    ->send();
            });
    }

    public function requestLeaveAction(): Action
    {
        return Action::make('requestLeave')
            ->label('Solicitar Licença / Falta')
            ->icon('heroicon-m-calendar')
            ->color('secondary')
            ->form([
                Select::make('type')
                    ->label('Tipo de Ausência')
                    ->options([
                        'sick_leave' => 'Baixa Médica (SNS)',
                        'parental' => 'Licença Parental',
                        'marriage' => 'Licença de Casamento',
                        'bereavement' => 'Nojo (Falecimento)',
                        'justified_absence' => 'Falta Justificada',
                    ])
                    ->required()
                    ->native(false),
                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->native(false),
                DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date'),
                Textarea::make('reason')
                    ->label('Motivo')
                    ->required(),
                FileUpload::make('justification_doc')
                    ->label('Documento de Justificação')
                    ->directory('leaves'),
            ])
            ->action(function (array $data): void {
                $employee = Auth::user()->employee;

                if (! $employee) {
                    Notification::make()
                        ->title('Erro')
                        ->body('Não foi encontrado um perfil de funcionário associado ao seu utilizador.')
                        ->danger()
                        ->send();

                    return;
                }

                LeaveAndAbsence::create([
                    'employee_id' => $employee->id,
                    'type' => $data['type'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'reason' => $data['reason'],
                    'justification_doc' => $data['justification_doc'],
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Pedido de Licença Submetido')
                    ->body('O seu pedido de licença/falta foi registado e aguarda aprovação.')
                    ->success()
                    ->send();
            });
    }
}
