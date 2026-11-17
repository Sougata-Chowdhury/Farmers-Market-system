<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CouponService $coupons,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ]);

        $totals = $this->cart->totals($request->user());

        if ($totals['count'] === 0) {
            return back()->with('error', 'Add something to your cart before applying a coupon.');
        }

        $coupon = $this->coupons->find($data['code'], $totals['subtotal']);

        $request->session()->put('coupon_code', $coupon->code);

        return back()->with('status', "Coupon {$coupon->code} applied to this order.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon_code');

        return back()->with('status', 'Coupon removed.');
    }
}
