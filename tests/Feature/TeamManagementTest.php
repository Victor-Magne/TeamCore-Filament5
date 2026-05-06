<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacation;
use App\Notifications\RequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
