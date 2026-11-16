<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden flex-col justify-between overflow-hidden bg-emerald-800 p-12 text-white lg:flex">
                <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-emerald-500/30 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-24 h-96 w-96 rounded-full bg-lime-400/20 blur-3xl"></div>

                <div class="relative">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/30">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2C8.5 4.5 5 8 5 12.5A7 7 0 0 0 12 19.5a7 7 0 0 0 7-7C19 8 15.5 4.5 12 2Z" opacity=".3" />
                                <path d="M12 21.5c-.4-4.2.6-7.4 3.2-10.1a.75.75 0 1 1 1.1 1c-2.3 2.5-3 5.2-2.7 9a.75.75 0 0 1-1.5.1Z" />
                                <path d="M12 21.5c.4-4.2-.6-7.4-3.2-10.1a.75.75 0 1 0-1.1 1c2.3 2.5 3 5.2 2.7 9a.75.75 0 0 0 1.5.1Z" />
                            </svg>
                        </span>
                        <span class="text-xl font-semibold tracking-tight">Farmers Market</span>
                    </a>
                </div>

                <div class="relative max-w-md">
                    <h1 class="font-display text-4xl font-medium leading-tight">
                        Fresh from the farm, straight to your kitchen.
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-emerald-100/90">
                        Shop seasonal vegetables, fruits and fish from verified local farmers. Farmers keep their own
                        storefront, inventory and earnings dashboard — no middlemen.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-emerald-50">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-white/20">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            Transparent per-item pricing with a flat 5% platform fee.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-white/20">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            Live stock tracking — no overselling, no silent failures.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-white/20">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            Farmers see every order and their real earnings.
                        </li>
                    </ul>
                </div>

                <p class="relative text-xs text-emerald-200/80">
                    Demo logins are in the README — admin, farmer and customer accounts included.
                </p>
            </aside>

            <div class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-2 lg:hidden">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-600 text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2C8.5 4.5 5 8 5 12.5A7 7 0 0 0 12 19.5a7 7 0 0 0 7-7C19 8 15.5 4.5 12 2Z" opacity=".3" />
                                <path d="M12 21.5c-.4-4.2.6-7.4 3.2-10.1a.75.75 0 1 1 1.1 1c-2.3 2.5-3 5.2-2.7 9a.75.75 0 0 1-1.5.1Z" />
                                <path d="M12 21.5c.4-4.2-.6-7.4-3.2-10.1a.75.75 0 1 0-1.1 1c2.3 2.5 3 5.2 2.7 9a.75.75 0 0 0 1.5.1Z" />
                            </svg>
                        </span>
                        <span class="text-lg font-semibold tracking-tight">Farmers<span class="text-emerald-600">Market</span></span>
                    </a>

                    <div class="card p-6 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-slate-500">
                        &copy; {{ now()->year }} Farmers Market. Built with Laravel 12.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
