<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * @return Collection<int, Cart>
     */
    public function items(User $user): Collection
    {
        return $user->carts()
            ->with(['product' => fn ($query) => $query->withTrashed()])
            ->get();
    }

    /**
     * @return array{subtotal: float, discount: float, fee: float, total: float, count: int}
     */
    public function totals(User $user, ?Coupon $coupon = null): array
    {
        $subtotal = 0.0;
        $count = 0;

        foreach ($this->items($user) as $item) {
            if ($item->product === null) {
                continue;
            }

            $subtotal += (float) $item->product->price * $item->quantity;
            $count += $item->quantity;
        }

        $subtotal = round($subtotal, 2);
        $discount = $coupon !== null ? $coupon->discountFor($subtotal) : 0.0;
        $fee = round($subtotal * (float) config('marketplace.platform_fee'), 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'fee' => $fee,
            'total' => round($subtotal - $discount + $fee, 2),
            'count' => $count,
        ];
    }

    public function add(User $user, Product $product, int $quantity = 1): Cart
    {
        $quantity = max(1, $quantity);
        $existing = $user->carts()->where('product_id', $product->id)->first();
        $wanted = $quantity + ($existing?->quantity ?? 0);

        $this->assertCanBuy($product, $wanted);

        return $user->carts()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $wanted]
        );
    }

    public function update(User $user, Product $product, int $quantity): void
    {
        $this->assertCanBuy($product, $quantity);

        $user->carts()
            ->where('product_id', $product->id)
            ->update(['quantity' => $quantity]);
    }

    public function remove(User $user, Product $product): void
    {
        $user->carts()->where('product_id', $product->id)->delete();
    }

    public function clear(User $user): void
    {
        $user->carts()->delete();
    }

    private function assertCanBuy(Product $product, int $quantity): void
    {
        if (! $product->is_active || $product->trashed()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is no longer available.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock} {$product->unit} of {$product->name} are available.",
            ]);
        }
    }
}
