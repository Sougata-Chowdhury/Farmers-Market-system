<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductGallerySeeder extends Seeder
{
    /**
     * Which photos to prefer for a product, matched on its name first and its
     * category second. Photos live in database/seeders/data/photos.
     */
    private const KEYWORDS = [
        'tomato' => ['tomato'],
        'greens' => ['shak', 'spinach', 'palong', 'kolmi', 'dhonia', 'pui'],
        'potato' => ['potato', 'alu'],
        'onion' => ['onion', 'peyaj'],
        'lemon' => ['lemon', 'lebu'],
        'garlic' => ['garlic', 'roshun'],
        'pumpkin' => ['pumpkin', 'kumra', 'lao', 'gourd', 'potol', 'jhinga'],
    ];

    private const CATEGORY_FALLBACK = [
        'Vegetables' => ['tomato', 'greens', 'potato', 'pumpkin'],
        'Fruits' => ['lemon', 'pumpkin', 'tomato'],
        'Fish' => ['greens', 'tomato', 'lemon'],
        'Meat' => ['onion', 'tomato', 'garlic'],
        'Rice' => ['pumpkin', 'potato', 'onion'],
        'Dairy' => ['lemon', 'tomato', 'greens'],
    ];

    public function run(): void
    {
        $photos = $this->photos();

        if ($photos === []) {
            $this->command?->warn('No seed photos found in database/seeders/data/photos.');

            return;
        }

        Product::query()
            ->with('category:id,name')
            ->orderBy('id')
            ->each(function (Product $product, int $index) use ($photos): void {
                if ($product->getMedia('gallery')->isNotEmpty()) {
                    return;
                }

                $primary = $this->primaryKey($product);

                // Give the first rows a full gallery, the rest a single cover
                // so seeding stays fast and the grid does not look repetitive.
                $keys = $index < 24 ? $this->galleryKeys($primary, $photos) : [$primary];

                foreach ($keys as $position => $key) {
                    if (! isset($photos[$key])) {
                        continue;
                    }

                    $product->addMedia($photos[$key])
                        ->preservingOriginal()
                        ->usingFileName(Str::slug($product->name).'-'.($position + 1).'.webp')
                        ->withCustomProperties(['alt' => $product->name])
                        ->toMediaCollection('gallery');
                }
            });

        $this->command?->info('Attached gallery photos to the seeded catalogue.');
    }

    /**
     * @return array<string, string>
     */
    private function photos(): array
    {
        $files = glob(database_path('seeders/data/photos/*.webp')) ?: [];

        return collect($files)
            ->mapWithKeys(fn (string $path): array => [basename($path, '.webp') => $path])
            ->all();
    }

    private function primaryKey(Product $product): string
    {
        $name = Str::lower($product->name);

        foreach (self::KEYWORDS as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) {
                    return $key;
                }
            }
        }

        $fallback = self::CATEGORY_FALLBACK[$product->category?->name] ?? ['tomato', 'greens'];

        return $fallback[crc32($product->slug) % count($fallback)];
    }

    /**
     * @param  array<string, string>  $photos
     * @return array<int, string>
     */
    private function galleryKeys(string $primary, array $photos): array
    {
        $pool = array_values(array_diff(array_keys($photos), [$primary]));
        $offset = crc32($primary) % max(1, count($pool));

        $extras = [];
        for ($i = 0; $i < 2; $i++) {
            $extras[] = $pool[($offset + $i) % count($pool)];
        }

        return array_values(array_unique(array_merge([$primary], $extras)));
    }
}
