<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerStorefrontController extends Controller
{
    public function show(Request $request, User $farmer): View
    {
        abort_unless($farmer->isFarmer(), 404);

        $products = $farmer->products()
            ->active()
            ->with(['category:id,name,slug', 'farmer:id,name,slug'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $reviewsQuery = Review::query()
            ->whereHas('product', fn ($query) => $query->where('farmer_id', $farmer->id));

        $rating = round((float) (clone $reviewsQuery)->avg('rating'), 1);
        $reviewCount = (clone $reviewsQuery)->count();

        $reviews = (clone $reviewsQuery)
            ->with(['user:id,name', 'product:id,name,slug'])
            ->latest()
            ->limit(4)
            ->get();

        $ordersFulfilled = OrderItem::query()
            ->where('farmer_id', $farmer->id)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::Delivered))
            ->distinct()
            ->count('order_id');

        return view('farmers.show', [
            'farmer' => $farmer->load('farmerProfile'),
            'products' => $products,
            'reviews' => $reviews,
            'rating' => $rating,
            'reviewCount' => $reviewCount,
            'ordersFulfilled' => $ordersFulfilled,
        ]);
    }
}
