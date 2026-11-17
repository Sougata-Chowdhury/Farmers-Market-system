<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()
            ->withCount('orders')
            ->latest('id')
            ->paginate(20);

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'stats' => [
                'total' => Coupon::count(),
                'active' => Coupon::available()->count(),
                'redemptions' => (int) Coupon::sum('used_count'),
                'discount_given' => round((float) Order::whereNotNull('coupon_id')->sum('discount'), 2),
            ],
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::create($request->validated());

        return back()->with('status', "Coupon {$coupon->code} created.");
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('status', "Coupon {$coupon->code} ".($coupon->is_active ? 'enabled' : 'disabled').'.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('status', "Coupon {$coupon->code} deleted.");
    }
}
