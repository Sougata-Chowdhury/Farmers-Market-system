@props(['rating' => 0, 'count' => null, 'size' => 'sm'])

@php
    $rounded = round((float) $rating);
    $class = $size === 'lg' ? 'text-sm' : 'text-[10px]';
@endphp

<span class="inline-flex items-center gap-1 {{ $class }}" title="{{ number_format((float) $rating, 1) }} out of 5">
    <span class="tracking-[0.12em] text-ochre-500" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            {{ $i <= $rounded ? '★' : '☆' }}
        @endfor
    </span>
    <span class="font-mono font-bold text-ink-soft">{{ number_format((float) $rating, 1) }}</span>
    @if ($count !== null)
        <span class="font-mono text-ink-faint">({{ $count }})</span>
    @endif
</span>
