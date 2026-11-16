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

        $farmers = [
            [
                'name' => 'Rafiq Hasan',
                'email' => 'farmer@farmersmarket.test',
                'phone' => '01700000002',
                'address' => 'Savar, Dhaka',
                'bank_name' => 'BRAC Bank',
                'branch' => 'Savar Branch',
                'account_no' => '1234567890',
            ],
            [
                'name' => 'Ayesha Begum',
                'email' => 'ayesha@farmersmarket.test',
                'phone' => '01700000003',
                'address' => 'Narsingdi',
                'bank_name' => 'Sonali Bank',
                'branch' => 'Narsingdi Branch',
                'account_no' => '9876543210',
            ],
        ];

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
                'bank_name' => $farmer['bank_name'],
                'branch' => $farmer['branch'],
                'account_no' => $farmer['account_no'],
            ]);
        }

        User::updateOrCreate(
            ['email' => 'customer@farmersmarket.test'],
            [
                'name' => 'Nusrat Jahan',
                'phone' => '01700000004',
                'role' => UserRole::Customer,
                'address' => 'Dhanmondi, Dhaka',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
    }
}
