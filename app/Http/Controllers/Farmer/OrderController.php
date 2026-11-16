<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->whereHas('items', fn ($query) => $query->where('farmer_id', $user->id))
            ->with([
                'user:id,name',
                'items' => fn ($query) => $query->where('farmer_id', $user->id),
            ])
            ->latest('placed_at')
            ->paginate(10);

        $earnings = $orders->getCollection()->sum(
            fn (Order $order) => $order->items->sum('line_total')
        );

        return view('farmer.orders', compact('orders', 'earnings'));
    }
}
