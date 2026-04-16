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

    public static function getNavigationLabel(): string
    {
        return __('widgets.attendance.check_in');
    }

    public function getTitle(): string
    {
        return __('widgets.attendance.check_in');
    }

    protected static string|UnitEnum|null $navigationGroup = 'Gestão de Pessoal';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->employee_id) {
            Notification::make()
                ->title('Erro')
                ->body(__('widgets.attendance.not_associated'))
                ->danger()
                ->send();

            $this->redirect(config('filament.path'));
        }
    }

    public function checkInAction(): Action
    {
        return Action::make('checkIn')
            ->label($this->getCheckInLabel())
            ->color('primary')
            ->requiresConfirmation()
            ->action(function () {
                $user = Auth::user();
                if (! $user || ! $user->employee_id) {
                    return;
                }

                $today = Carbon::today();
                $log = AttendanceLog::where('employee_id', $user->employee_id)
                    ->whereDate('time_in', $today)
                    ->first();

                $now = Carbon::now();

                if (! $log) {
                    AttendanceLog::create([
                        'employee_id' => $user->employee_id,
                        'time_in' => $now,
                    ]);
                    $message = __('widgets.attendance.success_entry');
                } elseif (! $log->lunch_break_start) {
                    $log->update(['lunch_break_start' => $now]);
                    $message = __('widgets.attendance.success_lunch_start');
                } elseif (! $log->lunch_break_end) {
                    $log->update(['lunch_break_end' => $now]);
                    $message = __('widgets.attendance.success_lunch_end');
                } elseif (! $log->time_out) {
                    $log->update(['time_out' => $now]);
                    $message = __('widgets.attendance.success_exit');
                } else {
                    Notification::make()
                        ->title('Aviso')
                        ->body(__('widgets.attendance.already_completed'))
                        ->warning()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Sucesso')
                    ->body($message)
                    ->success()
                    ->send();
            });
    }

    protected function getCheckInLabel(): string
    {
        $user = Auth::user();
        if (! $user || ! $user->employee_id) {
            return 'Indisponível';
        }

        $today = Carbon::today();
        $log = AttendanceLog::where('employee_id', $user->employee_id)
            ->whereDate('time_in', $today)
            ->first();

        if (! $log) {
            return __('widgets.attendance.entry');
        }
        if (! $log->lunch_break_start) {
            return __('widgets.attendance.lunch_start');
        }
        if (! $log->lunch_break_end) {
            return __('widgets.attendance.lunch_end');
        }
        if (! $log->time_out) {
            return __('widgets.attendance.exit');
        }

        return __('widgets.attendance.completed');
    }

    protected function getForms(): array
    {
        return [];
    }
}
