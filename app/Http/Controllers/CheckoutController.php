<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly CouponService $coupons,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $base = $this->cart->totals($user);

        if ($base['count'] === 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Add something to your cart before checking out.');
        }

        $coupon = $this->coupons->forSession($request, $base['subtotal']);

        return view('checkout.index', [
            'items' => $this->cart->items($user),
            'totals' => $this->cart->totals($user, $coupon),
            'coupon' => $coupon,
            'customer' => $user,
            'addresses' => $user->addresses()->orderByDesc('is_default')->orderBy('id')->get(),
            'defaultAddress' => $user->defaultAddress(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $address = null;

        $base = $this->cart->totals($user);
        $code = $request->session()->get('coupon_code');
        $coupon = blank($code) ? null : $this->coupons->find((string) $code, $base['subtotal']);

        if (filled($data['address_id'] ?? null)) {
            $address = $user->addresses()->find($data['address_id']);

            if ($address === null) {
                throw ValidationException::withMessages([
                    'address_id' => 'That delivery address is no longer available.',
                ]);
            }

            $delivery = [
                'name' => $address->recipient_name,
                'phone' => $address->phone,
                'address' => trim($address->address_line.($address->area ? ', '.$address->area : '')),
                'notes' => $data['notes'] ?? null,
            ];
        } else {
            $delivery = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'notes' => $data['notes'] ?? null,
            ];
        }

        $order = $this->checkout->placeOrder(
            $user,
            $delivery,
            PaymentMethod::from($data['payment_method']),
            $address?->id,
            $coupon,
        );

        $request->session()->forget('coupon_code');

        if ($address === null && ($data['save_address'] ?? false)) {
            $isFirst = $user->addresses()->count() === 0;

            $saved = $user->addresses()->create([
                'label' => ($data['label'] ?? null) ?: 'Home',
                'recipient_name' => $delivery['name'],
                'phone' => $delivery['phone'],
                'address_line' => $delivery['address'],
                'is_default' => $isFirst,
            ]);

            if ($isFirst) {
                $saved->makeDefault();
            }
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Order #{$order->id} placed successfully.");
    }
}
