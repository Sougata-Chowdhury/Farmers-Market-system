<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CouponService $coupons,
    ) {}

    public function index(Request $request): View
    {
        $state = $this->state($request);

        return view('cart.index', [
            'items' => $state['items'],
            'totals' => $state['totals'],
            'coupon' => $state['coupon'],
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
        $state = $this->state($request);

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'coupon' => $state['coupon']?->code] + $state['totals']);
        }

        return back()->with('status', $message);
    }

    /**
     * @return array{items: Collection<int, Cart>, coupon: ?Coupon, totals: array{subtotal: float, discount: float, fee: float, total: float, count: int}}
     */
    private function state(Request $request): array
    {
        $user = $request->user();
        $base = $this->cart->totals($user);
        $coupon = $this->coupons->forSession($request, $base['subtotal']);

        return [
            'items' => $this->cart->items($user),
            'coupon' => $coupon,
            'totals' => $this->cart->totals($user, $coupon),
        ];
    }
}
