<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Convert the customer's cart into an order.
     *
     * The whole operation runs in a single transaction with row locks on the
     * products so two customers cannot buy the same last unit.
     *
     * @param  array{name: string, phone: string, address: string, notes?: ?string}  $delivery
     */
    public function placeOrder(User $user, array $delivery): Order
    {
        return DB::transaction(function () use ($user, $delivery): Order {
            /** @var Collection<int, Cart> $carts */
            $carts = $user->carts()->lockForUpdate()->get();

            if ($carts->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            /** @var Collection<int, Product> $products */
            $products = Product::withTrashed()
                ->whereIn('id', $carts->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $lines = [];

            foreach ($carts as $cart) {
                $product = $products->get($cart->product_id);

                if (! $product || $product->trashed() || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in your cart is no longer available. Please review your cart.',
                    ]);
                }

                if ($cart->quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$product->stock} {$product->unit} of {$product->name} are left in stock.",
                    ]);
                }

                $lineTotal = round((float) $product->price * $cart->quantity, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $cart->quantity,
                    'unit_price' => (float) $product->price,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $fee = round($subtotal * (float) config('marketplace.platform_fee'), 2);

            $order = $user->orders()->create([
                'status' => OrderStatus::Pending,
                'delivery_name' => $delivery['name'],
                'delivery_phone' => $delivery['phone'],
                'delivery_address' => $delivery['address'],
                'notes' => $delivery['notes'] ?? null,
                'subtotal' => $subtotal,
                'platform_fee' => $fee,
                'total' => round($subtotal + $fee, 2),
                'placed_at' => now(),
            ]);

            $order->recordHistory(OrderStatus::Pending, $user, 'Order placed and stock reserved.');

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'farmer_id' => $line['product']->farmer_id,
                    'product_name' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            $user->carts()->delete();

            return $order->load('items');
        });
    }

    /**
     * Cancel an order and return the reserved stock to the farmers.
     */
    public function cancel(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order, $actor): void {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $this->cancelLocked($locked, $actor);
        });
    }

    /**
     * Move an order forward and record the change on its timeline.
     */
    public function updateStatus(Order $order, OrderStatus $target, User $actor): void
    {
        DB::transaction(function () use ($order, $target, $actor): void {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($target === $locked->status) {
                throw ValidationException::withMessages([
                    'status' => 'Order already has that status.',
                ]);
            }

            if ($target === OrderStatus::Cancelled) {
                $this->cancelLocked($locked, $actor);

                return;
            }

            if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => "Cannot move an order from {$locked->status->label()} to {$target->label()}.",
                ]);
            }

            $locked->update(['status' => $target]);

            $locked->recordHistory($target, $actor, match ($target) {
                OrderStatus::Confirmed => 'Order confirmed and queued for packing.',
                OrderStatus::Delivered => 'Order marked as delivered.',
                default => null,
            });
        });
    }

    private function cancelLocked(Order $order, User $actor): void
    {
        abort_unless($this->canCancel($order, $actor), 403);

        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed], true)) {
            throw ValidationException::withMessages([
                'order' => 'This order can no longer be cancelled.',
            ]);
        }

        foreach ($order->items as $item) {
            Product::withTrashed()
                ->whereKey($item->product_id)
                ->increment('stock', $item->quantity);
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        $order->recordHistory(OrderStatus::Cancelled, $actor, 'Order cancelled and stock returned to farmers.');
    }

    public function canCancel(Order $order, User $actor): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->role === UserRole::Customer && $order->isCancellableBy($actor);
    }
}
