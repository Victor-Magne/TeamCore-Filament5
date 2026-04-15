<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[{"id":1,"employee_id":1,"name":"Jo\\u00e3o Silva (Admin)","email":"admin@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$8JKN1fi5jSbFGJUrkZ89AuBCj4OQytwNvLrHXnU0b\\/bhkxsu0elc2","roles":[],"permissions":[]},{"id":2,"employee_id":2,"name":"Maria Santos","email":"maria.santos@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":3,"employee_id":3,"name":"Pedro Costa","email":"pedro.costa@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":4,"employee_id":4,"name":"Victor Sales","email":"victor.sales@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":5,"employee_id":5,"name":"Jefferson Padr\\u00e3o","email":"jefferson.padr\\u00e3o@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":6,"employee_id":6,"name":"Marina Pena","email":"marina.pena@test.test","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":7,"employee_id":12,"name":"Milena Tess\\u00e1lia Zarago\\u00e7a","email":"carvalho.cristiana@example.org","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":8,"employee_id":13,"name":"Cezar In\\u00e1cio Amaral","email":"alexandre98@example.com","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":9,"employee_id":14,"name":"Wagner Carmona Sobrinho","email":"luciana.toledo@example.com","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":10,"employee_id":15,"name":"J\\u00e1como Allan Santos","email":"gsandoval@example.net","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]},{"id":11,"employee_id":16,"name":"Srta. Roberta Ayla Marin Jr.","email":"rafael.zambrano@example.net","email_verified_at":"2026-04-15T16:12:09.000000Z","must_change_password":false,"created_at":"2026-04-15T16:12:09.000000Z","updated_at":"2026-04-15T16:12:09.000000Z","deleted_at":null,"password":"$2y$12$xQvBT8hBbMl4rjXryZEKieT3OyAIpBCcmOqIrgoksS0ItmM8\\/DW76","roles":[],"permissions":[]}]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Absence","View:Absence","Create:Absence","Update:Absence","Delete:Absence","DeleteAny:Absence","Restore:Absence","ForceDelete:Absence","ForceDeleteAny:Absence","RestoreAny:Absence","Replicate:Absence","Reorder:Absence","ViewAny:Activity","View:Activity","Create:Activity","Update:Activity","Delete:Activity","DeleteAny:Activity","Restore:Activity","ForceDelete:Activity","ForceDeleteAny:Activity","RestoreAny:Activity","Replicate:Activity","Reorder:Activity","ViewAny:AttendanceLog","View:AttendanceLog","Create:AttendanceLog","Update:AttendanceLog","Delete:AttendanceLog","DeleteAny:AttendanceLog","Restore:AttendanceLog","ForceDelete:AttendanceLog","ForceDeleteAny:AttendanceLog","RestoreAny:AttendanceLog","Replicate:AttendanceLog","Reorder:AttendanceLog","ViewAny:City","View:City","Create:City","Update:City","Delete:City","DeleteAny:City","Restore:City","ForceDelete:City","ForceDeleteAny:City","RestoreAny:City","Replicate:City","Reorder:City","ViewAny:Contract","View:Contract","Create:Contract","Update:Contract","Delete:Contract","DeleteAny:Contract","Restore:Contract","ForceDelete:Contract","ForceDeleteAny:Contract","RestoreAny:Contract","Replicate:Contract","Reorder:Contract","ViewAny:Country","View:Country","Create:Country","Update:Country","Delete:Country","DeleteAny:Country","Restore:Country","ForceDelete:Country","ForceDeleteAny:Country","RestoreAny:Country","Replicate:Country","Reorder:Country","ViewAny:Designation","View:Designation","Create:Designation","Update:Designation","Delete:Designation","DeleteAny:Designation","Restore:Designation","ForceDelete:Designation","ForceDeleteAny:Designation","RestoreAny:Designation","Replicate:Designation","Reorder:Designation","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","DeleteAny:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee","ViewAny:HourBank","View:HourBank","Create:HourBank","Update:HourBank","Delete:HourBank","DeleteAny:HourBank","Restore:HourBank","ForceDelete:HourBank","ForceDeleteAny:HourBank","RestoreAny:HourBank","Replicate:HourBank","Reorder:HourBank","ViewAny:LeaveAndAbsence","View:LeaveAndAbsence","Create:LeaveAndAbsence","Update:LeaveAndAbsence","Delete:LeaveAndAbsence","DeleteAny:LeaveAndAbsence","Restore:LeaveAndAbsence","ForceDelete:LeaveAndAbsence","ForceDeleteAny:LeaveAndAbsence","RestoreAny:LeaveAndAbsence","Replicate:LeaveAndAbsence","Reorder:LeaveAndAbsence","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","DeleteAny:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:State","View:State","Create:State","Update:State","Delete:State","DeleteAny:State","Restore:State","ForceDelete:State","ForceDeleteAny:State","RestoreAny:State","Replicate:State","Reorder:State","ViewAny:Unit","View:Unit","Create:Unit","Update:Unit","Delete:Unit","DeleteAny:Unit","Restore:Unit","ForceDelete:Unit","ForceDeleteAny:Unit","RestoreAny:Unit","Replicate:Unit","Reorder:Unit","ViewAny:User","View:User","Create:User","Update:User","Delete:User","DeleteAny:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Vacation","View:Vacation","Create:Vacation","Update:Vacation","Delete:Vacation","DeleteAny:Vacation","Restore:Vacation","ForceDelete:Vacation","ForceDeleteAny:Vacation","RestoreAny:Vacation","Replicate:Vacation","Reorder:Vacation","View:MyProfilePage","View:Dashboard","View:SalaryByLevelStat","View:HourBankStatsWidget","View:TotalPayrollStat","View:EmployeeStatsWidget","View:EmployeesByUnitChart","View:DailyAbsenceStat","View:AbsenceReasonChart","View:AttendanceOverviewChart","View:ContractStatsWidget","View:ContractExpirationsStat","View:ContractTypeChart"]}]';
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

        /** @var Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var Model $permissionModel */
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

        /** @var Model $permissionModel */
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
