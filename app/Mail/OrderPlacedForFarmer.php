<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderPlacedForFarmer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, OrderItem>  $items
     */
    public function __construct(
        public Order $order,
        public User $farmer,
        public Collection $items,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New sale on order #{$this->order->id} — ".config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.received-by-farmer',
            with: [
                'order' => $this->order,
                'farmer' => $this->farmer,
                'items' => $this->items,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
