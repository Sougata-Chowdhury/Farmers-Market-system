@component('mail::message')
# Restock reminder, {{ $farmer->name }}

These products are at or below {{ config('marketplace.low_stock_threshold') }} units and may sell out soon.

@component('mail::table')
| Product | Stock left |
|:--------|-----------:|
@foreach ($products as $product)
| {{ $product->name }} | {{ $product->stock }} {{ $product->unit }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('farmer.products.index'), 'color' => 'success'])
Update stock levels
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
