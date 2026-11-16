<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_protected_areas(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/cart')->assertRedirect('/login');
        $this->get('/farmer')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customers_cannot_access_the_admin_area(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/orders')->assertForbidden();
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
    }

    public function test_customers_cannot_access_the_farmer_area(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/farmer')->assertForbidden();
        $this->actingAs($customer)->get('/farmer/products')->assertForbidden();
    }

    public function test_farmers_cannot_access_the_admin_or_customer_areas(): void
    {
        $farmer = User::factory()->farmer()->create();

        $this->actingAs($farmer)->get('/admin')->assertForbidden();
        $this->actingAs($farmer)->get('/cart')->assertForbidden();
        $this->actingAs($farmer)->get('/orders')->assertForbidden();
    }

    public function test_admins_cannot_access_the_farmer_or_customer_areas(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/farmer')->assertForbidden();
        $this->actingAs($admin)->get('/cart')->assertForbidden();
        $this->actingAs($admin)->get('/checkout')->assertForbidden();
    }

    public function test_each_role_is_sent_to_its_own_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs(User::factory()->farmer()->create())
            ->get('/dashboard')
            ->assertRedirect(route('farmer.dashboard'));

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertRedirect(route('orders.index'));
    }
}
