<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\View\View;

class FarmerController extends Controller
{
    public function index(): View
    {
        $farmers = User::query()
            ->where('role', UserRole::Farmer)
            ->with('farmerProfile')
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);

        $sales = OrderItem::query()
            ->selectRaw('farmer_id, SUM(line_total) as earnings, SUM(quantity) as units, COUNT(DISTINCT order_id) as orders')
            ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'))
            ->groupBy('farmer_id')
            ->get()
            ->keyBy('farmer_id');

        return view('admin.farmers.index', compact('farmers', 'sales'));
    }
}
