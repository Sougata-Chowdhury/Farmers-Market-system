<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case Bkash = 'bkash';
    case Nagad = 'nagad';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Cash on delivery',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Cod => 'Pay the delivery rider in cash when your order arrives.',
            self::Bkash => 'Send the amount to the platform bKash number after placing the order.',
            self::Nagad => 'Send the amount to the platform Nagad number after placing the order.',
        };
    }

    public function isMobileWallet(): bool
    {
        return $this !== self::Cod;
    }
}
