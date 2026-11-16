<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $totals = $this->cart->totals($request->user());

        if ($totals['count'] === 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Add something to your cart before checking out.');
        }

        return view('checkout.index', [
            'items' => $this->cart->items($request->user()),
            'totals' => $totals,
            'customer' => $request->user(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->placeOrder($request->user(), $request->validated());

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Order #{$order->id} placed successfully.");
    }
}
