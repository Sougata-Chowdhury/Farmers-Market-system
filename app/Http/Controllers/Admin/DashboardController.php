<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $validOrders = Order::query()->where('status', '!=', OrderStatus::Cancelled);

        $stats = [
            'revenue' => round((float) (clone $validOrders)->sum('total'), 2),
            'fees' => round((float) (clone $validOrders)->sum('platform_fee'), 2),
            'orders' => Order::count(),
            'pending' => Order::where('status', OrderStatus::Pending)->count(),
            'customers' => User::where('role', UserRole::Customer)->count(),
            'farmers' => User::where('role', UserRole::Farmer)->count(),
            'products' => Product::count(),
            'average_order' => round((float) (clone $validOrders)->avg('total'), 2),
        ];

        $statusCounts = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $recentOrders = Order::with('user:id,name')
            ->latest('placed_at')
            ->limit(8)
            ->get();

        $topProducts = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as units, SUM(line_total) as revenue')
            ->whereHas('order', fn ($query) => $query->where('status', '!=', OrderStatus::Cancelled))
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        $revenueSeries = $this->revenueSeries();
        $topCategories = $this->topCategories();

        $statusSegments = collect(OrderStatus::cases())
            ->map(fn (OrderStatus $status): array => [
                'label' => $status->label(),
                'value' => (int) ($statusCounts[$status->value] ?? 0),
                'color' => match ($status) {
                    OrderStatus::Pending => '#f59e0b',
                    OrderStatus::Confirmed => '#0ea5e9',
                    OrderStatus::Delivered => '#059669',
                    OrderStatus::Cancelled => '#ef4444',
                },
            ])
            ->all();

        return view('admin.dashboard', compact(
            'stats',
            'statusCounts',
            'recentOrders',
            'topProducts',
            'revenueSeries',
            'topCategories',
            'statusSegments',
        ));
    }

    /**
     * Revenue for each of the last 30 days, including days without orders.
     *
     * @return array<int, array{label: string, value: float}>
     */
    private function revenueSeries(): array
    {
        $start = CarbonImmutable::today()->subDays(29);

        $revenue = Order::query()
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('placed_at', '>=', $start)
            ->selectRaw('DATE(placed_at) as day, SUM(total) as revenue')
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

    /**
     * @return Collection<int, Category>
     */
    private function topCategories(): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->selectRaw('categories.id, categories.name, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as units')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
    }
}
