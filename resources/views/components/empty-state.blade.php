@props(['title', 'description' => null])

<div class="card flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
        </svg>
    </span>
    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="pt-2">{{ $slot }}</div>
    @endif
</div>
