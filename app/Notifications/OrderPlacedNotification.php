<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    public function __construct(public Order $order) {}

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
        $itemCount = (int) $this->order->items->sum('quantity');

        return [
            'title' => "Order #{$this->order->id} placed",
            'body' => "{$itemCount} ".($itemCount === 1 ? 'item' : 'items').' · '.number_format((float) $this->order->total, 2).' BDT',
            'url' => route('orders.show', $this->order),
            'kind' => 'order',
        ];
    }
}
