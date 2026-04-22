<?php

namespace App\Filament\Pages;

use App\Models\AttendanceLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AttendanceCheckIn extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected string $view = 'filament.pages.attendance-check-in';
    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Pessoal';

    // Propriedades públicas para sincronização reativa com Alpine.js
    public ?int $timeIn = null;
    public ?int $lunchStart = null;
    public ?int $lunchEnd = null;
    public ?int $timeOut = null;
    public int $serverTimestamp;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->employee_id) {
            $this->redirect(config('filament.path'));
            return;
        }
        $this->refreshTimestamps();
    }

    /**
     * Atualiza os valores das propriedades com o que está na BD
     */
    public function refreshTimestamps(): void
    {
        $log = AttendanceLog::where('employee_id', Auth::user()->employee_id)
            ->whereDate('time_in', Carbon::today())
            ->first();

        $this->timeIn = $log?->time_in?->timestamp;
        $this->lunchStart = $log?->lunch_break_start?->timestamp;
        $this->lunchEnd = $log?->lunch_break_end?->timestamp;
        $this->timeOut = $log?->time_out?->timestamp;
        $this->serverTimestamp = now()->timestamp;
    }

    public function checkInAction(): Action
    {
        return Action::make('checkIn')
            ->label($this->getCheckInLabel())
            ->color($this->getCheckInColor())
            ->icon($this->getCheckInIcon())
            ->size('xl')
            ->requiresConfirmation()
            ->action(function () {
                $user = Auth::user();
                $now = Carbon::now();
                $log = AttendanceLog::where('employee_id', $user->employee_id)
                    ->whereDate('time_in', Carbon::today())
                    ->first();

                if (! $log) {
                    AttendanceLog::create(['employee_id' => $user->employee_id, 'time_in' => $now]);
                } elseif (! $log->lunch_break_start) {
                    $log->update(['lunch_break_start' => $now]);
                } elseif (! $log->lunch_break_end) {
                    $log->update(['lunch_break_end' => $now]);
                } elseif (! $log->time_out) {
                    $log->update(['time_out' => $now]);
                }

                $this->refreshTimestamps(); // Atualiza os dados para o JS
                Notification::make()->title('Registo efetuado')->success()->send();
            });
    }

    public function getCheckInLabel(): string
    {
        if (!$this->timeIn) return __('widgets.attendance.entry');
        if (!$this->lunchStart) return __('widgets.attendance.lunch_start');
        if (!$this->lunchEnd) return __('widgets.attendance.lunch_end');
        if (!$this->timeOut) return __('widgets.attendance.exit');
        return __('widgets.attendance.completed');
    }

    protected function getCheckInColor(): string
    {
        $label = $this->getCheckInLabel();
        return match ($label) {
            __('widgets.attendance.entry') => 'success',
            __('widgets.attendance.lunch_start') => 'warning',
            __('widgets.attendance.lunch_end') => 'info',
            __('widgets.attendance.exit') => 'danger',
            default => 'gray',
        };
    }

    protected function getCheckInIcon(): string
    {
        $label = $this->getCheckInLabel();
        return match ($label) {
            __('widgets.attendance.entry') => 'heroicon-m-arrow-right-end-on-rectangle',
            __('widgets.attendance.lunch_start') => 'heroicon-m-cake',
            __('widgets.attendance.lunch_end') => 'heroicon-m-briefcase',
            __('widgets.attendance.exit') => 'heroicon-m-arrow-left-start-on-rectangle',
            default => 'heroicon-m-check-circle',
        };
    }
}
