<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockDigest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Product>  $products
     */
    public function __construct(public User $farmer, public Collection $products) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->products->count().' products need restocking — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.farmers.low-stock',
            with: [
                'farmer' => $this->farmer,
                'products' => $this->products,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
