<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\RecentlyViewedService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly RecentlyViewedService $recentlyViewed) {}

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $this->recentlyViewed->track($request, $product);

        $product->load([
            'category:id,name,slug',
            'farmer:id,name,slug,address',
            'farmer.farmerProfile',
            'reviews' => fn ($query) => $query->with('user:id,name')->latest(),
        ]);

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with('farmer:id,name,slug')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $user = $request->user();

        return view('products.show', [
            'product' => $product,
            'related' => $related,
            'recentlyViewed' => $this->recentlyViewed->products($request, $product),
            'canReview' => $user?->isCustomer() === true
                && ! $product->reviews->contains('user_id', $user->id),
            'myReview' => $user?->isCustomer() === true
                ? $product->reviews->firstWhere('user_id', $user->id)
                : null,
        ]);
    }
}
