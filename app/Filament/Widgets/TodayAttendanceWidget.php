<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AttendanceCheckIn;
use App\Models\AttendanceLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TodayAttendanceWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Ponto de Hoje';
    }

    public static function canView(): bool
    {
        return auth()->user()?->employee !== null;
    }

    protected function getStats(): array
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return [];
        }

        $log = AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('time_in', Carbon::today())
            ->latest()
            ->first();

        $fmt = fn (?Carbon $dt): string => $dt ? $dt->format('H:i') : '—';

        $lunch = '—';
        if ($log?->lunch_break_start && $log?->lunch_break_end) {
            $lunch = $fmt($log->lunch_break_start).' – '.$fmt($log->lunch_break_end);
        } elseif ($log?->lunch_break_start) {
            $lunch = $fmt($log->lunch_break_start).' – ...';
        }

        $worked = '—';
        if ($log?->total_minutes) {
            $h = intdiv($log->total_minutes, 60);
            $m = $log->total_minutes % 60;
            $worked = "{$h}h ".str_pad($m, 2, '0', STR_PAD_LEFT).'m';
        }

        $checkInUrl = AttendanceCheckIn::getUrl(panel: 'app');

        return [
            Stat::make('Entrada', $fmt($log?->time_in))
                ->icon('heroicon-m-arrow-right-circle')
                ->color($log?->time_in ? 'success' : 'gray')
                ->url($checkInUrl),

            Stat::make('Almoço', $lunch)
                ->icon('heroicon-m-pause-circle')
                ->color('warning'),

            Stat::make('Saída', $fmt($log?->time_out))
                ->icon('heroicon-m-arrow-left-circle')
                ->color($log?->time_out ? 'danger' : 'gray'),

            Stat::make('Tempo Trabalhado', $worked)
                ->icon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
