<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public OrderStatus $status) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order #{$this->order->id} is {$this->status->label()} — ".config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.status-changed',
            with: [
                'order' => $this->order,
                'status' => $this->status,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
