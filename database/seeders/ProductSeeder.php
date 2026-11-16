<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductPlaceholder;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, array{name: string, category: string, price: string, stock: int, image: ?string}> $catalogue */
        $catalogue = require database_path('seeders/data/products.php');

        $farmers = User::where('role', UserRole::Farmer)->orderBy('id')->pluck('id')->all();

        if ($farmers === []) {
            $this->command?->warn('No farmers found. Run DemoUsersSeeder first.');

            return;
        }

        $categories = Category::pluck('id', 'name');

        foreach ($catalogue as $index => $item) {
            $categoryId = $categories[$item['category']] ?? $categories->first();

            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'farmer_id' => $farmers[$index % count($farmers)],
                    'category_id' => $categoryId,
                    'description' => "Fresh {$item['name']} sourced directly from a local farm.",
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'unit' => 'kg',
                    'image_path' => ProductPlaceholder::ensure($item['name'], $item['category']),
                    'is_active' => true,
                ]
            );
        }
    }
}
