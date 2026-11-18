<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@farmersmarket.test'],
            [
                'name' => 'Platform Admin',
                'phone' => '01700000001',
                'role' => UserRole::Admin,
                'address' => 'Farmers Market HQ, Dhaka',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        /** @var array<int, array<string, mixed>> $farmers */
        $farmers = require database_path('seeders/data/farmers.php');

        foreach ($farmers as $farmer) {
            $user = User::updateOrCreate(
                ['email' => $farmer['email']],
                [
                    'name' => $farmer['name'],
                    'phone' => $farmer['phone'],
                    'role' => UserRole::Farmer,
                    'address' => $farmer['address'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->farmerProfile()->updateOrCreate([], [
                'farm_name' => $farmer['farm_name'],
                'location' => $farmer['location'],
                'bio' => $farmer['bio'],
                'bank_name' => $farmer['bank_name'],
                'branch' => $farmer['branch'],
                'account_no' => $farmer['account_no'],
            ]);
        }

        /** @var array<int, array<string, mixed>> $customers */
        $customers = require database_path('seeders/data/customers.php');

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'role' => UserRole::Customer,
                    'address' => $customer['address'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info(sprintf(
            'Seeded %d farmers and %d customers (password: "password").',
            count($farmers),
            count($customers)
        ));
    }
}
