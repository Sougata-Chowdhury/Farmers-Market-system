<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        $farmer = User::factory()->farmer()->create();
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $this->product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_customers_can_add_a_delivery_address(): void
    {
        $this->actingAs($this->customer)
            ->post(route('addresses.store'), [
                'label' => 'Home',
                'recipient_name' => 'Nusrat Jahan',
                'phone' => '01712345678',
                'address_line' => 'House 12, Road 5, Dhanmondi',
                'area' => 'Dhaka 1205',
                'is_default' => '1',
            ])
            ->assertSessionHasNoErrors();

        $address = Address::firstOrFail();

        $this->assertTrue($address->is_default);
        $this->assertSame($this->customer->id, $address->user_id);
    }

    public function test_the_first_address_becomes_the_default_automatically(): void
    {
        $this->actingAs($this->customer)->post(route('addresses.store'), [
            'label' => 'Home',
            'recipient_name' => 'Nusrat Jahan',
            'phone' => '01712345678',
            'address_line' => 'House 12, Dhanmondi',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Address::firstOrFail()->is_default);
    }

    public function test_customers_can_change_their_default_address(): void
    {
        $home = $this->address(['label' => 'Home', 'is_default' => true]);
        $office = $this->address(['label' => 'Office', 'address_line' => 'Gulshan 1']);

        $this->actingAs($this->customer)
            ->patch(route('addresses.default', $office))
            ->assertSessionHasNoErrors();

        $this->assertFalse($home->fresh()->is_default);
        $this->assertTrue($office->fresh()->is_default);
    }

    public function test_customers_cannot_touch_another_users_address(): void
    {
        $other = User::factory()->create();
        $address = $this->address(['label' => 'Home', 'is_default' => true], $other);

        $this->actingAs($this->customer)
            ->patch(route('addresses.default', $address))
            ->assertForbidden();

        $this->actingAs($this->customer)
            ->delete(route('addresses.destroy', $address))
            ->assertForbidden();

        $this->assertModelExists($address);
    }

    public function test_deleting_the_default_address_promotes_another_one(): void
    {
        $home = $this->address(['label' => 'Home', 'is_default' => true]);
        $office = $this->address(['label' => 'Office', 'address_line' => 'Gulshan 1']);

        $this->actingAs($this->customer)
            ->delete(route('addresses.destroy', $home))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($home);
        $this->assertTrue($office->fresh()->is_default);
    }

    public function test_checkout_uses_a_saved_address_and_stores_the_payment_method(): void
    {
        $address = $this->address([
            'label' => 'Home',
            'is_default' => true,
            'recipient_name' => 'Saved Recipient',
            'address_line' => 'House 12, Dhanmondi',
            'area' => 'Dhaka 1205',
        ]);

        $this->addToCart();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => PaymentMethod::Bkash->value,
                'notes' => 'Call on arrival.',
            ])
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();

        $this->assertSame(PaymentMethod::Bkash, $order->payment_method);
        $this->assertSame($address->id, $order->address_id);
        $this->assertSame('Saved Recipient', $order->delivery_name);
        $this->assertSame('House 12, Dhanmondi, Dhaka 1205', $order->delivery_address);
    }

    public function test_checkout_can_save_a_new_address_for_next_time(): void
    {
        $this->addToCart();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'name' => 'Nusrat Jahan',
                'phone' => '01712345678',
                'address' => 'House 9, Uttara',
                'payment_method' => PaymentMethod::Cod->value,
                'save_address' => '1',
                'label' => 'Parents',
            ])
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();

        $this->assertNull($order->address_id);

        $address = Address::firstOrFail();

        $this->assertSame('Parents', $address->label);
        $this->assertSame('House 9, Uttara', $address->address_line);
        $this->assertTrue($address->is_default);
    }

    public function test_payment_method_defaults_to_cash_on_delivery(): void
    {
        $this->addToCart();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->delivery())
            ->assertSessionHasNoErrors();

        $this->assertSame(PaymentMethod::Cod, Order::firstOrFail()->payment_method);
    }

    public function test_invalid_payment_methods_are_rejected(): void
    {
        $this->addToCart();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->delivery() + ['payment_method' => 'bitcoin'])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
    }

    public function test_orders_page_shows_the_payment_method(): void
    {
        $this->addToCart();

        $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery() + [
            'payment_method' => PaymentMethod::Nagad->value,
        ]);

        $order = Order::firstOrFail();

        $this->actingAs($this->customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Nagad');
    }

    public function test_guests_cannot_access_saved_addresses(): void
    {
        $this->get(route('addresses.index'))->assertRedirect(route('login'));
    }

    public function test_customers_can_view_their_saved_addresses(): void
    {
        $this->address(['label' => 'Home', 'is_default' => true]);

        $this->actingAs($this->customer)
            ->get(route('addresses.index'))
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('House 12, Road 5, Dhanmondi');
    }

    public function test_checkout_page_offers_saved_addresses_and_payment_methods(): void
    {
        $this->address(['label' => 'Home', 'is_default' => true]);
        $this->addToCart();

        $this->actingAs($this->customer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('Cash on delivery')
            ->assertSee('bKash')
            ->assertSee('Nagad');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function address(array $overrides = [], ?User $user = null): Address
    {
        return ($user ?? $this->customer)->addresses()->create(array_merge([
            'label' => 'Home',
            'recipient_name' => 'Nusrat Jahan',
            'phone' => '01712345678',
            'address_line' => 'House 12, Road 5, Dhanmondi',
            'area' => 'Dhaka 1205',
            'is_default' => false,
        ], $overrides));
    }

    private function addToCart(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->product), ['quantity' => 2]);
    }

    /**
     * @return array{name: string, phone: string, address: string}
     */
    private function delivery(): array
    {
        return [
            'name' => 'Nusrat Jahan',
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ];
    }
}
