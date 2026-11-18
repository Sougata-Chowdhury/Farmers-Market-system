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
        /** @var array<int, array<string, mixed>> $catalogue */
        $catalogue = array_merge(
            require database_path('seeders/data/products.php'),
            require database_path('seeders/data/extra-products.php'),
        );

        $farmers = User::where('role', UserRole::Farmer)->orderBy('id')->get(['id', 'email', 'name']);

        if ($farmers->isEmpty()) {
            $this->command?->warn('No farmers found. Run DemoUsersSeeder first.');

            return;
        }

        /** @var array<int, array<string, mixed>> $farmerData */
        $farmerData = require database_path('seeders/data/farmers.php');
        $profiles = collect($farmerData)->keyBy('email');

        // Match each category to farmers who actually grow or raise it, so the
        // storefronts read like real farms rather than a random round-robin.
        $byCategory = [];
        foreach ($farmers as $farmer) {
            $specialties = $profiles->get($farmer->email)['specialties'] ?? [];

            foreach ($specialties as $category) {
                $byCategory[$category][] = $farmer->id;
            }
        }

        $allFarmerIds = $farmers->pluck('id')->all();
        $categories = Category::pluck('id', 'name');
        $cursors = [];

        foreach ($catalogue as $item) {
            $category = $item['category'];
            $categoryId = $categories[$category] ?? $categories->first();
            $pool = $byCategory[$category] ?? $allFarmerIds;

            $position = $cursors[$category] ?? 0;
            $farmerId = $pool[$position % count($pool)];
            $cursors[$category] = $position + 1;

            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'farmer_id' => $farmerId,
                    'category_id' => $categoryId,
                    'description' => $item['description'] ?? "Fresh {$item['name']} sourced directly from a local farm.",
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'unit' => $item['unit'] ?? 'kg',
                    'image_path' => ProductPlaceholder::ensure($item['name'], $category),
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info(sprintf(
            'Seeded %d products across %d farmers and %d categories.',
            count($catalogue),
            $farmers->count(),
            $categories->count()
        ));
    }
}
