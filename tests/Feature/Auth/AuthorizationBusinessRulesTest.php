<?php

use App\Filament\App\Pages\EmployeeDashboard;
use App\Filament\Pages\AttendanceCheckIn;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\EmployeeInfoWidget;
use App\Filament\Widgets\EmployeesByUnitChart;
use App\Filament\Widgets\HourBankStatsWidget;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacation;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function grant(User $user, array $permissions): void
{
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);
}

test('hierarchical employee policy allows subordinate access but blocks unrelated records', function () {
    $parentUnit = Unit::factory()->mainDirection()->create();
    $childUnit = Unit::factory()->department()->create(['parent_id' => $parentUnit->id]);
    $otherUnit = Unit::factory()->department()->create();

    $managerEmployee = Employee::factory()->create(['unit_id' => $parentUnit->id]);
    $subordinateEmployee = Employee::factory()->create(['unit_id' => $childUnit->id]);
    $outsiderEmployee = Employee::factory()->create(['unit_id' => $otherUnit->id]);

    // Define manager
    $childUnit->update(['manager_id' => $managerEmployee->id]);

    $managerUser = User::where('employee_id', $managerEmployee->id)->firstOrFail();

    grant($managerUser, [
        'View:Employee',
        'Scope:View:Subordinates',
    ]);

    expect(Gate::forUser($managerUser)->allows('view', $managerEmployee))->toBeTrue();
    expect(Gate::forUser($managerUser)->allows('view', $subordinateEmployee))->toBeTrue();
    expect(Gate::forUser($managerUser)->allows('view', $outsiderEmployee))->toBeFalse();
});

test('hierarchical vacation policy respects subordinate scope', function () {
    $parentUnit = Unit::factory()->mainDirection()->create();
    $childUnit = Unit::factory()->department()->create(['parent_id' => $parentUnit->id]);
    $otherUnit = Unit::factory()->department()->create();

    $managerEmployee = Employee::factory()->create(['unit_id' => $parentUnit->id]);
    $subordinateEmployee = Employee::factory()->create(['unit_id' => $childUnit->id]);
    $outsiderEmployee = Employee::factory()->create(['unit_id' => $otherUnit->id]);

    // Define manager
    $childUnit->update(['manager_id' => $managerEmployee->id]);

    $subordinateVacation = Vacation::factory()->for($subordinateEmployee)->create();
    $outsiderVacation = Vacation::factory()->for($outsiderEmployee)->create();

    $managerUser = User::where('employee_id', $managerEmployee->id)->firstOrFail();

    grant($managerUser, [
        'View:Vacation',
        'Scope:View:Subordinates',
    ]);

    expect(Gate::forUser($managerUser)->allows('view', $subordinateVacation))->toBeTrue();
    expect(Gate::forUser($managerUser)->allows('view', $outsiderVacation))->toBeFalse();
});

test('user role assignment is blocked without role management permission and strips super admin for regular role managers', function () {
    $editor = User::factory()->create();
    $roleManager = User::factory()->create();

    $superAdminRole = Role::findOrCreate('super_admin', 'web');
    $staffRole = Role::findOrCreate('staff', 'web');

    grant($editor, ['Update:User']);
    grant($roleManager, ['Update:User', 'Update:Role']);

    $this->actingAs($editor);
    expect(UserResource::canManageRoles())->toBeFalse();
    expect(UserResource::sanitizeRoleIds([$superAdminRole->id, $staffRole->id]))->toBe([]);

    $this->actingAs($roleManager);
    expect(UserResource::canManageRoles())->toBeTrue();
    expect(UserResource::getAssignableRoleIds())->toContain($staffRole->id)->not->toContain($superAdminRole->id);
    expect(UserResource::sanitizeRoleIds([$superAdminRole->id, $staffRole->id]))->toBe([$staffRole->id]);
});

test('app panel pages and widgets require their explicit shield permissions', function () {
    $employee = Employee::factory()->create();
    $user = User::where('employee_id', $employee->id)->firstOrFail();

    grant($user, [
        'View:Dashboard',
        'View:AttendanceCheckIn',
        'View:EmployeeInfoWidget',
        'View:HourBankStatsWidget',
        'View:EmployeesByUnitChart',
    ]);

    $this->actingAs($user);

    expect(EmployeeDashboard::canAccess())->toBeTrue();
    expect(AttendanceCheckIn::canAccess())->toBeTrue();
    expect(EmployeeInfoWidget::canView())->toBeTrue();
    expect(HourBankStatsWidget::canView())->toBeTrue();
    expect(EmployeesByUnitChart::canView())->toBeTrue();
});
