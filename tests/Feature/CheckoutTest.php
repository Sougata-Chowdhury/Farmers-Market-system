<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);
    }

    public function test_checkout_creates_an_order_and_decrements_stock(): void
    {
        $product = $this->product(['price' => 100, 'stock' => 10]);

        $this->addToCart($product, 2);

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery());

        $order = Order::firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame($this->customer->id, $order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertEquals(200.0, (float) $order->subtotal);
        $this->assertEquals(10.0, (float) $order->platform_fee);
        $this->assertEquals(210.0, (float) $order->total);
        $this->assertSame('Test Customer', $order->delivery_name);
        $this->assertSame('01712345678', $order->delivery_phone);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'farmer_id' => $product->farmer_id,
            'product_name' => $product->name,
            'quantity' => 2,
            'line_total' => 200,
        ]);

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_checkout_requires_a_non_empty_cart(): void
    {
        $response = $this
            ->actingAs($this->customer)
            ->from(route('cart.index'))
            ->post(route('checkout.store'), $this->delivery());

        $response
            ->assertSessionHasErrors('cart')
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_when_stock_falls_below_the_cart_quantity(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->addToCart($product, 5);

        $product->update(['stock' => 2]);

        $response = $this
            ->actingAs($this->customer)
            ->from(route('cart.index'))
            ->post(route('checkout.store'), $this->delivery());

        $response
            ->assertSessionHasErrors('cart')
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseHas('carts', ['product_id' => $product->id, 'quantity' => 5]);
    }

    public function test_checkout_rejects_invalid_delivery_details(): void
    {
        $product = $this->product();
        $this->addToCart($product, 1);

        $response = $this
            ->actingAs($this->customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'name' => 'Test Customer',
                'phone' => '12345',
                'address' => '12 Green Road, Dhaka',
            ]);

        $response
            ->assertSessionHasErrors('phone')
            ->assertRedirect(route('checkout.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_cancel_a_pending_order_and_stock_is_restored(): void
    {
        $product = $this->product(['stock' => 10]);
        $order = $this->placeOrder($product, 3);

        $response = $this
            ->actingAs($this->customer)
            ->post(route('orders.cancel', $order));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        $product = $this->product(['stock' => 10]);
        $order = $this->placeOrder($product, 2);

        $other = User::factory()->create();

        $this
            ->actingAs($other)
            ->post(route('orders.cancel', $order))
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_delivered_orders_cannot_be_cancelled(): void
    {
        $product = $this->product(['stock' => 10]);
        $order = $this->placeOrder($product, 2);

        $order->update(['status' => OrderStatus::Delivered]);

        $this
            ->actingAs($this->customer)
            ->post(route('orders.cancel', $order))
            ->assertForbidden();

        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    private function placeOrder(Product $product, int $quantity): Order
    {
        $this->addToCart($product, $quantity);

        $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery());

        return Order::latest('id')->firstOrFail();
    }

    private function addToCart(Product $product, int $quantity): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $product), ['quantity' => $quantity])
            ->assertSessionHasNoErrors();
    }

    /**
     * @return array{name: string, phone: string, address: string, notes?: string}
     */
    private function delivery(): array
    {
        return [
            'name' => 'Test Customer',
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
            'notes' => 'Please call before delivery.',
        ];
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
