<?php

namespace App\Filament\App\Pages;

use App\Models\LeaveAndAbsence;
use App\Models\Vacation;
use BackedEnum;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeeCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|UnitEnum|null $navigationGroup = 'O Meu Espaço';

    protected string $view = 'filament.app.pages.employee-calendar';

    public array $calendarEvents = [];

    public static function getNavigationLabel(): string
    {
        return 'O Meu Calendário';
    }

    public function getTitle(): string
    {
        return 'O Meu Calendário';
    }

    public function mount(): void
    {
        $this->calendarEvents = $this->buildEvents();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('requestVacation')
                ->label('Solicitar Férias')
                ->icon('heroicon-m-sun')
                ->color('primary')
                ->modalHeading('Pedido de Férias')
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
                        ->body('O seu pedido foi registado e aguarda aprovação dos Recursos Humanos.')
                        ->success()
                        ->send();

                    $this->calendarEvents = $this->buildEvents();
                    $this->dispatch('calendar-refresh', events: $this->calendarEvents);
                }),

            Action::make('requestLeave')
                ->label('Solicitar Licença / Falta')
                ->icon('heroicon-m-calendar-days')
                ->color('gray')
                ->modalHeading('Pedido de Licença ou Falta')
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
                        ->directory('leaves'),
                ])
                ->action(function (array $data, Action $action): void {
                    $employee = Auth::user()->employee;

                    if (! $employee) {
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
                        'justification_doc' => $data['justification_doc'] ?? null,
                        'status' => 'pending',
                    ]);

                    Notification::make()
                        ->title('Pedido de Licença Submetido')
                        ->body('O seu pedido de licença/falta foi registado e aguarda aprovação dos Recursos Humanos.')
                        ->success()
                        ->send();

                    $this->calendarEvents = $this->buildEvents();
                    $this->dispatch('calendar-refresh', events: $this->calendarEvents);
                }),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildEvents(): array
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return [];
        }

        $events = [];

        foreach ($employee->vacations as $vacation) {
            $color = match ($vacation->status) {
                'approved' => '#10b981',
                'pending' => '#f59e0b',
                default => '#6b7280',
            };
            $events[] = [
                'title' => 'Férias',
                'start' => $vacation->start_date->toDateString(),
                'end' => $vacation->end_date->addDay()->toDateString(),
                'color' => $color,
                'extendedProps' => [
                    'type' => 'vacation',
                    'status' => $vacation->status,
                    'days' => $vacation->days_taken,
                ],
            ];
        }

        foreach ($employee->leaves as $leave) {
            $color = match ($leave->status) {
                'approved' => '#3b82f6',
                'pending' => '#93c5fd',
                default => '#6b7280',
            };
            $label = match ($leave->type) {
                'sick_leave' => 'Baixa Médica',
                'parental' => 'Lic. Parental',
                'marriage' => 'Lic. Casamento',
                'bereavement' => 'Nojo',
                'justified_absence' => 'Falta Justificada',
                default => $leave->type,
            };
            $events[] = [
                'title' => $label,
                'start' => $leave->start_date->toDateString(),
                'end' => $leave->end_date->addDay()->toDateString(),
                'color' => $color,
                'extendedProps' => [
                    'type' => 'leave',
                    'status' => $leave->status,
                    'reason' => $leave->reason,
                ],
            ];
        }

        foreach ($employee->absences as $absence) {
            $events[] = [
                'title' => 'Falta',
                'start' => $absence->absence_date->toDateString(),
                'end' => $absence->absence_date->toDateString(),
                'color' => '#ef4444',
                'extendedProps' => [
                    'type' => 'absence',
                    'deduction_type' => $absence->deduction_type,
                    'reason' => $absence->reason,
                ],
            ];
        }

        return $events;
    }
}
