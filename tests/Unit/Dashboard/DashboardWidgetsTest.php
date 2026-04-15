<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AttendanceOverviewChart;
use App\Filament\Widgets\ContractStatsWidget;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\EmployeeStatsWidget;

test('dashboard page exists', function () {
    $dashboard = new Dashboard;
    expect($dashboard)->toBeInstanceOf(Dashboard::class);
});

test('dashboard has all required widgets registered', function () {
    $dashboard = new Dashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)->toContain(
        EmployeeStatsWidget::class,
        ContractStatsWidget::class,
        EmployeesByUnitChart::class,
        AttendanceOverviewChart::class,
    );
});

test('employee stats widget can be instantiated', function () {
    $widget = new EmployeeStatsWidget;
    expect($widget)->toBeInstanceOf(EmployeeStatsWidget::class);
});

test('contract stats widget can be instantiated', function () {
    $widget = new ContractStatsWidget;
    expect($widget)->toBeInstanceOf(ContractStatsWidget::class);
});

test('employees by unit chart widget can be instantiated', function () {
    $widget = new EmployeesByUnitChart;
    expect($widget)->toBeInstanceOf(EmployeesByUnitChart::class);
});

test('attendance overview chart widget can be instantiated', function () {
    $widget = new AttendanceOverviewChart;
    expect($widget)->toBeInstanceOf(AttendanceOverviewChart::class);
});

test('dashboard columns are configured for responsive layout', function () {
    $dashboard = new Dashboard;
    $columns = $dashboard->getColumns();

    expect($columns)->toBe([
        'default' => 1,
        'md' => 2,
        'xl' => 3,
    ]);
});
