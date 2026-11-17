<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    private const KEY = 'recently_viewed';

    private const LIMIT = 8;

    /**
     * Record that the customer looked at a product.
     */
    public function track(Request $request, Product $product): void
    {
        $ids = array_values(array_filter(
            $this->ids($request),
            fn (int $id): bool => $id !== $product->id
        ));

        array_unshift($ids, $product->id);

        $request->session()->put(self::KEY, array_slice($ids, 0, self::LIMIT));
    }

    /**
     * @return array<int, int>
     */
    public function ids(Request $request): array
    {
        $ids = $request->session()->get(self::KEY, []);

        return is_array($ids) ? array_values(array_map('intval', $ids)) : [];
    }

    /**
     * Recently viewed products, newest first, optionally excluding one.
     *
     * @return Collection<int, Product>
     */
    public function products(Request $request, ?Product $exclude = null): Collection
    {
        $ids = array_values(array_filter(
            $this->ids($request),
            fn (int $id): bool => $exclude === null || $id !== $exclude->id
        ));

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', $ids)
            ->active()
            ->with(['category:id,name,slug', 'farmer:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?Product => $products->get($id))
            ->filter()
            ->values()
            ->take(self::LIMIT);
    }
}
