<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_any_booking',
            'approve_booking',
            'view_any_application',
            'review_application',
            'decide_application',
            'manage_members',
            'manage_settings',
            'view_audit_logs',
            'manage_content',
            'manage_invoices',
            'issue_refunds',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->givePermissionTo([
            'view_any_booking',
            'view_any_application',
        ]);

        $committee = Role::firstOrCreate(['name' => 'committee', 'guard_name' => 'web']);
        $committee->givePermissionTo([
            'view_any_application',
            'review_application',
        ]);

        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        $this->command->info('Roles and permissions seeded.');
    }
}
