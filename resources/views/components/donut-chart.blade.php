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
    $palette = ['#059669', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="relative mx-auto h-36 w-36 shrink-0">
            <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-label="{{ $title }}">
                <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#f1f5f9" stroke-width="5" />
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
                    <p class="text-xl font-bold text-slate-900">{{ number_format((float) $segments->sum('value')) }}</p>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">{{ $unit ?: 'total' }}</p>
                </div>
            </div>
        </div>

        <ul class="flex-1 space-y-2">
            @forelse ($segments as $index => $segment)
                @php($color = $segment['color'] ?? $palette[$index % count($palette)])
                <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $color }}"></span>
                        <span class="truncate text-slate-600">{{ $segment['label'] }}</span>
                    </span>
                    <span class="font-semibold text-slate-900">{{ number_format((float) $segment['value']) }}</span>
                </li>
            @empty
                <li class="text-sm text-slate-500">Nothing to show yet.</li>
            @endforelse
        </ul>
    </div>
</div>
