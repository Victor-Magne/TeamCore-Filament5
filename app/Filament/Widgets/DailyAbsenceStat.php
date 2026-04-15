<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyAbsenceStat extends StatsOverviewWidget
{
    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    protected ?string $pollingInterval = '10s';

    public static function canView(): bool
    {
        return Auth::user()?->can('View:DailyAbsenceStat') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Absentismo';
    }

    public function getDescription(): ?string
    {
        return 'Estado de presença e faltas de hoje';
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        // Total de funcionários ativos com query otimizada
        $totalEmployees = Employee::where(function ($query) {
            $query->whereNull('date_dismissed')
                ->orWhere('date_dismissed', '>', Carbon::today());
        })->count();

        // Funcionários ausentes hoje com subquery melhorada
        $absentToday = LeaveAndAbsence::where(function ($query) use ($today) {
            $query->whereBetween('start_date', [$today, $today])
                ->orWhereBetween('end_date', [$today, $today])
                ->orWhere(function ($q) use ($today) {
                    $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                });
        })
            ->distinct('employee_id')
            ->count('employee_id');

        // Calcula percentagem
        $absencePercentage = $totalEmployees > 0 ? round(($absentToday / $totalEmployees) * 100, 1) : 0;

        // Define cor baseada em threshold
        $absenceColor = match (true) {
            $absencePercentage > 20 => 'danger',
            $absencePercentage > 10 => 'warning',
            default => 'success',
        };

        // Conta faltas injustificadas hoje
        $unjustifiedToday = LeaveAndAbsence::where('type', 'unjustified')
            ->where(function ($query) use ($today) {
                $query->whereBetween('start_date', [$today, $today])
                    ->orWhereBetween('end_date', [$today, $today])
                    ->orWhere(function ($q) use ($today) {
                        $q->where('start_date', '<=', $today)
                            ->where('end_date', '>=', $today);
                    });
            })
            ->count();

        return [
            Stat::make('Taxa de Absentismo', $absencePercentage.'%')
                ->description($absentToday.' de '.$totalEmployees.' funcionários')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($absenceColor),
            Stat::make('Ausentes Hoje', $absentToday)
                ->description('Funcionários em falta')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('info'),
            Stat::make('Faltas Injustificadas', $unjustifiedToday)
                ->description('Sem justificação registada')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($unjustifiedToday > 0 ? 'danger' : 'success'),
        ];
    }
}
