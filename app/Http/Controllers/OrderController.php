<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\ReorderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly ReorderService $reorder,
    ) {}

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

        $order->load(['items.farmer:id,name', 'user:id,name,email,phone', 'histories.actor:id,name,role']);

        return view('orders.show', ['order' => $order]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $this->checkout->cancel($order, $request->user());

        return back()->with('status', "Order #{$order->id} has been cancelled.");
    }

    public function reorder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $result = $this->reorder->addToCart($request->user(), $order);

        if ($result['added'] === 0) {
            return back()->with('error', 'None of the products on this order are available right now.');
        }

        $message = "Added {$result['units']} "
            .Str::plural('unit', $result['units'])
            .' from order #'.$order->id.' to your cart.';

        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} unavailable "
                .Str::plural('product', $result['skipped'])
                .' skipped.';
        }

        return redirect()->route('cart.index')->with('status', $message);
    }
}
