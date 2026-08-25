<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define distinct modular permissions
        $permissions = [
            // Student Management
            'view students',
            'create students',
            'edit students',
            'manage student lifecycle', // active/inactive
            
            // Class & Assignment Management
            'view classes',
            'manage classes',
            'assign student classes',
            'assign attendance roles',

            // Attendance Operations
            'view attendance sessions',
            'manage attendance sessions', // create/edit config
            'record attendance', // Used by mobile app
            'override closed attendance', // HR only
            
            // Reports
            'view attendance reports',
            'export attendance reports',
            
            // System Administration
            'manage users',
            'manage departments',
            'manage roles',
            'view audit logs'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Create Roles and Assign Permissions

        // System Admin (God Mode for IT/Developers)
        $adminRole = Role::firstOrCreate(['name' => 'System Administrator']);
        $adminRole->givePermissionTo(Permission::all());

        // HR Leader (Full Business Control)
        $hrLeader = Role::firstOrCreate(['name' => 'HR Leader']);
        $hrLeader->givePermissionTo([
            'view students', 'create students', 'edit students', 'manage student lifecycle',
            'view classes', 'manage classes', 'assign student classes', 'assign attendance roles',
            'view attendance sessions', 'manage attendance sessions', 'override closed attendance',
            'view attendance reports', 'export attendance reports',
            'manage users', 'manage departments'
        ]);

        // HR Assistant Leader (Same as Leader initially, per requirements)
        $hrAssistant = Role::firstOrCreate(['name' => 'HR Assistant Leader']);
        $hrAssistant->syncPermissions($hrLeader->permissions);

        // HR Writer (Same as Leader initially, per requirements)
        $hrWriter = Role::firstOrCreate(['name' => 'HR Writer']);
        $hrWriter->syncPermissions($hrLeader->permissions);

        // Attendance Member (Restricted to their assigned classes via policy later)
        $attendanceMember = Role::firstOrCreate(['name' => 'Attendance Member']);
        $attendanceMember->givePermissionTo([
            'view students',
            'view classes',
            'view attendance sessions',
            'record attendance'
        ]);

        // Student Manager
        $studentManager = Role::firstOrCreate(['name' => 'Student Manager']);
        $studentManager->givePermissionTo([
            'view students', 'create students', 'edit students', 'manage student lifecycle',
            'view classes', 'assign student classes'
        ]);
    }
}