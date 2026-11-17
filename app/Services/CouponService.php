<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * Find a coupon that can be applied to the given subtotal.
     *
     * @throws ValidationException
     */
    public function find(string $code, float $subtotal): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', Coupon::normalizeCode($code))
            ->first();

        if ($coupon === null) {
            throw ValidationException::withMessages([
                'code' => 'That coupon code does not exist.',
            ]);
        }

        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'code' => 'This coupon is no longer active.',
            ]);
        }

        if ($coupon->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'This coupon expired on '.$coupon->expires_at->format('d M Y').'.',
            ]);
        }

        if ($coupon->isExhausted()) {
            throw ValidationException::withMessages([
                'code' => 'This coupon has been fully redeemed.',
            ]);
        }

        if (! $coupon->meetsMinimum($subtotal)) {
            throw ValidationException::withMessages([
                'code' => "Spend at least {$this->money((float) $coupon->min_spend)} BDT to use this coupon.",
            ]);
        }

        return $coupon;
    }

    /**
     * Resolve the coupon stored in the session, dropping it if it is no
     * longer valid for the current cart.
     */
    public function forSession(Request $request, float $subtotal): ?Coupon
    {
        $code = $request->session()->get('coupon_code');

        if (blank($code)) {
            return null;
        }

        try {
            return $this->find((string) $code, $subtotal);
        } catch (ValidationException $exception) {
            $request->session()->forget('coupon_code');
            $request->session()->flash('error', $exception->validator->errors()->first('code'));

            return null;
        }
    }

    private function money(float $value): string
    {
        return number_format($value, 2);
    }
}
