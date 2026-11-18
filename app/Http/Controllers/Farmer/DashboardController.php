<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            ->orderByDesc(Order::select('placed_at')->whereColumn('orders.id', 'order_items.order_id'))
            ->limit(8)
            ->get();

        $topProducts = $user->products()
            ->with('category:id,name')
            ->withSum(['orderItems as units_sold' => fn ($query) => $query->whereHas(
                'order',
                fn ($query) => $query->where('status', '!=', 'cancelled')
            )], 'quantity')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $lowStockProducts = $user->products()
            ->where('is_active', true)
            ->where('stock', '<=', config('marketplace.low_stock_threshold'))
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('farmer.dashboard', [
            'stats' => $stats,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'salesSeries' => $this->salesSeries($user->id),
            'lastThirtyDays' => round((float) (clone $sales)
                ->whereHas('order', fn ($query) => $query->where('placed_at', '>=', now()->subDays(30)))
                ->sum('line_total'), 2),
        ]);
    }

    /**
     * Daily sales for the last 30 days for this farmer, zero-filled.
     *
     * @return array<int, array{label: string, value: float}>
     */
    private function salesSeries(int $farmerId): array
    {
        $start = CarbonImmutable::today()->subDays(29);

        $revenue = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.farmer_id', $farmerId)
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.placed_at', '>=', $start)
            ->selectRaw('DATE(orders.placed_at) as day, SUM(order_items.line_total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        return Collection::times(30, function (int $day) use ($start, $revenue): array {
            $date = $start->addDays($day - 1);

            return [
                'label' => $date->format('d M'),
                'value' => round((float) ($revenue[$date->toDateString()] ?? 0), 2),
            ];
        })->all();
    }
}
