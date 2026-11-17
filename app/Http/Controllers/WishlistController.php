<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\RecentlyViewedService;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
        private readonly RecentlyViewedService $recentlyViewed,
    ) {}

    public function index(Request $request): View
    {
        return view('wishlist.index', [
            'products' => $this->wishlist->products($request),
            'recentlyViewed' => $this->recentlyViewed->products($request),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->wishlist->add($request, $product);

        return back()->with('status', "{$product->name} saved to your wishlist.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->wishlist->remove($request, $product);

        return back()->with('status', "{$product->name} removed from your wishlist.");
    }
}
