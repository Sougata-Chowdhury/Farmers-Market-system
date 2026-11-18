<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#F3EADB">

        <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bitter:400,500,600,700|karla:400,500,600,700|caveat:500,600|courier-prime:400,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-paper font-sans text-ink antialiased">
        <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
            <aside class="relative hidden flex-col justify-between overflow-hidden border-r-2 border-ink bg-forest-900 p-12 text-white lg:flex">
                <div class="absolute inset-0 bg-grain opacity-30" aria-hidden="true"></div>
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full border-[24px] border-ochre-300/10" aria-hidden="true"></div>
                <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full border-[1px] border-white/10" aria-hidden="true"></div>

                <div class="relative">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-md border border-white/40 bg-white/10">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5 3.2-8.2 8-8.5-.4 4.8-3.5 8-8 8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5-3.2-8.2-8-8.5.4 4.8 3.5 8 8 8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.5V9" />
                            </svg>
                        </span>
                        <span class="flex flex-col leading-none">
                            <span class="font-display text-xl font-semibold tracking-tight">Farmers Market</span>
                            <span class="mt-1 font-mono text-[8px] uppercase tracking-[0.28em] text-forest-200">Field to table</span>
                        </span>
                    </a>
                </div>

                <div class="relative max-w-md">
                    <p class="eyebrow !text-ochre-300">A note from the market</p>
                    <h1 class="mt-4 font-display text-[2.6rem] font-medium leading-[1.1] text-white">
                        Good food begins with the person who grew it.
                    </h1>
                    <p class="mt-5 text-sm leading-relaxed text-forest-100/90">
                        Shop seasonal vegetables, fruit, fish and dairy straight from verified local farms.
                        Farmers keep their own storefront, stock and earnings — no middlemen.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-forest-50">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-white/30 font-mono text-[10px] font-bold">1</span>
                            Every item is priced by the farmer, plainly.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-white/30 font-mono text-[10px] font-bold">2</span>
                            One flat 5% platform fee. Nothing hidden.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-white/30 font-mono text-[10px] font-bold">3</span>
                            Live stock — no overselling, no surprises.
                        </li>
                    </ul>
                </div>

                <div class="relative flex items-end justify-between gap-6">
                    <div class="max-w-[15rem] -rotate-1 border border-white/25 bg-paper-light p-4 text-ink shadow-lg">
                        <p class="font-mono text-[9px] font-bold uppercase tracking-label text-clay-600">Demo logins</p>
                        <ul class="mt-2 space-y-1 font-mono text-[10px] leading-relaxed text-ink-soft">
                            <li>customer@farmersmarket.test</li>
                            <li>farmer@farmersmarket.test</li>
                            <li>admin@farmersmarket.test</li>
                        </ul>
                        <p class="mt-2 border-t border-dashed border-line-dark pt-1.5 text-[10px] text-ink-soft">
                            Password: <span class="font-bold text-ink">password</span>
                        </p>
                    </div>
                    <p class="hand text-2xl text-ochre-200">See you Saturday.</p>
                </div>
            </aside>

            <div class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2 lg:hidden">
                        <span class="grid h-10 w-10 place-items-center rounded-md border border-forest-900 bg-forest-700 text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5 3.2-8.2 8-8.5-.4 4.8-3.5 8-8 8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5-3.2-8.2-8-8.5.4 4.8 3.5 8 8 8.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.5V9" />
                            </svg>
                        </span>
                        <span class="font-display text-lg font-semibold tracking-tight">Farmers Market</span>
                    </a>

                    <div class="relative">
                        <span class="tape -top-3 left-1/2 -translate-x-1/2 -rotate-2" aria-hidden="true"></span>
                        <div class="card p-6 pt-8 sm:p-8 sm:pt-9">
                            {{ $slot }}
                        </div>
                    </div>

                    <p class="mt-6 text-center font-mono text-[10px] uppercase tracking-label text-ink-faint">
                        &copy; {{ now()->year }} Farmers Market &middot; Grown local, sold honest
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
