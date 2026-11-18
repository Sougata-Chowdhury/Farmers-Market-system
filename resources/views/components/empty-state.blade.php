@props(['title', 'description' => null])

<div class="relative flex flex-col items-center justify-center gap-3 border border-dashed border-line-dark bg-paper-light px-6 py-14 text-center">
    <span class="absolute left-1/2 top-0 h-5 w-20 -translate-x-1/2 -translate-y-1/2 -rotate-2 bg-paper-deep/80" aria-hidden="true"></span>
    <span class="grid h-12 w-12 place-items-center rounded-full border border-line-dark text-clay-600">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.4" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5 3.2-8.2 8-8.5-.4 4.8-3.5 8-8 8.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5-3.2-8.2-8-8.5.4 4.8 3.5 8 8 8.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.5V9" />
        </svg>
    </span>
    <h3 class="font-display text-xl font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="max-w-md text-sm leading-relaxed text-ink-soft">{{ $description }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="pt-2">{{ $slot }}</div>
    @endif
</div>
