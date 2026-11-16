<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        $farmer = User::factory()->farmer()->create();
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->customer->id,
            'status' => OrderStatus::Pending,
            'delivery_name' => $this->customer->name,
            'delivery_phone' => '01712345678',
            'delivery_address' => '12 Green Road, Dhaka',
            'subtotal' => 200,
            'platform_fee' => 10,
            'total' => 210,
            'placed_at' => now(),
        ]);

        $this->order->items()->create([
            'product_id' => $product->id,
            'farmer_id' => $farmer->id,
            'product_name' => $product->name,
            'unit_price' => 100,
            'quantity' => 2,
            'line_total' => 200,
        ]);
    }

    public function test_customers_can_download_their_own_invoice(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('orders.invoice', $this->order));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('invoice-'.$this->order->id.'.pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_customers_cannot_download_another_customers_invoice(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('orders.invoice', $this->order))
            ->assertForbidden();
    }

    public function test_admins_can_download_any_invoice(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('orders.invoice', $this->order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_farmers_cannot_download_customer_invoices(): void
    {
        $farmer = User::factory()->farmer()->create();

        $this->actingAs($farmer)
            ->get(route('orders.invoice', $this->order))
            ->assertForbidden();
    }
}
