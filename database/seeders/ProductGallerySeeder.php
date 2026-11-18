<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductGallerySeeder extends Seeder
{
    /**
     * Photos are only attached when the product name actually matches the
     * picture. Anything else keeps its drawn placeholder rather than showing
     * a photo of the wrong produce.
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
            ->each(function (Product $product) use ($photos): void {
                if ($product->getMedia('gallery')->isNotEmpty()) {
                    return;
                }

                $key = $this->photoKey($product);

                if ($key === null || ! isset($photos[$key])) {
                    return;
                }

                $product->addMedia($photos[$key])
                    ->preservingOriginal()
                    ->usingFileName(Str::slug($product->name).'-1.webp')
                    ->withCustomProperties(['alt' => $product->name])
                    ->toMediaCollection('gallery');
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

    private function photoKey(Product $product): ?string
    {
        $name = Str::lower($product->name);

        foreach (self::KEYWORDS as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) {
                    return $key;
                }
            }
        }

        return null;
    }
}
