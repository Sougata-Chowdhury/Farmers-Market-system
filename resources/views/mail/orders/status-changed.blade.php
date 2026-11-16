@component('mail::message')
# Order #{{ $order->id }} is {{ $status->label() }}

Hi {{ $order->user->name }},

@if ($status === \App\Enums\OrderStatus::Confirmed)
Your order has been confirmed and is being packed for delivery.
@elseif ($status === \App\Enums\OrderStatus::Delivered)
Your order has been delivered. We hope you enjoy the produce!
@elseif ($status === \App\Enums\OrderStatus::Cancelled)
Your order was cancelled and any reserved stock has been returned to the farmers.
@endif

@component('mail::button', ['url' => route('orders.show', $order), 'color' => 'success'])
Track this order
@endcomponent

**Order total:** {{ number_format((float) $order->total, 2) }} BDT

Thanks,<br>
{{ config('app.name') }}
@endcomponent
