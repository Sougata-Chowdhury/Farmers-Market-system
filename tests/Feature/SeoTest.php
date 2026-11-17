<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_exposes_open_graph_and_twitter_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:description"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_product_pages_publish_product_structured_data(): void
    {
        $product = $this->product();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"priceCurrency":"BDT"', false)
            ->assertSee('"availability":"https://schema.org/InStock"', false);
    }

    public function test_private_pages_are_noindex(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_the_sitemap_lists_live_products_and_farmers(): void
    {
        $product = $this->product();
        $product->farmer->update(['slug' => 'rafiq-hasan']);

        $hidden = $this->product(['name' => 'Hidden Pumpkin', 'is_active' => false]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringStartsWith('application/xml', (string) $response->headers->get('Content-Type'));

        $xml = $response->getContent();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString(route('products.show', $product), $xml);
        $this->assertStringContainsString(route('farmers.show', $product->farmer), $xml);
        $this->assertStringNotContainsString('Hidden Pumpkin', $xml);
        $this->assertStringNotContainsString($hidden->slug, $xml);
    }

    public function test_robots_points_search_engines_at_the_sitemap(): void
    {
        $response = $this->get(route('robots'));

        $response->assertOk();

        $body = $response->getContent();

        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $body);
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
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
