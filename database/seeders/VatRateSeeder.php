<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['product_type' => 'hall', 'rate' => 20.00],
            ['product_type' => 'table', 'rate' => 20.00],
            ['product_type' => 'event', 'rate' => 20.00],
            ['product_type' => 'food', 'rate' => 20.00],
            ['product_type' => 'membership', 'rate' => 0.00], // Outside scope of VAT
        ];

        foreach ($rates as $rate) {
            VatRate::updateOrCreate(
                ['product_type' => $rate['product_type']],
                ['rate' => $rate['rate']]
            );
        }

        $this->command->info('VAT rates seeded.');
    }
}
