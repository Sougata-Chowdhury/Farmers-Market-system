@props([
    'points' => [],
    'height' => 180,
    'color' => '#4A653D',
    'fill' => 'rgba(74, 101, 61, 0.14)',
    'currency' => true,
    'label' => 'Revenue',
])

@php
    $width = 600;
    $paddingX = 6;
    $paddingY = 14;
    $values = collect($points)->pluck('value')->map(fn ($value) => (float) $value);
    $max = max(1, (float) $values->max());
    $count = max(1, count($points));
    $step = $count > 1 ? ($width - $paddingX * 2) / ($count - 1) : 0;
    $chartHeight = $height - $paddingY * 2;

    $coordinates = collect($points)->values()->map(function (array $point, int $index) use ($paddingX, $step, $paddingY, $chartHeight, $max, $height): array {
        return [
            'x' => round($paddingX + $index * $step, 2),
            'y' => round($height - $paddingY - (((float) $point['value']) / $max) * $chartHeight, 2),
            'label' => $point['label'] ?? '',
            'value' => (float) $point['value'],
        ];
    });

    $line = $coordinates->map(fn (array $point, int $index): string => ($index === 0 ? 'M' : 'L').$point['x'].' '.$point['y'])->implode(' ');
    $bottom = $height - $paddingY;
    $area = $coordinates->isEmpty()
        ? ''
        : $line.' L'.$coordinates->last()['x'].' '.$bottom.' L'.$coordinates->first()['x'].' '.$bottom.' Z';

    $format = fn (float $value): string => $currency
        ? config('marketplace.currency.symbol').number_format($value, $value >= 1000 ? 0 : 2)
        : number_format($value);

    $tickLabels = $coordinates->isEmpty() ? collect() : collect([
        $coordinates->first(),
        $coordinates->get((int) floor(($count - 1) / 2)),
        $coordinates->last(),
    ])->filter()->unique('label')->values();
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex items-start justify-between gap-4 border-b border-dashed border-line-dark pb-3">
        <div>
            <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">{{ $label }}</p>
            <p class="mt-1 font-display text-2xl font-semibold text-ink">{{ $format((float) $values->sum()) }}</p>
        </div>
        <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">Last {{ $count }} days</p>
    </div>

    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="mt-3 w-full" role="img"
         aria-label="{{ $label }} over the last {{ $count }} days, totalling {{ $format((float) $values->sum()) }}">
        @foreach ([0.25, 0.5, 0.75] as $ratio)
            <line x1="{{ $paddingX }}" x2="{{ $width - $paddingX }}"
                  y1="{{ round($paddingY + $chartHeight * $ratio, 2) }}"
                  y2="{{ round($paddingY + $chartHeight * $ratio, 2) }}"
                  stroke="#D3C4AC" stroke-width="1" stroke-dasharray="2 5" />
        @endforeach

        @if ($area !== '')
            <path d="{{ $area }}" fill="{{ $fill }}" />
            <path d="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="2.5"
                  stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="{{ $coordinates->last()['x'] }}" cy="{{ $coordinates->last()['y'] }}"
                    r="3.5" fill="#FFFCF4" stroke="{{ $color }}" stroke-width="2.5" />
        @endif
    </svg>

    <div class="mt-2 flex items-center justify-between font-mono text-[9px] uppercase tracking-label text-ink-faint">
        @foreach ($tickLabels as $tick)
            <span>{{ $tick['label'] }}</span>
        @endforeach
    </div>
</div>
