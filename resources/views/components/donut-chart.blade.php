@props([
    'segments' => [],
    'title' => 'Breakdown',
    'unit' => '',
])

@php
    $segments = collect($segments)->filter(fn (array $segment) => (float) $segment['value'] > 0)->values();
    $total = max(1, (float) $segments->sum('value'));
    $circumference = 100;
    $offset = 0;
    $palette = ['#4A653D', '#B88126', '#A9542F', '#5B7E79', '#95846C', '#2A3A25'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="relative mx-auto h-36 w-36 shrink-0">
            <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-label="{{ $title }}">
                <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#E8DCC8" stroke-width="5" />
                @foreach ($segments as $index => $segment)
                    @php
                        $percentage = ((float) $segment['value'] / $total) * $circumference;
                        $color = $segment['color'] ?? $palette[$index % count($palette)];
                    @endphp
                    <circle cx="21" cy="21" r="15.9155" fill="none"
                            stroke="{{ $color }}" stroke-width="5"
                            stroke-dasharray="{{ round($percentage, 2) }} {{ round($circumference - $percentage, 2) }}"
                            stroke-dashoffset="{{ round(-$offset, 2) }}"
                            stroke-linecap="butt" />
                    @php($offset += $percentage)
                @endforeach
            </svg>
            <div class="pointer-events-none absolute inset-0 grid place-items-center text-center">
                <div>
                    <p class="font-display text-xl font-semibold text-ink">{{ number_format((float) $segments->sum('value')) }}</p>
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ $unit ?: 'total' }}</p>
                </div>
            </div>
        </div>

        <ul class="flex-1 space-y-2">
            @forelse ($segments as $index => $segment)
                @php($color = $segment['color'] ?? $palette[$index % count($palette)])
                <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-sm" style="background-color: {{ $color }}"></span>
                        <span class="truncate text-ink-soft">{{ $segment['label'] }}</span>
                    </span>
                    <span class="font-display font-semibold text-ink">{{ number_format((float) $segment['value']) }}</span>
                </li>
            @empty
                <li class="text-sm text-ink-soft">Nothing to show yet.</li>
            @endforelse
        </ul>
    </div>
</div>
