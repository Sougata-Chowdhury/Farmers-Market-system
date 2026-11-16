@component('mail::message')
# Thanks, {{ $order->user->name }}!

We received **order #{{ $order->id }}** and reserved your produce. The farmer will confirm it shortly.

@component('mail::table')
| Product | Qty | Line total |
|:--------|:---:|-----------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format((float) $item->line_total, 2) }} BDT |
@endforeach
@endcomponent

Subtotal: **{{ number_format((float) $order->subtotal, 2) }} BDT**
Platform fee: **{{ number_format((float) $order->platform_fee, 2) }} BDT**
Total: **{{ number_format((float) $order->total, 2) }} BDT**

@component('mail::button', ['url' => route('orders.show', $order), 'color' => 'success'])
View your order
@endcomponent

Delivery to **{{ $order->delivery_name }}** · {{ $order->delivery_phone }}
{{ $order->delivery_address }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
