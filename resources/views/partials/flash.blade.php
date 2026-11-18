@if (session('status'))
    <div class="mb-5 flex items-start gap-3 border border-forest-700/30 border-l-4 border-l-forest-700 bg-forest-50 px-4 py-3 text-sm text-forest-900"
         role="status" aria-live="polite">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-forest-700 font-mono text-[10px] font-bold text-white" aria-hidden="true">✓</span>
        <p class="leading-relaxed">{{ session('status') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="mb-5 flex items-start gap-3 border border-clay-600/40 border-l-4 border-l-clay-600 bg-clay-50 px-4 py-3 text-sm text-clay-800"
         role="alert">
        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-clay-600 font-mono text-[10px] font-bold text-white" aria-hidden="true">!</span>
        <p class="leading-relaxed">{{ session('error') }}</p>
    </div>
@endif

@if ($errors->any())
    <div id="form-errors" tabindex="-1" role="alert"
         class="mb-5 border border-ochre-500/40 border-l-4 border-l-ochre-500 bg-ochre-50 px-4 py-3 text-sm text-ochre-900">
        <p class="font-mono text-[10px] font-bold uppercase tracking-label">Please fix the following</p>
        <ul class="mt-2 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
