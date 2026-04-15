<?php

namespace Tests\Feature\Widgets;

use App\Filament\Widgets\AbsenceReasonChart;
use App\Filament\Widgets\ContractExpirationsStat;
use App\Filament\Widgets\ContractTypeChart;
use App\Filament\Widgets\DailyAbsenceStat;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\EmployeeStatsWidget;
use App\Filament\Widgets\SalaryByLevelStat;
use App\Filament\Widgets\TotalPayrollStat;
use App\Filament\Widgets\UnitDensityChart;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    #[Test]
    public function all_widgets_are_hidden_without_authentication(): void
    {
        $this->assertFalse(TotalPayrollStat::canView());
        $this->assertFalse(ContractExpirationsStat::canView());
        $this->assertFalse(ContractTypeChart::canView());
        $this->assertFalse(UnitDensityChart::canView());
        $this->assertFalse(SalaryByLevelStat::canView());
        $this->assertFalse(EmployeesByUnitChart::canView());
        $this->assertFalse(EmployeeStatsWidget::canView());
        $this->assertFalse(DailyAbsenceStat::canView());
        $this->assertFalse(AbsenceReasonChart::canView());
    }

    #[Test]
    public function total_payroll_stat_widget_instantiates(): void
    {
        $widget = new TotalPayrollStat;
        $this->assertInstanceOf(TotalPayrollStat::class, $widget);
    }

    #[Test]
    public function contract_expirations_stat_widget_instantiates(): void
    {
        $widget = new ContractExpirationsStat;
        $this->assertInstanceOf(ContractExpirationsStat::class, $widget);
    }

    #[Test]
    public function contract_type_chart_widget_instantiates(): void
    {
        $widget = new ContractTypeChart;
        $this->assertInstanceOf(ContractTypeChart::class, $widget);
    }

    #[Test]
    public function unit_density_chart_widget_instantiates(): void
    {
        $widget = new UnitDensityChart;
        $this->assertInstanceOf(UnitDensityChart::class, $widget);
    }

    #[Test]
    public function salary_by_level_stat_widget_instantiates(): void
    {
        $widget = new SalaryByLevelStat;
        $this->assertInstanceOf(SalaryByLevelStat::class, $widget);
    }

    #[Test]
    public function employees_by_unit_chart_widget_instantiates(): void
    {
        $widget = new EmployeesByUnitChart;
        $this->assertInstanceOf(EmployeesByUnitChart::class, $widget);
    }

    #[Test]
    public function employee_stats_widget_instantiates(): void
    {
        $widget = new EmployeeStatsWidget;
        $this->assertInstanceOf(EmployeeStatsWidget::class, $widget);
    }

    #[Test]
    public function daily_absence_stat_widget_instantiates(): void
    {
        $widget = new DailyAbsenceStat;
        $this->assertInstanceOf(DailyAbsenceStat::class, $widget);
    }

    #[Test]
    public function absence_reason_chart_widget_instantiates(): void
    {
        $widget = new AbsenceReasonChart;
        $this->assertInstanceOf(AbsenceReasonChart::class, $widget);
    }
}
