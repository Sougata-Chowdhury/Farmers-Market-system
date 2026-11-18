@props(['value'])

<span class="tabular-nums">{{ config('marketplace.currency.symbol') }}{{ number_format((float) $value, 2) }}</span>
