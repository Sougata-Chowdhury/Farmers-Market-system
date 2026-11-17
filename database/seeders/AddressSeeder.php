<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Give the demo customer a couple of saved delivery addresses.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@farmersmarket.test')->first();

        if (! $customer) {
            $this->command?->warn('Demo customer missing. Run DemoUsersSeeder first.');

            return;
        }

        $addresses = [
            [
                'label' => 'Home',
                'recipient_name' => 'Nusrat Jahan',
                'phone' => '01712345678',
                'address_line' => 'House 12, Road 5, Dhanmondi',
                'area' => 'Dhaka 1205',
                'is_default' => true,
            ],
            [
                'label' => 'Office',
                'recipient_name' => 'Nusrat Jahan',
                'phone' => '01712345678',
                'address_line' => 'Level 8, Concord Tower, Gulshan 1',
                'area' => 'Dhaka 1212',
                'is_default' => false,
            ],
        ];

        foreach ($addresses as $attributes) {
            Address::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'label' => $attributes['label'],
                ],
                $attributes
            );
        }
    }
}
