<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CouponTest extends TestCase
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
            'stock' => 20,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_a_percentage_coupon_discounts_the_cart(): void
    {
        $this->coupon(['code' => 'FRESH10', 'type' => 'percent', 'value' => 10, 'min_spend' => 300]);
        $this->addToCart(4);

        $this->apply('fresh10')->assertSessionHasNoErrors();

        $this->actingAs($this->customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('FRESH10')
            ->assertSee('40.00')
            ->assertSee('380.00');
    }

    public function test_a_fixed_coupon_discounts_the_cart(): void
    {
        $this->coupon(['code' => 'FLAT50', 'type' => 'fixed', 'value' => 50]);
        $this->addToCart(4);

        $this->apply('FLAT50')->assertSessionHasNoErrors();

        $this->actingAs($this->customer)
            ->get(route('cart.index'))
            ->assertSee('370.00');
    }

    public function test_coupons_respect_the_minimum_spend(): void
    {
        $this->coupon(['code' => 'BIG', 'min_spend' => 1000]);
        $this->addToCart(2);

        $this->apply('BIG')->assertSessionHasErrors('code');

        $this->assertNull(session('coupon_code'));
    }

    public function test_expired_coupons_are_rejected(): void
    {
        $this->coupon(['code' => 'OLD', 'expires_at' => now()->subDay()]);
        $this->addToCart(2);

        $this->apply('OLD')->assertSessionHasErrors('code');
        $this->assertNull(session('coupon_code'));
    }

    public function test_inactive_coupons_are_rejected(): void
    {
        $this->coupon(['code' => 'OFF', 'is_active' => false]);
        $this->addToCart(2);

        $this->apply('OFF')->assertSessionHasErrors('code');
    }

    public function test_fully_redeemed_coupons_are_rejected(): void
    {
        $this->coupon(['code' => 'GONE', 'max_uses' => 5, 'used_count' => 5]);
        $this->addToCart(2);

        $this->apply('GONE')->assertSessionHasErrors('code');
    }

    public function test_expired_coupons_are_dropped_from_the_session(): void
    {
        $coupon = $this->coupon(['code' => 'OLD']);
        $this->addToCart(2);

        $this->apply('OLD')->assertSessionHasNoErrors();

        $coupon->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('expired');

        $this->assertNull(session('coupon_code'));
    }

    public function test_removing_a_coupon_clears_it(): void
    {
        $this->coupon(['code' => 'FLAT50', 'type' => 'fixed', 'value' => 50]);
        $this->addToCart(4);

        $this->apply('FLAT50')->assertSessionHasNoErrors();

        $this->actingAs($this->customer)
            ->delete(route('coupons.destroy'))
            ->assertSessionHasNoErrors();

        $this->assertNull(session('coupon_code'));
    }

    public function test_checkout_stores_the_discount_and_counts_the_redemption(): void
    {
        $coupon = $this->coupon(['code' => 'FLAT50', 'type' => 'fixed', 'value' => 50]);
        $this->addToCart(4);

        $this->apply('FLAT50')->assertSessionHasNoErrors();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->delivery())
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();

        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('FLAT50', $order->coupon_code);
        $this->assertEquals(50, (float) $order->discount);
        $this->assertEquals(370, (float) $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertNull(session('coupon_code'));
    }

    public function test_checkout_revalidates_the_usage_cap(): void
    {
        $coupon = $this->coupon(['code' => 'ONCE', 'max_uses' => 1]);
        $this->addToCart(4);

        $this->apply('ONCE')->assertSessionHasNoErrors();

        $coupon->update(['used_count' => 1]);

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->delivery())
            ->assertSessionHasErrors('code');

        $this->assertSame(0, Order::count());
    }

    public function test_order_page_shows_the_discount(): void
    {
        $this->coupon(['code' => 'FLAT50', 'type' => 'fixed', 'value' => 50]);
        $this->addToCart(4);

        $this->apply('FLAT50')->assertSessionHasNoErrors();

        $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery());

        $order = Order::firstOrFail();

        $this->actingAs($this->customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('FLAT50')
            ->assertSee('370.00');
    }

    public function test_admins_can_create_and_manage_coupons(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'welcome25',
                'description' => 'Welcome offer',
                'type' => 'percent',
                'value' => 25,
                'min_spend' => 400,
                'max_uses' => 50,
                'expires_at' => now()->addWeek()->toDateString(),
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $coupon = Coupon::firstOrFail();

        $this->assertSame('WELCOME25', $coupon->code);
        $this->assertTrue($coupon->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.coupons.toggle', $coupon))
            ->assertSessionHasNoErrors();

        $this->assertFalse($coupon->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.coupons.destroy', $coupon))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($coupon);
    }

    public function test_percentage_coupons_cannot_exceed_one_hundred_percent(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'TOOMUCH',
                'type' => 'percent',
                'value' => 150,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('value');
    }

    public function test_non_admins_cannot_manage_coupons(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.coupons.index'))
            ->assertForbidden();

        $this->actingAs($this->customer)
            ->post(route('admin.coupons.store'), [
                'code' => 'HACK',
                'type' => 'fixed',
                'value' => 10,
            ])
            ->assertForbidden();
    }

    private function apply(string $code): TestResponse
    {
        return $this->actingAs($this->customer)->post(route('coupons.store'), ['code' => $code]);
    }

    private function addToCart(int $quantity): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->product), ['quantity' => $quantity]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'FRESH10',
            'type' => 'percent',
            'value' => 10,
            'min_spend' => 0,
            'is_active' => true,
        ], $overrides));
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
