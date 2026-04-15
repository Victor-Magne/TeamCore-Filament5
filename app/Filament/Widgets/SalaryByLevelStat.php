<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SalaryByLevelStat extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
        'xl' => 2,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('View:SalaryByLevelStat') ?? false;
    }

    public function getHeading(): ?string
    {
        return 'Estrutura Salarial';
    }

    public function getDescription(): ?string
    {
        return 'Salários médios por nível hierárquico';
    }

    protected function getStats(): array
    {
        // Query otimizada com subqueries ao invés de joins
        $seniorLevel = Contract::where('status', 'active')
            ->whereHas('designation', function ($query) {
                $query->where('level', '>=', 3);
            })
            ->avg('salary') ?? 0;

        $midLevel = Contract::where('status', 'active')
            ->whereHas('designation', function ($query) {
                $query->where('level', 2);
            })
            ->avg('salary') ?? 0;

        $juniorLevel = Contract::where('status', 'active')
            ->whereHas('designation', function ($query) {
                $query->where('level', '<=', 1);
            })
            ->avg('salary') ?? 0;

        return [
            Stat::make('Salarial Superior', '€ '.number_format($seniorLevel, 2, ',', '.'))
                ->description('Nível C/Direção')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
            Stat::make('Salarial Médio', '€ '.number_format($midLevel, 2, ',', '.'))
                ->description('Nível B/Intermédio')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
            Stat::make('Salarial Base', '€ '.number_format($juniorLevel, 2, ',', '.'))
                ->description('Nível A/Operacional')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),
        ];
    }
}
