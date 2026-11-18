@props(['label', 'value', 'hint' => null, 'icon' => null])

<div class="relative border border-line bg-white p-5 shadow-sm">
    <span class="absolute left-0 top-0 h-1 w-12 bg-forest-700" aria-hidden="true"></span>
    <div class="flex items-start justify-between gap-3">
        <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">{{ $label }}</p>
        @if ($icon)
            <span class="grid h-8 w-8 shrink-0 place-items-center border border-line bg-paper-light text-forest-700">
                {!! $icon !!}
            </span>
        @endif
    </div>
    <p class="mt-3 font-display text-[1.7rem] font-semibold leading-none tracking-tight text-ink">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $hint }}</p>
    @endif
</div>
