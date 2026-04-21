<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Absence","View:Absence","Create:Absence","Update:Absence","Delete:Absence","DeleteAny:Absence","Restore:Absence","ForceDelete:Absence","ForceDeleteAny:Absence","RestoreAny:Absence","Replicate:Absence","Reorder:Absence","ViewAny:ActivityLog","View:ActivityLog","Create:ActivityLog","Update:ActivityLog","Delete:ActivityLog","DeleteAny:ActivityLog","Restore:ActivityLog","ForceDelete:ActivityLog","ForceDeleteAny:ActivityLog","RestoreAny:ActivityLog","Replicate:ActivityLog","Reorder:ActivityLog","ViewAny:AttendanceLog","View:AttendanceLog","Create:AttendanceLog","Update:AttendanceLog","Delete:AttendanceLog","DeleteAny:AttendanceLog","Restore:AttendanceLog","ForceDelete:AttendanceLog","ForceDeleteAny:AttendanceLog","RestoreAny:AttendanceLog","Replicate:AttendanceLog","Reorder:AttendanceLog","ViewAny:City","View:City","Create:City","Update:City","Delete:City","DeleteAny:City","Restore:City","ForceDelete:City","ForceDeleteAny:City","RestoreAny:City","Replicate:City","Reorder:City","ViewAny:Contract","View:Contract","Create:Contract","Update:Contract","Delete:Contract","DeleteAny:Contract","Restore:Contract","ForceDelete:Contract","ForceDeleteAny:Contract","RestoreAny:Contract","Replicate:Contract","Reorder:Contract","ViewAny:Country","View:Country","Create:Country","Update:Country","Delete:Country","DeleteAny:Country","Restore:Country","ForceDelete:Country","ForceDeleteAny:Country","RestoreAny:Country","Replicate:Country","Reorder:Country","ViewAny:Designation","View:Designation","Create:Designation","Update:Designation","Delete:Designation","DeleteAny:Designation","Restore:Designation","ForceDelete:Designation","ForceDeleteAny:Designation","RestoreAny:Designation","Replicate:Designation","Reorder:Designation","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","DeleteAny:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee","ViewAny:HourBank","View:HourBank","Create:HourBank","Update:HourBank","Delete:HourBank","DeleteAny:HourBank","Restore:HourBank","ForceDelete:HourBank","ForceDeleteAny:HourBank","RestoreAny:HourBank","Replicate:HourBank","Reorder:HourBank","ViewAny:LeaveAndAbsence","View:LeaveAndAbsence","Create:LeaveAndAbsence","Update:LeaveAndAbsence","Delete:LeaveAndAbsence","DeleteAny:LeaveAndAbsence","Restore:LeaveAndAbsence","ForceDelete:LeaveAndAbsence","ForceDeleteAny:LeaveAndAbsence","RestoreAny:LeaveAndAbsence","Replicate:LeaveAndAbsence","Reorder:LeaveAndAbsence","ViewAny:Payroll","View:Payroll","Create:Payroll","Update:Payroll","Delete:Payroll","DeleteAny:Payroll","Restore:Payroll","ForceDelete:Payroll","ForceDeleteAny:Payroll","RestoreAny:Payroll","Replicate:Payroll","Reorder:Payroll","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","DeleteAny:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:State","View:State","Create:State","Update:State","Delete:State","DeleteAny:State","Restore:State","ForceDelete:State","ForceDeleteAny:State","RestoreAny:State","Replicate:State","Reorder:State","ViewAny:Unit","View:Unit","Create:Unit","Update:Unit","Delete:Unit","DeleteAny:Unit","Restore:Unit","ForceDelete:Unit","ForceDeleteAny:Unit","RestoreAny:Unit","Replicate:Unit","Reorder:Unit","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Vacation","View:Vacation","Create:Vacation","Update:Vacation","Delete:Vacation","DeleteAny:Vacation","Restore:Vacation","ForceDelete:Vacation","ForceDeleteAny:Vacation","RestoreAny:Vacation","Replicate:Vacation","Reorder:Vacation","View:MyProfilePage","View:AttendanceCheckIn","View:Dashboard","View:EmployeeDashboard","View:EmployeeActionsWidget","View:EmployeeContractWidget","View:EmployeeInfoWidget","View:EmployeeLeaveWidget","View:EmployeeVacationWidget","View:SalaryByLevelStat","View:HourBankStatsWidget","View:TotalPayrollStat","View:EmployeeStatsWidget","View:EmployeesByUnitChart","View:UpcomingBirthdaysWidget","View:DailyAbsenceStat","View:UnitDensityChart","View:AbsenceReasonChart","View:AttendanceOverviewChart","View:ContractStatsWidget","View:ContractExpirationsStat","View:ContractTypeChart","Access:AdminPanel","Access:AppPanel","Scope:ViewDeptHeads","Scope:ViewSectionChiefs","Scope:ViewBaseEmployees"]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
