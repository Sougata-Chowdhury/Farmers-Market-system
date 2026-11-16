<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'actor_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Human-friendly actor name for the timeline.
     */
    public function actorLabel(?Order $order = null): string
    {
        $order ??= $this->order;

        if ($this->actor === null) {
            return 'System';
        }

        if ($this->actor->isAdmin()) {
            return 'Platform team';
        }

        if ($this->actor->id === $order?->user_id) {
            return 'Customer';
        }

        return $this->actor->name;
    }
}
