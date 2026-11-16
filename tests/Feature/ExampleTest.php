<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Farmers Market');
    }

    public function test_the_home_page_lists_active_products(): void
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
        $farmer = User::factory()->farmer()->create();

        Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Red Spinach',
            'description' => 'Fresh red spinach.',
            'price' => 40,
            'stock' => 20,
            'unit' => 'kg',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Red Spinach');
    }
}
