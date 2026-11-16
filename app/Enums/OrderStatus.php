<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * CSS classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Confirmed => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::Delivered => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::Cancelled => 'bg-rose-100 text-rose-800 ring-rose-200',
        };
    }

    /**
     * Friendly label for the order timeline.
     */
    public function timelineLabel(): string
    {
        return match ($this) {
            self::Pending => 'Order placed',
            self::Confirmed => 'Order confirmed',
            self::Delivered => 'Order delivered',
            self::Cancelled => 'Order cancelled',
        };
    }

    /**
     * Dot colour used by the order timeline.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-500 ring-amber-100',
            self::Confirmed => 'bg-sky-500 ring-sky-100',
            self::Delivered => 'bg-emerald-500 ring-emerald-100',
            self::Cancelled => 'bg-rose-500 ring-rose-100',
        };
    }

    /**
     * Statuses an admin can move an order to.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Delivered, self::Cancelled],
            self::Delivered, self::Cancelled => [],
        };
    }
}
