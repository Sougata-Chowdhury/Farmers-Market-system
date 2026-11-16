<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Request $request): View
    {
        return view('cart.index', [
            'items' => $this->cart->items($request->user()),
            'totals' => $this->cart->totals($request->user()),
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $this->cart->add($request->user(), $product, (int) ($data['quantity'] ?? 1));

        return $this->respond($request, "{$product->name} added to your cart.");
    }

    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $this->cart->update($request->user(), $product, (int) $data['quantity']);

        return $this->respond($request, 'Cart updated.');
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->cart->remove($request->user(), $product);

        return $this->respond($request, "{$product->name} removed from your cart.");
    }

    private function respond(Request $request, string $message): JsonResponse|RedirectResponse
    {
        $totals = $this->cart->totals($request->user());

        if ($request->expectsJson()) {
            return response()->json(['message' => $message] + $totals);
        }

        return back()->with('status', $message);
    }
}
