<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'payment_method',
        'address_id',
        'coupon_id',
        'coupon_code',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'notes',
        'subtotal',
        'discount',
        'platform_fee',
        'total',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest('created_at')->oldest('id');
    }

    /**
     * Record a status change for the order timeline.
     */
    public function recordHistory(OrderStatus $status, ?User $actor = null, ?string $note = null): OrderStatusHistory
    {
        return $this->histories()->create([
            'status' => $status,
            'actor_id' => $actor?->id,
            'note' => $note,
        ]);
    }

    public function scopeForCustomer(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForFarmer(Builder $query, User $farmer): Builder
    {
        return $query->whereHas('items', fn (Builder $query) => $query->where('farmer_id', $farmer->id));
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function isCancellableBy(User $user): bool
    {
        return $this->user_id === $user->id
            && in_array($this->status, [OrderStatus::Pending, OrderStatus::Confirmed], true);
    }
}
