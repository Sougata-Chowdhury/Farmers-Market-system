<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Farmer = 'farmer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Farmer => 'Farmer',
            self::Admin => 'Administrator',
        };
    }
}
