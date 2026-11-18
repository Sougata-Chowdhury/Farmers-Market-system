<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketplaceActivitySeeder extends Seeder
{
    /**
     * Seed a busy market: months of orders across every customer, status,
     * payment method and farmer, plus reviews on delivered produce.
     */
    public function run(): void
    {
        if (Order::query()->count() > 20) {
            $this->command?->info('Marketplace activity already present; skipping.');

            return;
        }

        $customers = User::where('role', UserRole::Customer)->with('addresses')->get();
        $products = Product::where('is_active', true)->get();
        $coupons = Coupon::all();
        $admin = User::where('role', UserRole::Admin)->first();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('Customers or products are missing; run the user and product seeders first.');

            return;
        }

        $demoCustomer = $customers->firstWhere('email', 'customer@farmersmarket.test');
        $demoStatuses = [
            OrderStatus::Delivered,
            OrderStatus::Confirmed,
            OrderStatus::Pending,
            OrderStatus::Cancelled,
        ];
        $notes = [
            null,
            null,
            null,
            'Please call before delivery.',
            'Leave with the building guard if I am not home.',
            'Ring the bell twice — the gate is usually locked.',
            'Deliver after 6 pm if possible.',
        ];
        $reviewBodies = [
            'Very fresh and delivered on time. Will buy again.',
            'Good quality, slightly pricey compared to the local bazaar.',
            'Exactly as pictured. The farmer packed it carefully.',
            'Decent, but a couple of pieces were bruised.',
            'Happy with the purchase — the whole family liked it.',
            'The aroma when I opened the bag was wonderful.',
            'Ordered for a small gathering and everyone asked where it came from.',
            'Not the cheapest, but you can taste the difference.',
        ];
        $ratings = [5, 5, 5, 4, 4, 3];

        mt_srand(20261118);

        DB::transaction(function () use (
            $admin,
            $coupons,
            $customers,
            $demoCustomer,
            $demoStatuses,
            $notes,
            $products,
            $ratings,
            $reviewBodies,
        ): void {
            for ($index = 0; $index < 160; $index++) {
                $forced = $index < 4 && $demoCustomer !== null;

                $customer = $forced
                    ? $demoCustomer
                    : $customers[mt_rand(0, $customers->count() - 1)];

                $placedAt = $forced
                    ? now()->subDays([18, 5, 1, 40][$index])->setTime(mt_rand(8, 20), mt_rand(0, 59))
                    : now()->subDays(mt_rand(0, 119))->setTime(mt_rand(8, 21), mt_rand(0, 59));

                $status = $forced
                    ? $demoStatuses[$index]
                    : $this->pickStatus($placedAt);

                $lineCount = mt_rand(1, 4);
                $picked = [];
                $attempts = 0;

                while (count($picked) < $lineCount && $attempts < 40) {
                    $product = $products[mt_rand(0, $products->count() - 1)];
                    $picked[$product->id] = $product;
                    $attempts++;
                }

                $lines = [];
                $subtotal = 0.0;

                foreach ($picked as $product) {
                    $quantity = mt_rand(1, 3);
                    $unitPrice = (float) $product->price;
                    $lineTotal = round($unitPrice * $quantity, 2);

                    $lines[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                    $subtotal += $lineTotal;
                }

                $subtotal = round($subtotal, 2);
                $discount = 0.0;
                $coupon = null;

                if (mt_rand(1, 100) <= 20) {
                    $eligible = $coupons
                        ->filter(fn (Coupon $candidate): bool => $candidate->isAvailable() && $candidate->meetsMinimum($subtotal))
                        ->values();

                    if ($eligible->isNotEmpty()) {
                        $coupon = $eligible[mt_rand(0, $eligible->count() - 1)];
                        $discount = $coupon->discountFor($subtotal);
                    }
                }

                $fee = round($subtotal * (float) config('marketplace.platform_fee'), 2);
                $total = round(max(0, $subtotal - $discount + $fee), 2);
                $address = $customer->addresses->firstWhere('is_default', true)
                    ?? $customer->addresses->first();

                $order = $customer->orders()->create([
                    'status' => $status,
                    'payment_method' => $this->pickPayment(),
                    'address_id' => $address?->id,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'delivery_name' => $customer->name,
                    'delivery_phone' => $customer->phone,
                    'delivery_address' => trim(($address?->address_line ?? $customer->address).($address?->area ? ', '.$address->area : '')),
                    'notes' => $notes[mt_rand(0, count($notes) - 1)],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'platform_fee' => $fee,
                    'total' => $total,
                    'placed_at' => $placedAt,
                ]);

                DB::table('orders')->where('id', $order->id)->update([
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]);

                foreach ($lines as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'farmer_id' => $line['product']->farmer_id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'line_total' => $line['line_total'],
                    ]);
                }

                $this->seedTimeline($order, $status, $placedAt, $customer, $admin);

                if ($coupon !== null) {
                    $coupon->used_count++;
                    $coupon->save();
                }

                if ($status === OrderStatus::Delivered) {
                    $this->seedReviews($order, $customer, $placedAt, $ratings, $reviewBodies);
                }
            }
        });

        $this->command?->info('Seeded 160 orders with timelines, coupons and reviews.');
    }

    private function pickStatus(CarbonInterface $placedAt): OrderStatus
    {
        $daysAgo = (int) $placedAt->diffInDays(now());
        $roll = mt_rand(1, 100);

        if ($daysAgo >= 14) {
            return match (true) {
                $roll <= 82 => OrderStatus::Delivered,
                $roll <= 92 => OrderStatus::Cancelled,
                default => OrderStatus::Confirmed,
            };
        }

        if ($daysAgo >= 4) {
            return match (true) {
                $roll <= 40 => OrderStatus::Delivered,
                $roll <= 70 => OrderStatus::Confirmed,
                $roll <= 90 => OrderStatus::Pending,
                default => OrderStatus::Cancelled,
            };
        }

        return match (true) {
            $roll <= 45 => OrderStatus::Pending,
            $roll <= 85 => OrderStatus::Confirmed,
            $roll <= 95 => OrderStatus::Delivered,
            default => OrderStatus::Cancelled,
        };
    }

    private function pickPayment(): PaymentMethod
    {
        return match (mt_rand(1, 100)) {
            1...20 => PaymentMethod::Bkash,
            21...35 => PaymentMethod::Nagad,
            default => PaymentMethod::Cod,
        };
    }

    private function seedTimeline(Order $order, OrderStatus $status, CarbonInterface $placedAt, User $customer, ?User $admin): void
    {
        $this->addHistory($order, OrderStatus::Pending, $customer, 'Order placed and stock reserved.', $placedAt);

        if ($status === OrderStatus::Pending) {
            return;
        }

        if ($status === OrderStatus::Cancelled) {
            $this->addHistory($order, OrderStatus::Cancelled, $customer, 'Order cancelled before dispatch.', $placedAt->copy()->addHours(2));

            return;
        }

        $this->addHistory($order, OrderStatus::Confirmed, $admin, 'Order confirmed and queued for packing.', $placedAt->copy()->addHours(3));

        if ($status === OrderStatus::Delivered) {
            $this->addHistory($order, OrderStatus::Delivered, $admin, 'Order marked as delivered.', $placedAt->copy()->addDay());
        }
    }

    private function addHistory(Order $order, OrderStatus $status, ?User $actor, string $note, CarbonInterface $at): void
    {
        $at = $at->greaterThan(now()) ? now() : $at;

        $history = $order->histories()->make([
            'status' => $status,
            'actor_id' => $actor?->id,
            'note' => $note,
        ]);

        $history->created_at = $at;
        $history->updated_at = $at;
        $history->save();
    }

    /**
     * @param  array<int, int>  $ratings
     * @param  array<int, string>  $bodies
     */
    private function seedReviews(Order $order, User $customer, CarbonInterface $placedAt, array $ratings, array $bodies): void
    {
        foreach ($order->items as $item) {
            if (mt_rand(1, 100) > 55) {
                continue;
            }

            $review = Review::firstOrCreate(
                ['product_id' => $item->product_id, 'user_id' => $customer->id],
                [
                    'rating' => $ratings[mt_rand(0, count($ratings) - 1)],
                    'body' => $bodies[mt_rand(0, count($bodies) - 1)],
                ]
            );

            if ($review->wasRecentlyCreated) {
                $at = $placedAt->copy()->addDays(mt_rand(1, 3));

                DB::table('reviews')->where('id', $review->id)->update([
                    'created_at' => $at->greaterThan(now()) ? now() : $at,
                    'updated_at' => $at->greaterThan(now()) ? now() : $at,
                ]);
            }
        }
    }
}
