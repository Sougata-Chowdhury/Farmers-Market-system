<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReorderService
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * Put the products from a past order back in the cart, skipping anything
     * that is no longer sellable or cannot satisfy the requested quantity.
     *
     * @return array{added: int, skipped: int, units: int}
     */
    public function addToCart(User $user, Order $order): array
    {
        $order->loadMissing('items');

        $added = 0;
        $skipped = 0;
        $units = 0;

        foreach ($order->items as $item) {
            $product = Product::withTrashed()->find($item->product_id);

            if ($product === null || $product->trashed() || ! $product->is_active || $product->stock < 1) {
                $skipped++;

                continue;
            }

            try {
                $this->cart->add($user, $product, min($item->quantity, $product->stock));
                $added++;
                $units += min($item->quantity, $product->stock);
            } catch (ValidationException) {
                $skipped++;
            }
        }

        return ['added' => $added, 'skipped' => $skipped, 'units' => $units];
    }
}
