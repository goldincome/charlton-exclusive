<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_application_with_png_id_document(): void
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.applications.store'), [
            'phone' => '07123456789',
            'address_line_1' => '123 Charlton Road',
            'city' => 'London',
            'postcode' => 'SE7 8JW',
            'profession' => 'Engineer',
            'id_type' => 'passport',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'id_document' => UploadedFile::fake()->image('id.png'),
            'terms' => '1',
        ]);

        $response->assertRedirect(route('account.applications.index'));
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'id_type' => 'passport',
        ]);
    }

    public function test_admin_can_create_walk_in_application_for_new_account(): void
    {
        Storage::fake('private');
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $plan = Plan::query()->create([
            'name' => 'Full Membership',
            'slug' => 'full-membership',
            'annual_fee' => 100000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applications.store'), [
            'account_type' => 'new',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '07987654321',
            'plan_id' => $plan->id,
            'id_document' => UploadedFile::fake()->image('id.png'),
            'notes' => 'Walk-in submission verified in person.',
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
        $this->assertDatabaseHas('applications', ['membership_category_id' => $plan->id]);
    }

    public function test_admin_can_create_walk_in_application_for_existing_account(): void
    {
        Storage::fake('private');
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $existingUser = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
        ]);

        $plan = Plan::query()->create([
            'name' => 'Country Membership',
            'slug' => 'country-membership',
            'annual_fee' => 80000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applications.store'), [
            'account_type' => 'existing',
            'user_id' => $existingUser->id,
            'name' => $existingUser->name,
            'email' => $existingUser->email,
            'phone' => '07111222333',
            'plan_id' => $plan->id,
            'id_document' => UploadedFile::fake()->image('id.png'),
            'notes' => 'Walk-in for existing user.',
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('applications', [
            'user_id' => $existingUser->id,
            'membership_category_id' => $plan->id,
        ]);
    }

    public function test_admin_can_search_users_for_application(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create(['name' => 'UniqueSearchName', 'email' => 'unique@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.applications.users-search', ['q' => 'UniqueSearchName']));

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'UniqueSearchName']);
    }
}
