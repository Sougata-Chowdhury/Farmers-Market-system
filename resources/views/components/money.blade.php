@props(['value'])

<span>{{ config('marketplace.currency.symbol') }}{{ number_format((float) $value, 2) }}</span>
