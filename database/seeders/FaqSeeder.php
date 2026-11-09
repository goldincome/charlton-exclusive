<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'group' => 'Membership',
                'question' => 'Who can apply for membership?',
                'answer' => 'Any individual over 18 years of age may apply. All applications require two sponsoring members of at least three years\' standing.',
                'order' => 1,
                'active' => true,
            ],
            [
                'group' => 'Membership',
                'question' => 'How long does the application process take?',
                'answer' => 'From submission to decision typically takes 4-6 weeks. This includes sponsor confirmations, committee review and an informal interview.',
                'order' => 2,
                'active' => true,
            ],
            [
                'group' => 'Membership',
                'question' => 'Can I visit the club before applying?',
                'answer' => 'Yes. Contact the club secretary to arrange a visit. Prospective members may visit as a guest of a current member.',
                'order' => 3,
                'active' => true,
            ],
            [
                'group' => 'Bookings',
                'question' => 'How far in advance can I book?',
                'answer' => 'Members can book meeting rooms up to 6 months in advance. Event bookings open 3 months ahead. Table reservations can be made up to 4 weeks in advance.',
                'order' => 10,
                'active' => true,
            ],
            [
                'group' => 'Bookings',
                'question' => 'What is your cancellation policy?',
                'answer' => 'Cancellations made 7+ days before the booking receive a full refund. 48 hours to 7 days: 50% refund. Less than 48 hours: no refund. No-shows are not refunded.',
                'order' => 11,
                'active' => true,
            ],
            [
                'group' => 'Facilities',
                'question' => 'Do you have parking?',
                'answer' => 'Yes, complimentary parking is available on site. Spaces are allocated on a first-come basis. Nearby street parking is also available.',
                'order' => 20,
                'active' => true,
            ],
            [
                'group' => 'Facilities',
                'question' => 'Is the club accessible?',
                'answer' => 'Yes. The club has step-free access, accessible toilets and hearing loop systems in meeting rooms. See our full accessibility statement for details.',
                'order' => 21,
                'active' => true,
            ],
        ];

        foreach ($faqs as $data) {
            FaqItem::firstOrCreate(
                ['question' => $data['question']],
                $data
            );
        }

        $this->command->info('FAQ items seeded.');
    }
}
