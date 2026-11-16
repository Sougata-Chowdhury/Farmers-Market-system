<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
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

    public function test_placing_an_order_records_the_pending_status(): void
    {
        $product = $this->product();

        $this->actingAs($this->customer)->post(route('cart.store', $product), ['quantity' => 2]);
        $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery());

        $order = Order::firstOrFail();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'pending',
            'actor_id' => $this->customer->id,
            'note' => 'Order placed and stock reserved.',
        ]);
    }

    public function test_admin_status_changes_are_recorded_with_the_actor(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->placeOrder();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHasNoErrors();

        $timeline = $order->histories()->get();

        $this->assertSame(
            [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Delivered],
            $timeline->pluck('status')->all()
        );

        $this->assertSame($admin->id, $timeline[1]->actor_id);
        $this->assertSame('Order confirmed and queued for packing.', $timeline[1]->note);
        $this->assertSame('Order marked as delivered.', $timeline[2]->note);
    }

    public function test_cancelling_an_order_is_recorded_on_the_timeline(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->customer)
            ->post(route('orders.cancel', $order))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'actor_id' => $this->customer->id,
            'note' => 'Order cancelled and stock returned to farmers.',
        ]);
    }

    public function test_order_page_displays_the_timeline(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Order timeline')
            ->assertSee('Order placed')
            ->assertSee('Order placed and stock reserved.');
    }

    private function placeOrder(): Order
    {
        $product = $this->product();

        $this->actingAs($this->customer)->post(route('cart.store', $product), ['quantity' => 2]);
        $this->actingAs($this->customer)->post(route('checkout.store'), $this->delivery());

        return Order::latest('id')->firstOrFail();
    }

    /**
     * @return array{name: string, phone: string, address: string}
     */
    private function delivery(): array
    {
        return [
            'name' => 'Test Customer',
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ];
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Vegetables'],
            ['slug' => 'vegetables'],
        );

        $farmer = User::factory()->farmer()->create();

        return Product::create([
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
}
