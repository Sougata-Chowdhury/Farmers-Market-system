<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,delivered,cancelled'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = Order::query()
            ->with('user:id,name')
            ->withCount('items')
            ->withStatus($filters['status'] ?? null)
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('id', $term)
                        ->orWhereHas('user', fn ($query) => $query
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.farmer:id,name', 'user', 'histories.actor:id,name,role']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $target = OrderStatus::from($data['status']);

        if ($target === $order->status) {
            return back()->with('status', 'Order already has that status.');
        }

        $this->checkout->updateStatus($order, $target, $request->user());

        return back()->with('status', $target === OrderStatus::Cancelled
            ? "Order #{$order->id} cancelled and stock restored."
            : "Order #{$order->id} marked as {$target->label()}.");
    }
}
