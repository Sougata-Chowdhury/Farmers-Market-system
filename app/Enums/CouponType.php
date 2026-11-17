<?php

namespace App\Enums;

enum CouponType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Percentage off',
            self::Fixed => 'Fixed amount off',
        };
    }

    /**
     * Human readable value, e.g. "10%" or "50.00 BDT".
     */
    public function describe(float $value): string
    {
        return match ($this) {
            self::Percent => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').'% off',
            self::Fixed => number_format($value, 2).' BDT off',
        };
    }
}
