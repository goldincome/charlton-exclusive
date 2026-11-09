<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'company_number' => '12345678',
            'vat_number' => 'GB123456789',
            'ico_number' => 'ZA123456',
            'booking_request_expiry_hours' => '72',
            'payment_window_hours' => '72',
            'hall_food_discount_type' => 'percent',
            'hall_food_discount_value' => '20',
            'sponsor_cap_default' => '3',
            'allow_training_crawlers' => 'false',
            'travel_notes' => 'Charlton station (Southeastern) 5 minutes walk. Buses 161, 472, 486. Free parking available.',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'refund_tier_1_days' => '7',
            'refund_tier_1_percent' => '100',
            'refund_tier_2_days' => '2',
            'refund_tier_2_percent' => '50',
            'refund_tier_3_percent' => '0',
            'no_show_minutes_after_start' => '30',
        ];

        foreach ($defaults as $key => $value) {
            AppConfig::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->info('App configuration defaults seeded.');
    }
}
