<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Product $tomato;

    private Product $potato;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        $farmer = User::factory()->farmer()->create();
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);

        $this->tomato = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->potato = Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'New Potato',
            'description' => 'Just dug potatoes.',
            'price' => 60,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_customers_can_save_a_product_to_their_wishlist(): void
    {
        $this->actingAs($this->customer)
            ->post(route('wishlist.store', $this->tomato))
            ->assertSessionHasNoErrors();

        $this->assertSame([$this->tomato->id], session('wishlist'));

        $this->actingAs($this->customer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Fresh Tomato')
            ->assertSee('1 product saved for later');
    }

    public function test_a_product_can_be_removed_from_the_wishlist(): void
    {
        $this->actingAs($this->customer)->post(route('wishlist.store', $this->tomato));

        $this->actingAs($this->customer)
            ->delete(route('wishlist.destroy', $this->tomato))
            ->assertSessionHasNoErrors();

        $this->assertSame([], session('wishlist'));

        $this->actingAs($this->customer)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Your wishlist is empty');
    }

    public function test_saving_the_same_product_twice_does_not_duplicate_it(): void
    {
        $this->actingAs($this->customer)->post(route('wishlist.store', $this->tomato));
        $this->actingAs($this->customer)->post(route('wishlist.store', $this->tomato));

        $this->assertSame([$this->tomato->id], session('wishlist'));
    }

    public function test_newest_saved_products_show_first(): void
    {
        $this->actingAs($this->customer)->post(route('wishlist.store', $this->tomato));
        $this->actingAs($this->customer)->post(route('wishlist.store', $this->potato));

        $this->assertSame([$this->potato->id, $this->tomato->id], session('wishlist'));
    }

    public function test_visiting_a_product_tracks_it_as_recently_viewed(): void
    {
        $this->actingAs($this->customer)
            ->get(route('products.show', $this->tomato))
            ->assertOk();

        $this->assertSame([$this->tomato->id], session('recently_viewed'));
    }

    public function test_the_product_page_lists_recently_viewed_products_except_the_current_one(): void
    {
        $this->actingAs($this->customer)->get(route('products.show', $this->tomato));

        $this->actingAs($this->customer)
            ->get(route('products.show', $this->potato))
            ->assertOk()
            ->assertSee('Recently viewed')
            ->assertSee('Fresh Tomato')
            ->assertSee('New Potato');
    }

    public function test_recently_viewed_is_capped_and_moves_a_revisited_product_to_the_front(): void
    {
        $this->actingAs($this->customer)->get(route('products.show', $this->tomato));
        $this->actingAs($this->customer)->get(route('products.show', $this->potato));
        $this->actingAs($this->customer)->get(route('products.show', $this->tomato));

        $this->assertSame([$this->tomato->id, $this->potato->id], session('recently_viewed'));
    }

    public function test_guests_are_redirected_from_the_wishlist(): void
    {
        $this->get(route('wishlist.index'))->assertRedirect(route('login'));
    }

    public function test_non_customers_cannot_use_the_wishlist(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('wishlist.index'))->assertForbidden();
    }
}
