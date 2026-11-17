<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WishlistService
{
    private const KEY = 'wishlist';

    /**
     * @return array<int, int>
     */
    public function ids(Request $request): array
    {
        $ids = $request->session()->get(self::KEY, []);

        return is_array($ids) ? array_values(array_map('intval', $ids)) : [];
    }

    public function count(Request $request): int
    {
        return count($this->ids($request));
    }

    public function has(Request $request, Product $product): bool
    {
        return in_array($product->id, $this->ids($request), true);
    }

    public function add(Request $request, Product $product): void
    {
        $ids = $this->ids($request);

        if (! in_array($product->id, $ids, true)) {
            array_unshift($ids, $product->id);
        }

        $request->session()->put(self::KEY, array_slice($ids, 0, 50));
    }

    public function remove(Request $request, Product $product): void
    {
        $ids = array_values(array_filter(
            $this->ids($request),
            fn (int $id): bool => $id !== $product->id
        ));

        $request->session()->put(self::KEY, $ids);
    }

    public function toggle(Request $request, Product $product): bool
    {
        if ($this->has($request, $product)) {
            $this->remove($request, $product);

            return false;
        }

        $this->add($request, $product);

        return true;
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::KEY);
    }

    /**
     * Products saved to the wishlist, newest first.
     *
     * @return Collection<int, Product>
     */
    public function products(Request $request): Collection
    {
        $ids = $this->ids($request);

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', $ids)
            ->with(['category:id,name,slug', 'farmer:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?Product => $products->get($id))
            ->filter()
            ->values();
    }
}
