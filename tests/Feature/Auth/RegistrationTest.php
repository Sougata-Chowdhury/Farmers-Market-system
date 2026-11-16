<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_customers_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '01712345678',
            'role' => 'customer',
            'address' => '12 Green Road, Dhaka',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::firstOrFail();

        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertSame('01712345678', $user->phone);
        $this->assertNull($user->farmerProfile);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_farmers_can_register_and_get_a_farmer_profile(): void
    {
        $this->post('/register', [
            'name' => 'Test Farmer',
            'email' => 'farmer@example.com',
            'phone' => '01812345678',
            'role' => 'farmer',
            'address' => 'Village Road, Savar',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $farmer = User::firstOrFail();

        $this->assertSame(UserRole::Farmer, $farmer->role);
        $this->assertNotNull($farmer->farmerProfile);
    }

    public function test_registration_requires_a_valid_phone_number_and_role(): void
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '12345',
                'role' => 'admin',
                'address' => '12 Green Road, Dhaka',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response
            ->assertSessionHasErrors(['phone', 'role'])
            ->assertRedirect('/register');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
