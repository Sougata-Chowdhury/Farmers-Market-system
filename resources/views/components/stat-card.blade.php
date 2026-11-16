@props(['label', 'value', 'hint' => null, 'icon' => null])

<div class="card p-5">
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        @if ($icon)
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                {!! $icon !!}
            </span>
        @endif
    </div>
    <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
