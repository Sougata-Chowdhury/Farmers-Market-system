<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', UserRole::Customer)->get();
        $products = Product::inRandomOrder()->limit(15)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $samples = [
            [5, 'Very fresh and delivered on time. Will buy again.'],
            [4, 'Good quality, slightly pricey compared to the local bazaar.'],
            [5, 'Exactly as pictured. The farmer packed it carefully.'],
            [3, 'Decent, but a couple of pieces were bruised.'],
            [4, 'Happy with the purchase.'],
        ];

        foreach ($products as $index => $product) {
            [$rating, $body] = $samples[$index % count($samples)];
            $customer = $customers[$index % $customers->count()];

            Review::updateOrCreate(
                ['product_id' => $product->id, 'user_id' => $customer->id],
                ['rating' => $rating, 'body' => $body]
            );
        }
    }
}
