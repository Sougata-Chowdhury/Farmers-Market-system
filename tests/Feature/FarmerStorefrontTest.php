<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmers_get_a_public_slug_when_created(): void
    {
        $farmer = User::factory()->farmer()->create(['name' => 'Rafiq Hasan']);

        $this->assertSame('rafiq-hasan', $farmer->slug);

        $duplicate = User::factory()->farmer()->create(['name' => 'Rafiq Hasan']);

        $this->assertSame('rafiq-hasan-2', $duplicate->slug);
    }

    public function test_storefront_lists_only_the_farmers_active_products(): void
    {
        $farmer = User::factory()->farmer()->create();
        $farmer->farmerProfile()->create([
            'farm_name' => 'Sunrise Farm',
            'location' => 'Savar, Dhaka',
            'bio' => 'We grow leafy greens.',
        ]);

        $visible = $this->product($farmer, ['name' => 'Red Spinach']);
        $this->product($farmer, ['name' => 'Hidden Pumpkin', 'is_active' => false]);

        $response = $this->get(route('farmers.show', $farmer));

        $response
            ->assertOk()
            ->assertSee('Sunrise Farm')
            ->assertSee('Savar, Dhaka')
            ->assertSee('We grow leafy greens.')
            ->assertSee('Red Spinach')
            ->assertDontSee('Hidden Pumpkin')
            ->assertSee($visible->farmer->name);
    }

    public function test_customers_do_not_have_a_storefront(): void
    {
        $customer = User::factory()->create();

        $this->get($customer->slug ? "/farmers/{$customer->slug}" : '/farmers/not-a-farmer')
            ->assertNotFound();
    }

    public function test_product_page_links_to_the_farmer_storefront(): void
    {
        $farmer = User::factory()->farmer()->create();
        $product = $this->product($farmer);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee(route('farmers.show', $farmer), false);
    }

    public function test_farmers_can_update_their_storefront_details(): void
    {
        $farmer = User::factory()->farmer()->create();

        $this->actingAs($farmer)
            ->patch(route('farmer.profile.update'), [
                'farm_name' => 'Green Valley',
                'location' => 'Narsingdi',
                'bio' => 'Seasonal fruit and vegetables.',
                'bank_name' => 'BRAC Bank',
                'branch' => 'Savar Branch',
                'account_no' => '1234567890',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('farmers', [
            'user_id' => $farmer->id,
            'farm_name' => 'Green Valley',
            'location' => 'Narsingdi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function product(User $farmer, array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Vegetables'],
            ['slug' => 'vegetables'],
        );

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
