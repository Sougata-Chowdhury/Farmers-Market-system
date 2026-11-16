<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace settings
    |--------------------------------------------------------------------------
    |
    | The platform fee is charged to the customer on top of the product
    | subtotal. Farmers receive the full line total of their products.
    |
    */

    'platform_fee' => (float) env('MARKETPLACE_FEE', 0.05),

    'currency' => [
        'code' => 'BDT',
        'symbol' => '৳',
    ],

    'products_per_page' => 12,
];
