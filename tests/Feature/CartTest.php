<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();
    }

    public function test_customer_can_add_a_product_to_the_cart(): void
    {
        $product = $this->product(['stock' => 10]);

        $response = $this
            ->actingAs($this->customer)
            ->post(route('cart.store', $product), ['quantity' => 2]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $product = $this->product(['stock' => 3]);

        $response = $this
            ->actingAs($this->customer)
            ->from(route('home'))
            ->post(route('cart.store', $product), ['quantity' => 4]);

        $response
            ->assertSessionHasErrors('quantity')
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_the_same_product_accumulates_quantity_and_respects_stock(): void
    {
        $product = $this->product(['stock' => 5]);

        $this->actingAs($this->customer)
            ->post(route('cart.store', $product), ['quantity' => 3])
            ->assertSessionHasNoErrors();

        $response = $this
            ->actingAs($this->customer)
            ->from(route('home'))
            ->post(route('cart.store', $product), ['quantity' => 3]);

        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_inactive_products_cannot_be_added_to_the_cart(): void
    {
        $product = $this->product(['is_active' => false]);

        $response = $this
            ->actingAs($this->customer)
            ->from(route('home'))
            ->post(route('cart.store', $product));

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_customer_can_update_and_remove_a_cart_item(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->customer)
            ->post(route('cart.store', $product), ['quantity' => 2]);

        $this->actingAs($this->customer)
            ->patch(route('cart.update', $product), ['quantity' => 5])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->actingAs($this->customer)
            ->delete(route('cart.destroy', $product))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_cart_totals_include_the_platform_fee(): void
    {
        $product = $this->product(['price' => 100, 'stock' => 10]);

        $this->actingAs($this->customer)
            ->post(route('cart.store', $product), ['quantity' => 2]);

        $totals = app(CartService::class)->totals($this->customer);

        $this->assertSame(200.0, $totals['subtotal']);
        $this->assertSame(10.0, $totals['fee']);
        $this->assertSame(210.0, $totals['total']);
        $this->assertSame(2, $totals['count']);
    }

    public function test_ajax_adds_return_the_mini_cart_payload(): void
    {
        $product = $this->product(['stock' => 10]);

        $response = $this->actingAs($this->customer)
            ->postJson(route('cart.store', $product), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('subtotal', 200);

        $html = (string) $response->json('html');

        $this->assertStringContainsString('Fresh Tomato', $html);
        $this->assertStringContainsString('Checkout', $html);
        $this->assertSame('Fresh Tomato added to your cart.', $response->json('message'));
    }

    public function test_the_mini_cart_endpoint_requires_a_customer(): void
    {
        $this->getJson(route('cart.mini'))->assertUnauthorized();

        $farmer = User::factory()->farmer()->create();
        $this->actingAs($farmer)->getJson(route('cart.mini'))->assertForbidden();
    }

    public function test_the_mini_cart_endpoint_reports_an_empty_cart(): void
    {
        $this->actingAs($this->customer)
            ->getJson(route('cart.mini'))
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, 'Your cart is empty'));
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
