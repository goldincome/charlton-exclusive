<?php

namespace Tests\Feature;

use App\Models\Hall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HallManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_hall(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $hall = Hall::query()->create([
            'name' => 'Original Room',
            'slug' => 'original-room',
            'capacity' => 20,
            'min_hours' => 2,
            'buffer_minutes' => 30,
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.halls.update', $hall), [
            'name' => 'Updated Room Name',
            'capacity' => 50,
            'min_hours' => 3,
            'buffer_minutes' => 45,
            'active' => 1,
            'ratings' => [
                'Mon' => [
                    'standard_price_per_hour' => '60.00',
                    'member_discount_type' => 'percent',
                    'member_discount_value' => '20',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('halls', [
            'id' => $hall->id,
            'name' => 'Updated Room Name',
            'capacity' => 50,
        ]);
        $this->assertDatabaseHas('room_rates', [
            'hall_id' => $hall->id,
            'day_type' => 'Mon',
            'standard_price_per_hour' => 6000,
        ]);
    }
}
