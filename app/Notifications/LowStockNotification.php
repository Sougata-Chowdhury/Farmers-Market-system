<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockNotification extends Notification
{
    /**
     * @param  Collection<int, Product>  $products
     */
    public function __construct(public Collection $products) {}

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
        $count = $this->products->count();

        return [
            'title' => "{$count} ".($count === 1 ? 'product needs' : 'products need').' restocking',
            'body' => $this->products->take(3)->pluck('name')->implode(', ')
                .($count > 3 ? ' and '.($count - 3).' more' : ''),
            'url' => route('farmer.products.index'),
            'kind' => 'stock',
        ];
    }
}
