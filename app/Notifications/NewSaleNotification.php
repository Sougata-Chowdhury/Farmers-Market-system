<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NewSaleNotification extends Notification
{
    /**
     * @param  Collection<int, OrderItem>  $items
     */
    public function __construct(
        public Order $order,
        public User $farmer,
        public Collection $items,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $units = (int) $this->items->sum('quantity');
        $value = (float) $this->items->sum('line_total');

        return [
            'title' => "New sale on order #{$this->order->id}",
            'body' => "{$units} ".($units === 1 ? 'unit' : 'units').' · '.number_format($value, 2).' BDT',
            'url' => route('farmer.orders.index'),
            'kind' => 'sale',
        ];
    }
}
