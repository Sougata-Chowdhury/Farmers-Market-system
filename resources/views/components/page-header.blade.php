@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4 border-b border-line-dark pb-5']) }}>
    <div>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-ink sm:text-[2.4rem] sm:leading-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-soft">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
