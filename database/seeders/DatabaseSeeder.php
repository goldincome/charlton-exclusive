<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            AppConfigSeeder::class,
            VatRateSeeder::class,
            MembershipDataSeeder::class,
            HallSeeder::class,
            FaqSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Admin login: admin@charltonexclusiveclub.co.uk / password');
        $this->command->info('Test member: member@example.com / password');
        $this->command->info('');
    }
}
