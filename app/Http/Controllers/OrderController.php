<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(Request $request): View
    {
        $status = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,delivered,cancelled'],
        ])['status'] ?? null;

        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->withStatus($status)
            ->latest('placed_at')
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        abort_unless(
            $order->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $order->load(['items.farmer:id,name', 'user:id,name,email,phone']);

        return view('orders.show', ['order' => $order]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $this->checkout->cancel($order, $request->user());

        return back()->with('status', "Order #{$order->id} has been cancelled.");
    }
}
