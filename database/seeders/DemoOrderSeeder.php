<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class DemoOrderSeeder extends Seeder
{
    /**
     * Seed a handful of orders so the customer, farmer and admin dashboards
     * have realistic numbers straight after a fresh install.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@farmersmarket.test')->first();

        if (! $customer || Order::where('user_id', $customer->id)->exists()) {
            return;
        }

        $orders = [
            [
                'status' => OrderStatus::Delivered,
                'placed_at' => now()->subDays(3)->setTime(10, 15),
                'lines' => [
                    ['Lal Shak (Red Spinach)', 2],
                    ['Deshi Roshun (Garlic Local) ±25 gm', 1],
                    ['Banana Sobri', 1],
                ],
            ],
            [
                'status' => OrderStatus::Confirmed,
                'placed_at' => now()->subDay()->setTime(16, 40),
                'lines' => [
                    ['Kagozi Lebu (Kagozi Lemon)', 4],
                    ['Malta ± 50 gm', 2],
                ],
            ],
            [
                'status' => OrderStatus::Pending,
                'placed_at' => now()->subHours(5),
                'lines' => [
                    ['Dhundhul (Sponge Gourd) ±20 gm', 2],
                ],
            ],
        ];

        $products = Product::whereIn('name', collect($orders)->flatMap(fn (array $order) => collect($order['lines'])->pluck(0))->all())
            ->get()
            ->keyBy('name');

        foreach ($orders as $definition) {
            $lines = [];

            foreach ($definition['lines'] as [$name, $quantity]) {
                $product = $products->get($name);

                if (! $product || $product->stock < $quantity) {
                    continue;
                }

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => (float) $product->price,
                    'line_total' => round((float) $product->price * $quantity, 2),
                ];
            }

            if ($lines === []) {
                continue;
            }

            $subtotal = round(array_sum(array_column($lines, 'line_total')), 2);
            $fee = round($subtotal * (float) config('marketplace.platform_fee'), 2);

            $order = $customer->orders()->create([
                'status' => $definition['status'],
                'delivery_name' => $customer->name,
                'delivery_phone' => $customer->phone,
                'delivery_address' => $customer->address,
                'notes' => null,
                'subtotal' => $subtotal,
                'platform_fee' => $fee,
                'total' => round($subtotal + $fee, 2),
                'placed_at' => $definition['placed_at'],
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

                $line['product']->decrement('stock', $line['quantity']);
            }

            $this->seedTimeline($order, $definition['status'], $definition['placed_at'], $customer);
        }
    }

    /**
     * Give demo orders a realistic status history for the timeline UI.
     */
    private function seedTimeline(Order $order, OrderStatus $status, CarbonInterface $placedAt, User $customer): void
    {
        $admin = User::where('email', 'admin@farmersmarket.test')->first();

        $events = [
            [OrderStatus::Pending, $customer, 'Order placed and stock reserved.', $placedAt],
        ];

        if ($status !== OrderStatus::Pending) {
            $events[] = [OrderStatus::Confirmed, $admin, 'Order confirmed and queued for packing.', $placedAt->copy()->addHours(3)];
        }

        if ($status === OrderStatus::Delivered) {
            $events[] = [OrderStatus::Delivered, $admin, 'Order marked as delivered.', $placedAt->copy()->addDay()];
        }

        foreach ($events as [$eventStatus, $actor, $note, $at]) {
            $history = $order->histories()->make([
                'status' => $eventStatus,
                'actor_id' => $actor?->id,
                'note' => $note,
            ]);

            $history->created_at = $at;
            $history->updated_at = $at;
            $history->save();
        }
    }
}
