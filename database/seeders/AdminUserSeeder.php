<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@charltonexclusiveclub.co.uk'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
        $admin->assignRole('staff');

        $this->command->info('Admin user created: admin@charltonexclusiveclub.co.uk / password');

        // Create a test member
        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Test Member',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $member->assignRole('member');
        $member->memberProfile()->create(['is_financial' => true]);

        $this->command->info('Test member created: member@example.com / password');
    }
}
