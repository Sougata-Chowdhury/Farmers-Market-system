<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $farmer;

    private Product $tomato;

    private Product $potato;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        $this->farmer = User::factory()->farmer()->create();
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $this->tomato = Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->potato = Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $category->id,
            'name' => 'New Potato',
            'description' => 'Just dug potatoes.',
            'price' => 60,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_buy_again_returns_past_order_products_to_the_cart(): void
    {
        $order = $this->order([
            [$this->tomato, 2],
            [$this->potato, 3],
        ]);

        $this->actingAs($this->customer)
            ->post(route('orders.reorder', $order))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');

        $this->assertSame(2, $this->cartQuantity($this->tomato));
        $this->assertSame(3, $this->cartQuantity($this->potato));
    }

    public function test_unavailable_products_are_skipped(): void
    {
        $order = $this->order([
            [$this->tomato, 2],
            [$this->potato, 1],
        ]);

        $this->potato->delete();

        $this->actingAs($this->customer)
            ->post(route('orders.reorder', $order))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', fn (string $message): bool => str_contains($message, '1 unavailable product skipped'));

        $this->assertSame(2, $this->cartQuantity($this->tomato));
        $this->assertNull($this->cartQuantity($this->potato));
    }

    public function test_reorder_is_capped_at_current_stock(): void
    {
        $order = $this->order([
            [$this->tomato, 8],
        ]);

        $this->tomato->update(['stock' => 3]);

        $this->actingAs($this->customer)
            ->post(route('orders.reorder', $order))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(3, $this->cartQuantity($this->tomato));
    }

    public function test_totally_unavailable_orders_report_an_error(): void
    {
        $order = $this->order([
            [$this->tomato, 2],
        ]);

        $this->tomato->update(['is_active' => false]);

        $this->actingAs($this->customer)
            ->post(route('orders.reorder', $order))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($this->cartQuantity($this->tomato));
    }

    public function test_customers_cannot_reorder_someone_elses_order(): void
    {
        $order = $this->order([
            [$this->tomato, 2],
        ]);

        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('orders.reorder', $order))
            ->assertForbidden();

        $this->assertNull($this->cartQuantity($this->tomato));
    }

    public function test_the_orders_page_offers_buy_again(): void
    {
        $this->order([
            [$this->tomato, 1],
        ]);

        $this->actingAs($this->customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Buy again');
    }

    public function test_cancelled_orders_do_not_offer_buy_again(): void
    {
        $this->order([
            [$this->tomato, 1],
        ], OrderStatus::Cancelled);

        $this->actingAs($this->customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertDontSee('Buy again');
    }

    public function test_guests_are_redirected_when_reordering(): void
    {
        $order = $this->order([
            [$this->tomato, 1],
        ]);

        $this->post(route('orders.reorder', $order))->assertRedirect(route('login'));
    }

    /**
     * @param  array<int, array{0: Product, 1: int}>  $lines
     */
    private function order(array $lines, OrderStatus $status = OrderStatus::Delivered): Order
    {
        $order = $this->customer->orders()->create([
            'status' => $status,
            'delivery_name' => $this->customer->name,
            'delivery_phone' => '01712345678',
            'delivery_address' => '12 Green Road, Dhaka',
            'subtotal' => 0,
            'platform_fee' => 0,
            'total' => 0,
            'placed_at' => now()->subDay(),
        ]);

        $subtotal = 0.0;

        foreach ($lines as [$product, $quantity]) {
            $lineTotal = (float) $product->price * $quantity;
            $subtotal += $lineTotal;

            $order->items()->create([
                'product_id' => $product->id,
                'farmer_id' => $product->farmer_id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ]);
        }

        $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);

        return $order;
    }

    private function cartQuantity(Product $product): ?int
    {
        return $this->customer->carts()
            ->where('product_id', $product->id)
            ->first()
            ?->quantity;
    }
}
