<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    public function __construct(public Order $order, public OrderStatus $status) {}

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
        return [
            'title' => "Order #{$this->order->id} is now {$this->status->label()}",
            'body' => match ($this->status) {
                OrderStatus::Confirmed => 'The farmer confirmed your order and will pack it shortly.',
                OrderStatus::Delivered => 'Your order has been delivered. Enjoy!',
                OrderStatus::Cancelled => 'Your order was cancelled and any reserved stock was released.',
                default => 'Your order status changed.',
            },
            'url' => route('orders.show', $this->order),
            'kind' => 'status',
        ];
    }
}
