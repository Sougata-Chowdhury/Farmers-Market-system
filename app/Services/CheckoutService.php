<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Mail\OrderPlaced;
use App\Mail\OrderPlacedForFarmer;
use App\Mail\OrderStatusChanged;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewSaleNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    public function placeOrder(
        User $user,
        array $delivery,
        PaymentMethod $paymentMethod = PaymentMethod::Cod,
        ?int $addressId = null,
        ?Coupon $coupon = null,
    ): Order {
        $order = DB::transaction(function () use ($user, $delivery, $paymentMethod, $addressId, $coupon): Order {
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

            $lockedCoupon = $coupon !== null
                ? Coupon::query()->whereKey($coupon->id)->lockForUpdate()->first()
                : null;

            $discount = 0.0;

            if ($lockedCoupon !== null) {
                if (! $lockedCoupon->isAvailable()) {
                    throw ValidationException::withMessages([
                        'coupon' => 'This coupon is no longer available.',
                    ]);
                }

                if (! $lockedCoupon->meetsMinimum($subtotal)) {
                    throw ValidationException::withMessages([
                        'coupon' => 'Your cart no longer meets the minimum spend for this coupon.',
                    ]);
                }

                $discount = $lockedCoupon->discountFor($subtotal);
            }

            $order = $user->orders()->create([
                'status' => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
                'address_id' => $addressId,
                'coupon_id' => $lockedCoupon?->id,
                'coupon_code' => $lockedCoupon?->code,
                'delivery_name' => $delivery['name'],
                'delivery_phone' => $delivery['phone'],
                'delivery_address' => $delivery['address'],
                'notes' => $delivery['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'platform_fee' => $fee,
                'total' => round($subtotal - $discount + $fee, 2),
                'placed_at' => now(),
            ]);

            $lockedCoupon?->increment('used_count');

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

        $this->notifyOrderPlaced($order);

        return $order;
    }

    /**
     * Send the receipt to the customer and a sale alert to every farmer.
     */
    private function notifyOrderPlaced(Order $order): void
    {
        $order->loadMissing(['items.farmer', 'user']);

        try {
            Mail::to($order->user)->queue(new OrderPlaced($order));
            $order->user->notify(new OrderPlacedNotification($order));

            foreach ($order->items->groupBy('farmer_id') as $items) {
                $farmer = $items->first()->farmer;

                if ($farmer === null) {
                    continue;
                }

                Mail::to($farmer)->queue(new OrderPlacedForFarmer($order, $farmer, $items));
                $farmer->notify(new NewSaleNotification($order, $farmer, $items));
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function notifyStatusChanged(Order $order, OrderStatus $status): void
    {
        $order->loadMissing('user');

        try {
            Mail::to($order->user)->queue(new OrderStatusChanged($order, $status));
            $order->user->notify(new OrderStatusChangedNotification($order, $status));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Cancel an order and return the reserved stock to the farmers.
     */
    public function cancel(Order $order, User $actor): void
    {
        $updated = DB::transaction(function () use ($order, $actor): Order {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $this->cancelLocked($locked, $actor);

            return $locked->fresh();
        });

        $this->notifyStatusChanged($updated, OrderStatus::Cancelled);
    }

    /**
     * Move an order forward and record the change on its timeline.
     */
    public function updateStatus(Order $order, OrderStatus $target, User $actor): void
    {
        $updated = DB::transaction(function () use ($order, $target, $actor): Order {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($target === $locked->status) {
                throw ValidationException::withMessages([
                    'status' => 'Order already has that status.',
                ]);
            }

            if ($target === OrderStatus::Cancelled) {
                $this->cancelLocked($locked, $actor);

                return $locked->fresh();
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

            return $locked;
        });

        $this->notifyStatusChanged($updated, $target);
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
