<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create();
    }

    public function test_admin_can_confirm_a_pending_order(): void
    {
        $order = $this->order(['stock' => 5], 2);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_admin_cannot_skip_from_pending_to_delivered(): void
    {
        $order = $this->order(['stock' => 5], 2);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHasErrors('status')
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_admin_cancelling_an_order_restores_stock(): void
    {
        $product = $this->product(['stock' => 5]);
        $order = $this->orderFrom($product, 3);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customers_cannot_change_order_status(): void
    {
        $order = $this->order(['stock' => 5], 1);

        $this->actingAs($this->customer)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_admin_order_show_page_is_displayed(): void
    {
        $order = $this->order(['stock' => 5], 1);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function order(array $overrides, int $quantity): Order
    {
        return $this->orderFrom($this->product($overrides), $quantity);
    }

    private function orderFrom(Product $product, int $quantity): Order
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => OrderStatus::Pending,
            'delivery_name' => $this->customer->name,
            'delivery_phone' => '01712345678',
            'delivery_address' => '12 Green Road, Dhaka',
            'subtotal' => (float) $product->price * $quantity,
            'platform_fee' => (float) $product->price * $quantity * 0.05,
            'total' => (float) $product->price * $quantity * 1.05,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'farmer_id' => $product->farmer_id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => (float) $product->price * $quantity,
        ]);

        $product->decrement('stock', $quantity);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function product(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Vegetables'],
            ['slug' => 'vegetables'],
        );

        $farmer = User::factory()->farmer()->create();

        return Product::create(array_merge([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ], $overrides));
    }
}
