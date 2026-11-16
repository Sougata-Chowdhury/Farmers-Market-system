<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $sales = OrderItem::query()
            ->where('farmer_id', $user->id)
            ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'));

        $stats = [
            'earnings' => round((float) (clone $sales)->sum('line_total'), 2),
            'units_sold' => (int) (clone $sales)->sum('quantity'),
            'orders' => (clone $sales)->distinct()->count('order_id'),
            'active_products' => $user->products()->count(),
            'low_stock' => $user->products()->where('is_active', true)->where('stock', '<=', config('marketplace.low_stock_threshold'))->count(),
        ];

        $recentSales = (clone $sales)
            ->with(['order:id,status,placed_at,user_id', 'order.user:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        $topProducts = $user->products()
            ->withSum(['orderItems as units_sold' => fn ($query) => $query->whereHas(
                'order',
                fn ($query) => $query->where('status', '!=', 'cancelled')
            )], 'quantity')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return view('farmer.dashboard', compact('stats', 'recentSales', 'topProducts'));
    }
}
