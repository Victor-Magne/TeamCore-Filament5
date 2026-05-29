<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AbsenceReasonChart;
use App\Filament\Widgets\AttendanceOverviewChart;
use App\Filament\Widgets\ContractExpirationsStat;
use App\Filament\Widgets\ContractStatsWidget;
use App\Filament\Widgets\ContractTypeChart;
use App\Filament\Widgets\DailyAbsenceStat;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\EmployeeStatsWidget;
use App\Filament\Widgets\SalaryByLevelStat;
use App\Filament\Widgets\TotalPayrollStat;
use App\Filament\Widgets\UnitDensityChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home';
    }

    public function getWidgets(): array
    {
        return [
            EmployeeStatsWidget::class,
            ContractStatsWidget::class,
            ContractExpirationsStat::class,
            EmployeesByUnitChart::class,
            AttendanceOverviewChart::class,
            TotalPayrollStat::class,
            ContractTypeChart::class,
            UnitDensityChart::class,
            SalaryByLevelStat::class,
            DailyAbsenceStat::class,
            AbsenceReasonChart::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 'full',
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->persistTabInQueryString()
                ->schema([
                    Tab::make('Colaboradores')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Grid::make(['md' => 2, 'xl' => 3])
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    EmployeeStatsWidget::class,
                                    EmployeesByUnitChart::class,
                                    UnitDensityChart::class,
                                ])),
                        ]),

                    Tab::make('Contratos')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Grid::make(['md' => 2, 'xl' => 3])
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    ContractStatsWidget::class,
                                    ContractExpirationsStat::class,
                                    ContractTypeChart::class,
                                ])),
                        ]),

                    Tab::make('Assiduidade')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Grid::make(['md' => 2, 'xl' => 3])
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    AttendanceOverviewChart::class,
                                    DailyAbsenceStat::class,
                                    AbsenceReasonChart::class,
                                ])),
                        ]),

                    Tab::make('Remuneração')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Grid::make(['md' => 2, 'xl' => 3])
                                ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                                    TotalPayrollStat::class,
                                    SalaryByLevelStat::class,
                                ])),
                        ]),
                ]),
        ]);
    }
}
