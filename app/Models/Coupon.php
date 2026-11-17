<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_spend',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'min_spend' => 'decimal:2',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon): void {
            $coupon->code = static::normalizeCode($coupon->code);
        });
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()))
            ->where(fn (Builder $query) => $query
                ->whereNull('max_uses')
                ->orWhereColumn('used_count', '<', 'max_uses'));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->isExhausted();
    }

    public function meetsMinimum(float $subtotal): bool
    {
        return $subtotal >= (float) $this->min_spend;
    }

    /**
     * The discount this coupon gives on a given subtotal.
     */
    public function discountFor(float $subtotal): float
    {
        if (! $this->meetsMinimum($subtotal)) {
            return 0.0;
        }

        $discount = match ($this->type) {
            CouponType::Percent => $subtotal * ((float) $this->value / 100),
            CouponType::Fixed => (float) $this->value,
        };

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Status label used by the admin list.
     */
    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Disabled';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isExhausted()) {
            return 'Used up';
        }

        return 'Active';
    }

    public function statusClasses(): string
    {
        return match ($this->statusLabel()) {
            'Active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'Expired' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'Used up' => 'bg-amber-100 text-amber-800 ring-amber-200',
            default => 'bg-rose-100 text-rose-700 ring-rose-200',
        };
    }
}
