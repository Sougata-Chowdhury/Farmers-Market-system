<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Give every demo customer at least one saved delivery address. The main
     * customer also gets an office address so checkout can show a choice.
     */
    public function run(): void
    {
        /** @var array<int, array<string, mixed>> $customers */
        $customers = require database_path('seeders/data/customers.php');

        foreach ($customers as $index => $customer) {
            $user = User::where('email', $customer['email'])->first();

            if (! $user) {
                continue;
            }

            Address::updateOrCreate(
                ['user_id' => $user->id, 'label' => 'Home'],
                [
                    'recipient_name' => $user->name,
                    'phone' => $customer['phone'],
                    'address_line' => $customer['address_line'],
                    'area' => $customer['area'],
                    'is_default' => true,
                ]
            );

            if ($index === 0) {
                Address::updateOrCreate(
                    ['user_id' => $user->id, 'label' => 'Office'],
                    [
                        'recipient_name' => $user->name,
                        'phone' => $customer['phone'],
                        'address_line' => 'Level 8, Concord Tower, Gulshan 1',
                        'area' => 'Dhaka 1212',
                        'is_default' => false,
                    ]
                );
            }
        }

        $this->command?->info('Seeded saved delivery addresses for every customer.');
    }
}
