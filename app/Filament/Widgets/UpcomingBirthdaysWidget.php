<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UpcomingBirthdaysWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    protected ?string $pollingInterval = '1h';
    protected int|string|array $columnSpan = [
        'default' => 'flex',
        'md' => 3,
        'xl' => 2,
    ];

        public function getHeading(): ?string
        {
            return 'Aniversariantes Próximos';
        }

        public function getDescription(): ?string
        {
            return __('widgets.upcoming_birthdays_description');
        }


    public function getStats(): array
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = $today->copy()->addDays(30);

        $birthdayCount = Employee::all()->filter(function ($employee) use ($today, $thirtyDaysFromNow) {
            if (!$employee->date_of_birth) return false;

            $birthdayThisYear = $employee->date_of_birth->copy()->year($today->year);
            $birthdayNextYear = $employee->date_of_birth->copy()->year($today->year + 1);

            return ($birthdayThisYear->between($today, $thirtyDaysFromNow)) ||
                   ($birthdayNextYear->between($today, $thirtyDaysFromNow));
        })->count();

        return [
            Stat::make(__('widgets.upcoming_birthdays'), $birthdayCount)
                ->description(__('widgets.upcoming_birthdays_description'))
                ->descriptionIcon('heroicon-m-cake')
                ->color('primary'),
        ];
    }
}
