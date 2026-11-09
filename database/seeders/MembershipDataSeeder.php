<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MembershipDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sections
        $sections = [
            [
                'name' => 'Dining',
                'slug' => 'dining',
                'description' => 'Access to club dining facilities and members\' menu',
                'fee' => 15000, // £150
                'recurring_annually' => true,
                'active' => true,
            ],
            [
                'name' => 'Events',
                'slug' => 'events',
                'description' => 'Preferential booking and pricing for club events',
                'fee' => 10000, // £100
                'recurring_annually' => true,
                'active' => true,
            ],
            [
                'name' => 'Meeting Rooms',
                'slug' => 'meeting-rooms',
                'description' => 'Member rates on meeting room hire',
                'fee' => 20000, // £200
                'recurring_annually' => true,
                'active' => true,
            ],
            [
                'name' => 'Library',
                'slug' => 'library',
                'description' => 'Access to the club library and reading room',
                'fee' => 8000, // £80
                'recurring_annually' => true,
                'active' => true,
            ],
            [
                'name' => 'Reciprocal',
                'slug' => 'reciprocal',
                'description' => 'Access to partner clubs in the reciprocal network',
                'fee' => 12000, // £120
                'recurring_annually' => true,
                'active' => true,
            ],
        ];

        foreach ($sections as $data) {
            Section::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // Create membership categories
        $categories = [
            [
                'name' => 'Full Membership',
                'slug' => 'full',
                'description' => 'Complete access to all club facilities and sections',
                'benefits' => [
                    'Unrestricted access to all club facilities',
                    'Member rates on all bookings and events',
                    'Voting rights at AGM',
                    'Eligibility for committee positions',
                ],
                'entrance_fee' => 100000, // £1,000
                'annual_fee' => 120000, // £1,200
                'monthly_fee' => 11000, // £110
                'min_sections' => 2,
                'nominee_limit' => 0,
                'active' => true,
            ],
            [
                'name' => 'Associate Membership',
                'slug' => 'associate',
                'description' => 'Core membership with flexible section choices',
                'benefits' => [
                    'Choose your sections',
                    'Member rates on bookings and events',
                    'Bring guests',
                ],
                'entrance_fee' => 50000, // £500
                'annual_fee' => 80000, // £800
                'monthly_fee' => 7500, // £75
                'min_sections' => 2,
                'nominee_limit' => 0,
                'active' => true,
            ],
            [
                'name' => 'Young Professional',
                'slug' => 'young-professional',
                'description' => 'For members under 35 years old',
                'benefits' => [
                    'Reduced membership fees',
                    'Core facilities access',
                    'Networking events',
                ],
                'entrance_fee' => 25000, // £250
                'annual_fee' => 50000, // £500
                'monthly_fee' => 4500, // £45
                'min_sections' => 2,
                'nominee_limit' => 0,
                'active' => true,
            ],
        ];

        foreach ($categories as $data) {
            $category = MembershipCategory::firstOrCreate(['slug' => $data['slug']], $data);

            // Attach all sections to each category
            $sectionIds = Section::pluck('id');
            $category->sections()->syncWithoutDetaching($sectionIds);
        }

        $this->command->info('Membership categories and sections seeded.');
    }
}
