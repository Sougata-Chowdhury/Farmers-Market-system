<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'revenue' => round((float) Order::where('status', '!=', OrderStatus::Cancelled)->sum('total'), 2),
            'fees' => round((float) Order::where('status', '!=', OrderStatus::Cancelled)->sum('platform_fee'), 2),
            'orders' => Order::count(),
            'pending' => Order::where('status', OrderStatus::Pending)->count(),
            'customers' => User::where('role', UserRole::Customer)->count(),
            'farmers' => User::where('role', UserRole::Farmer)->count(),
            'products' => Product::count(),
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

        return view('admin.dashboard', compact('stats', 'statusCounts', 'recentOrders', 'topProducts'));
    }
}
