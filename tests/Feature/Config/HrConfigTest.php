<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HR Config', function () {
    it('has all required keys', function () {
        expect(config('hr.default_daily_work_minutes'))->toBe(480);
        expect(config('hr.default_lunch_minutes'))->toBe(60);
        expect(config('hr.working_days_per_month'))->toBe(22);
        expect(config('hr.delay_tolerance_minutes'))->toBe(15);
        expect(config('hr.full_absence_threshold_minutes'))->toBe(60);
        expect(config('hr.consecutive_delays_limit'))->toBe(3);
        expect(config('hr.extra_hours_multiplier'))->toBe(1.5);
    });

    it('generates correct hourly rate with config values', function () {
        $salary = 1000.0;
        $dailyWorkHours = config('hr.default_daily_work_minutes') / 60; // 8h
        $workingDays = config('hr.working_days_per_month'); // 22

        $hourlyRate = $salary / ($dailyWorkHours * $workingDays);

        expect(round($hourlyRate, 4))->toBe(round(1000 / (8 * 22), 4));
    });

    it('enforces that delay_tolerance_minutes is less than full_absence_threshold_minutes', function () {
        expect(config('hr.delay_tolerance_minutes'))
            ->toBeLessThan(config('hr.full_absence_threshold_minutes'));
    });

    it('enforces that consecutive_delays_limit is at least 2', function () {
        expect(config('hr.consecutive_delays_limit'))->toBeGreaterThanOrEqual(2);
    });

    it('generates correct extra hours amount with multiplier', function () {
        $hourlyRate = 5.0;
        $extraMinutes = 60; // 1 hora extra
        $multiplier = config('hr.extra_hours_multiplier');

        $extraAmount = ($hourlyRate * $multiplier) * ($extraMinutes / 60);

        expect($extraAmount)->toBe(7.5);
    });
});
