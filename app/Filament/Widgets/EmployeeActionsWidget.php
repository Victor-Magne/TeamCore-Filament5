<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeActionsWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.employee-actions-widget';

    // 1. Expandido para ocupar toda a largura
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->can('View:EmployeeActionsWidget') ?? false;
    }

    public function requestVacationAction(): Action
    {
        return Action::make('requestVacation')
            ->label('Solicitar Férias')
            ->icon('heroicon-m-sun')
            ->color('primary')
            ->size('sm')
            ->form([
                DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        $end = $get('end_date');
                        if ($state && $end) {
                            $days = collect(CarbonPeriod::create($state, $end))
                                ->filter(fn ($d) => ! $d->isWeekend())
                                ->count();
                            $set('days_calculated', $days);
                        }
                    }),
                DatePicker::make('end_date')
                    ->label('Data de Fim')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date')
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        $start = $get('start_date');
                        if ($start && $state) {
                            $days = collect(CarbonPeriod::create($start, $state))
                                ->filter(fn ($d) => ! $d->isWeekend())
                                ->count();
                            $set('days_calculated', $days);
                        }
                    }),
                TextInput::make('days_calculated')
                    ->label('Dias Úteis')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(0)
                    ->suffix('dias'),
            ])
            ->action(function (array $data, Action $action): void {
                $employee = Auth::user()->employee;

                if (! $employee) {
                    Notification::make()
                        ->title('Erro')
                        ->body('Não foi encontrado um perfil de funcionário associado ao seu utilizador.')
                        ->danger()
                        ->send();
                    $action->halt();

                    return;
                }

                $workingDays = collect(CarbonPeriod::create($data['start_date'], $data['end_date']))
                    ->filter(fn ($d) => ! $d->isWeekend())
                    ->count();

                if ($workingDays > ($employee->vacation_balance ?? 0)) {
                    Notification::make()
                        ->title('Saldo de férias insuficiente')
                        ->body("Saldo disponível: {$employee->vacation_balance} dias. Dias úteis pedidos: {$workingDays}.")
                        ->danger()
                        ->send();
                    $action->halt();

                    return;
                }

                $hasVacationOverlap = Vacation::where('employee_id', $employee->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date'])
                    ->exists();

                $hasLeaveOverlap = LeaveAndAbsence::where('employee_id', $employee->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date'])
                    ->exists();

                if ($hasVacationOverlap || $hasLeaveOverlap) {
                    Notification::make()
                        ->title('Período em conflito')
                        ->body('Já existe uma férias ou licença registada que coincide com o período solicitado.')
                        ->danger()
                        ->send();
                    $action->halt();

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
            ->color('primary')
            ->size('sm')
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
                    ->label('Documento de Justificação (opcional)')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('leaves/justifications')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                    ->maxSize(5120),
            ])
            ->action(function (array $data, Action $action): void {
                $employee = Auth::user()->employee;

                if (! $employee) {
                    Notification::make()
                        ->title('Erro')
                        ->body('Não foi encontrado um perfil de funcionário associado ao seu utilizador.')
                        ->danger()
                        ->send();
                    $action->halt();

                    return;
                }

                $hasLeaveOverlap = LeaveAndAbsence::where('employee_id', $employee->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date'])
                    ->exists();

                $hasVacationOverlap = Vacation::where('employee_id', $employee->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date'])
                    ->exists();

                if ($hasLeaveOverlap || $hasVacationOverlap) {
                    Notification::make()
                        ->title('Período em conflito')
                        ->body('Já existe uma licença ou férias registada que coincide com o período solicitado.')
                        ->danger()
                        ->send();
                    $action->halt();

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
