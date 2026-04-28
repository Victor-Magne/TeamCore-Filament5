<?php

namespace Tests\Feature\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckDailyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 4, 21, 10, 0, 0)); // Uma terça-feira
    }

    public function test_it_registers_absence_for_missing_attendance_log()
    {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::yesterday()->subDays(10),
            'daily_work_minutes' => 480,
        ]);

        $yesterday = Carbon::yesterday()->toDateString();

        $this->artisan("app:check-daily-attendance $yesterday")
            ->assertSuccessful();

        $this->assertDatabaseHas('absences', [
            'employee_id' => $employee->id,
            'hours_deducted' => 480,
            'deduction_type' => 'unjustified_absence',
        ]);
    }

    public function test_it_does_not_register_absence_if_attendance_log_exists()
    {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
        ]);

        $yesterday = Carbon::yesterday();
        AttendanceLog::create([
            'employee_id' => $employee->id,
            'time_in' => $yesterday->copy()->setHour(9, 0),
        ]);

        $this->artisan("app:check-daily-attendance {$yesterday->toDateString()}")
            ->assertSuccessful();

        $this->assertDatabaseMissing('absences', [
            'employee_id' => $employee->id,
        ]);
    }

    public function test_it_respects_vacations()
    {
        $employee = Employee::factory()->create(['vacation_balance' => 22]);
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
            'start_date' => Carbon::create(2026, 4, 1),
        ]);

        $yesterday = Carbon::create(2026, 4, 20); // Segunda

        // Criar férias DIRETAMENTE via SQL para evitar hooks e o observer que cria usuário
        \Illuminate\Support\Facades\DB::table('vacations')->insert([
            'employee_id' => $employee->id,
            'start_date' => $yesterday->toDateString(),
            'end_date' => $yesterday->toDateString(),
            'status' => 'approved',
            'year_reference' => 2026,
            'days_taken' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan("app:check-daily-attendance {$yesterday->toDateString()}")
            ->assertSuccessful();

        $this->assertDatabaseMissing('absences', [
            'employee_id' => $employee->id,
        ]);
    }

    public function test_it_respects_weekends()
    {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'active',
        ]);

        $sunday = Carbon::parse('2026-04-19'); // Domingo

        $this->artisan("app:check-daily-attendance {$sunday->toDateString()}")
            ->assertSuccessful();

        $this->assertDatabaseMissing('absences', [
            'employee_id' => $employee->id,
        ]);
    }
}
