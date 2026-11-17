<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            DemoUsersSeeder::class,
            AddressSeeder::class,
            ProductSeeder::class,
            ProductGallerySeeder::class,
            ReviewSeeder::class,
            CouponSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
