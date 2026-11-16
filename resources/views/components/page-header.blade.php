@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
