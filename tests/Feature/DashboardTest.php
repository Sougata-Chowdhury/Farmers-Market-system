<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $farmer;

    private User $customer;

    private Product $tomato;

    private Product $potato;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->farmer = User::factory()->farmer()->create(['name' => 'Rafiq Hasan']);
        $this->customer = User::factory()->create(['name' => 'Nusrat Jahan']);

        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $this->tomato = Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 2,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->potato = Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $category->id,
            'name' => 'New Potato',
            'description' => 'Just dug potatoes.',
            'price' => 60,
            'stock' => 30,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->order();
    }

    public function test_the_admin_dashboard_shows_trends_and_top_categories(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Revenue, last 30 days')
            ->assertSee('Orders by status')
            ->assertSee('Top categories')
            ->assertSee('Vegetables')
            ->assertSee('Nusrat Jahan');
    }

    public function test_the_farmer_dashboard_shows_sales_and_low_stock(): void
    {
        $this->actingAs($this->farmer)
            ->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('Sales, last 30 days')
            ->assertSee('Low stock')
            ->assertSee('Fresh Tomato')
            ->assertSee('2 kg left')
            ->assertSee('Recent sales');
    }

    public function test_a_well_stocked_farmer_sees_a_positive_low_stock_message(): void
    {
        $this->tomato->update(['stock' => 50]);

        $this->actingAs($this->farmer)
            ->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('Everything is comfortably stocked');
    }

    public function test_admins_can_export_orders_as_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.exports.orders'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Customer,Status,Payment', $csv);
        $this->assertStringContainsString('Nusrat Jahan', $csv);
        $this->assertStringContainsString('Delivered', $csv);
    }

    public function test_admins_can_export_products_as_csv(): void
    {
        $csv = $this->actingAs($this->admin)
            ->get(route('admin.exports.products'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Product,Farmer,Category', $csv);
        $this->assertStringContainsString('Fresh Tomato', $csv);
        $this->assertStringContainsString('Rafiq Hasan', $csv);
    }

    public function test_farmers_only_export_their_own_sales(): void
    {
        $otherFarmer = User::factory()->farmer()->create();
        $theirProduct = Product::create([
            'farmer_id' => $otherFarmer->id,
            'category_id' => $this->tomato->category_id,
            'name' => 'Other Farmer Rice',
            'description' => 'Rice.',
            'price' => 90,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => OrderStatus::Confirmed,
            'payment_method' => PaymentMethod::Cod,
            'delivery_name' => 'Nusrat Jahan',
            'delivery_phone' => '01712345678',
            'delivery_address' => '12 Green Road, Dhaka',
            'subtotal' => 90,
            'platform_fee' => 4.5,
            'total' => 94.5,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $theirProduct->id,
            'farmer_id' => $otherFarmer->id,
            'product_name' => 'Other Farmer Rice',
            'unit_price' => 90,
            'quantity' => 1,
            'line_total' => 90,
        ]);

        $csv = $this->actingAs($this->farmer)
            ->get(route('farmer.exports.orders'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Fresh Tomato', $csv);
        $this->assertStringNotContainsString('Other Farmer Rice', $csv);
    }

    public function test_farmers_can_export_their_product_catalogue(): void
    {
        $csv = $this->actingAs($this->farmer)
            ->get(route('farmer.exports.products'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('New Potato', $csv);
        $this->assertStringContainsString('Vegetables', $csv);
    }

    public function test_non_admins_cannot_use_the_admin_exports(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.exports.orders'))
            ->assertForbidden();

        $this->actingAs($this->farmer)
            ->get(route('admin.exports.products'))
            ->assertForbidden();
    }

    private function order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'status' => OrderStatus::Delivered,
            'payment_method' => PaymentMethod::Bkash,
            'delivery_name' => 'Nusrat Jahan',
            'delivery_phone' => '01712345678',
            'delivery_address' => '12 Green Road, Dhaka',
            'subtotal' => 260,
            'discount' => 0,
            'platform_fee' => 13,
            'total' => 273,
            'placed_at' => now()->subDays(2),
        ]);

        foreach ([[$this->tomato, 2], [$this->potato, 1]] as [$product, $quantity]) {
            $order->items()->create([
                'product_id' => $product->id,
                'farmer_id' => $product->farmer_id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => (float) $product->price * $quantity,
            ]);
        }
    }
}
