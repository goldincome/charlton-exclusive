<?php

namespace Database\Seeders;

use App\Models\Hall;
use App\Models\HallFeature;
use App\Models\HallHour;
use App\Models\RoomRate;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    public function run(): void
    {
        $hall = Hall::firstOrCreate(
            ['slug' => 'boardroom'],
            [
                'name' => 'The Boardroom',
                'description' => 'Our flagship meeting space with panoramic views and integrated presentation technology.',
                'capacity' => 20,
                'capacity_seated' => 18,
                'area' => 'Charlton',
                'buffer_minutes' => 30,
                'min_hours' => 3,
                'active' => true,
                'seo_title' => 'The Boardroom - Meeting Room Hire in Charlton',
                'seo_description' => 'Book The Boardroom for your next meeting. Seats 20, minimum 3 hours. Member rates available.',
            ]
        );

        // Add features
        $features = [
            '4K presentation display',
            'Video conferencing equipment',
            'High-speed Wi-Fi',
            'Whiteboard and flip charts',
            'Natural daylight',
            'Climate control',
            'Complimentary tea and coffee',
        ];

        foreach ($features as $feature) {
            HallFeature::firstOrCreate([
                'hall_id' => $hall->id,
                'name' => $feature,
            ]);
        }

        // Add opening hours (Mon-Fri 08:00-22:00, Sat-Sun 09:00-18:00)
        $hours = [
            ['weekday' => 1, 'opens_at' => '08:00:00', 'closes_at' => '22:00:00'], // Monday
            ['weekday' => 2, 'opens_at' => '08:00:00', 'closes_at' => '22:00:00'], // Tuesday
            ['weekday' => 3, 'opens_at' => '08:00:00', 'closes_at' => '22:00:00'], // Wednesday
            ['weekday' => 4, 'opens_at' => '08:00:00', 'closes_at' => '22:00:00'], // Thursday
            ['weekday' => 5, 'opens_at' => '08:00:00', 'closes_at' => '22:00:00'], // Friday
            ['weekday' => 6, 'opens_at' => '09:00:00', 'closes_at' => '18:00:00'], // Saturday
            ['weekday' => 0, 'opens_at' => '09:00:00', 'closes_at' => '18:00:00'], // Sunday
        ];

        foreach ($hours as $hour) {
            HallHour::firstOrCreate([
                'hall_id' => $hall->id,
                'weekday' => $hour['weekday'],
            ], $hour);
        }

        // Add rates (Mon-Thu £120/h, Fri £90/h, Sat-Sun £150/h)
        $rates = [
            ['day_type' => 'Mon', 'standard_price_per_hour' => 12000],
            ['day_type' => 'Tue', 'standard_price_per_hour' => 12000],
            ['day_type' => 'Wed', 'standard_price_per_hour' => 12000],
            ['day_type' => 'Thu', 'standard_price_per_hour' => 12000],
            ['day_type' => 'Fri', 'standard_price_per_hour' => 9000],
            ['day_type' => 'Sat', 'standard_price_per_hour' => 15000],
            ['day_type' => 'Sun', 'standard_price_per_hour' => 15000],
            ['day_type' => 'BankHoliday', 'standard_price_per_hour' => 18000],
        ];

        foreach ($rates as $rate) {
            RoomRate::updateOrCreate(
                ['hall_id' => $hall->id, 'day_type' => $rate['day_type']],
                $rate
            );
        }

        $this->command->info('Sample hall (Boardroom) seeded with features, hours and rates.');
    }
}
