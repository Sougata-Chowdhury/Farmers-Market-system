<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'FRESH10',
                'description' => '10% off your first basket',
                'type' => 'percent',
                'value' => 10,
                'min_spend' => 500,
                'max_uses' => 200,
                'expires_at' => now()->addMonths(6),
            ],
            [
                'code' => 'FLAT50',
                'description' => '50 BDT off orders over 300 BDT',
                'type' => 'fixed',
                'value' => 50,
                'min_spend' => 300,
                'max_uses' => 500,
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'BULK15',
                'description' => '15% off bulk vegetable orders',
                'type' => 'percent',
                'value' => 15,
                'min_spend' => 1500,
                'max_uses' => 100,
                'expires_at' => now()->addMonth(),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon + ['is_active' => true]
            );
        }
    }
}
