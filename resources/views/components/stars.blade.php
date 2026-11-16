@props(['rating' => 0, 'count' => null, 'size' => 'sm'])

@php
    $rounded = round((float) $rating);
    $class = $size === 'lg' ? 'text-base' : 'text-xs';
@endphp

<span class="inline-flex items-center gap-1 {{ $class }}" title="{{ number_format((float) $rating, 1) }} out of 5">
    <span class="text-amber-500" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            {{ $i <= $rounded ? '★' : '☆' }}
        @endfor
    </span>
    <span class="font-medium text-slate-600">{{ number_format((float) $rating, 1) }}</span>
    @if ($count !== null)
        <span class="text-slate-400">({{ $count }})</span>
    @endif
</span>
