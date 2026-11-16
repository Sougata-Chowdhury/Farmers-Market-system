@component('mail::message')
# New sale, {{ $farmer->name }}!

Order **#{{ $order->id }}** from {{ $order->user->name }} includes your produce.

@component('mail::table')
| Product | Qty | Your total |
|:--------|:---:|-----------:|
@foreach ($items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format((float) $item->line_total, 2) }} BDT |
@endforeach
@endcomponent

Your payout for this order: **{{ number_format((float) $items->sum('line_total'), 2) }} BDT**

@component('mail::button', ['url' => route('farmer.orders.index'), 'color' => 'success'])
Open sales dashboard
@endcomponent

Ship to **{{ $order->delivery_name }}** · {{ $order->delivery_phone }}
{{ $order->delivery_address }}

@if ($order->notes)
**Customer note:** {{ $order->notes }}
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
