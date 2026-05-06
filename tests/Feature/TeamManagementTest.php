<?php

namespace Tests\Feature;

use App\Filament\Widgets\TeamPendingRequestsWidget;
use App\Models\Employee;
use App\Models\LeaveAndAbsence;
use App\Models\Unit;
use App\Models\Vacation;
use App\Notifications\RequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacation_approval_triggers_notification()
    {
        Notification::fake();

        $unit = Unit::factory()->create();

        $manager = Employee::factory()->create(['unit_id' => $unit->id]);
        $managerUser = $manager->user;

        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
        $employeeUser = $employee->user;

        $vacation = Vacation::create([
            'employee_id' => $employee->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
            'status' => 'pending',
        ]);

        $this->actingAs($managerUser);

        $vacation->update(['status' => 'approved']);

        Notification::assertSentTo(
            $employeeUser,
            RequestStatusNotification::class,
            function ($notification) use ($employeeUser) {
                return $notification->toArray($employeeUser)['status'] === 'approved';
            }
        );
    }

    public function test_vacation_rejection_triggers_notification_with_reason()
    {
        Notification::fake();

        $unit = Unit::factory()->create();
        $manager = Employee::factory()->create(['unit_id' => $unit->id]);
        $managerUser = $manager->user;

        $employee = Employee::factory()->create(['unit_id' => $unit->id]);
        $employeeUser = $employee->user;

        $vacation = Vacation::create([
            'employee_id' => $employee->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
            'status' => 'pending',
        ]);

        $this->actingAs($managerUser);

        $vacation->update([
            'status' => 'rejected',
            'rejection_reason' => 'Necessidades de serviço',
        ]);

        Notification::assertSentTo(
            $employeeUser,
            RequestStatusNotification::class,
            function ($notification) use ($employeeUser) {
                $data = $notification->toArray($employeeUser);

                return $data['status'] === 'rejected' && str_contains($data['message'], 'Necessidades de serviço');
            }
        );
    }

    public function test_manager_can_manage_only_subordinate_employees(): void
    {
        $manager = Employee::factory()->create();

        $parentUnit = Unit::factory()->create(['manager_id' => $manager->id]);
        $childUnit = Unit::factory()->create(['parent_id' => $parentUnit->id]);
        $outsideUnit = Unit::factory()->create();

        $directSubordinate = Employee::factory()->create(['unit_id' => $parentUnit->id]);
        $descendantSubordinate = Employee::factory()->create(['unit_id' => $childUnit->id]);
        $outsideEmployee = Employee::factory()->create(['unit_id' => $outsideUnit->id]);

        $this->assertTrue($manager->canManageEmployeeId($directSubordinate->id));
        $this->assertTrue($manager->canManageEmployeeId($descendantSubordinate->id));
        $this->assertFalse($manager->canManageEmployeeId($outsideEmployee->id));
    }

    public function test_team_pending_requests_widget_generates_unique_row_keys_for_union_rows(): void
    {
        $manager = Employee::factory()->create();
        $managerUser = $manager->user;
        $unit = Unit::factory()->create(['manager_id' => $manager->id]);

        $employee = Employee::factory()->create(['unit_id' => $unit->id]);

        DB::table('vacations')->insert([
            'id' => 901,
            'employee_id' => $employee->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(6),
            'year_reference' => now()->year,
            'days_taken' => 2,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leaves_and_absences')->insert([
            'id' => 901,
            'employee_id' => $employee->id,
            'type' => 'sick_leave',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'reason' => 'Motivo de teste',
            'is_paid' => true,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($managerUser);

        $rowRecords = Vacation::query()
            ->select([
                'id',
                'employee_id',
                DB::raw("'App\\\\Models\\\\Vacation' as model_type"),
                DB::raw("CONCAT('App\\\\\\\\Models\\\\\\\\Vacation', ':', id) as row_key"),
            ])
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->union(
                LeaveAndAbsence::query()->select([
                    'id',
                    'employee_id',
                    DB::raw("'App\\\\Models\\\\LeaveAndAbsence' as model_type"),
                    DB::raw("CONCAT('App\\\\\\\\Models\\\\\\\\LeaveAndAbsence', ':', id) as row_key"),
                ])->where('employee_id', $employee->id)->where('status', 'pending')
            )
            ->get();

        $widget = app(TeamPendingRequestsWidget::class);
        $keys = $rowRecords->map(fn ($record) => $widget->getTableRecordKey($record))->all();

        $this->assertCount(2, array_unique($keys));
        $this->assertCount(2, array_filter($keys, fn ($key) => str_ends_with($key, ':901')));
    }
}
