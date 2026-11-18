<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#F3EADB">

        @php
            $metaTitle = $title ? $title.' · '.config('app.name') : config('app.name');
            $metaDescription = $description ?? 'Farm-fresh vegetables, fruit, fish and dairy delivered from local Bangladeshi farmers, with transparent pricing and a flat platform fee.';
            $metaImage = $image ?? asset('images/og-cover.jpg');
            $metaUrl = url()->current();
            $privateRoutes = ['admin.', 'farmer.', 'cart.', 'checkout.', 'orders.', 'profile.', 'addresses.', 'notifications.', 'wishlist.', 'dashboard', 'login', 'register', 'password.', 'verification.'];
            $isPrivate = $noindex || collect($privateRoutes)->contains(fn (string $prefix): bool => request()->routeIs($prefix.'*') || request()->routeIs($prefix));
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="canonical" href="{{ $metaUrl }}">
        @if ($isPrivate)
            <meta name="robots" content="noindex, nofollow">
        @else
            <meta name="robots" content="index, follow">
        @endif

        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:locale" content="en_GB">
        <meta property="og:type" content="{{ $type }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:alt" content="{{ $title ?? config('app.name') }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">

        @stack('meta')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bitter:400,500,600,700|karla:400,500,600,700|caveat:500,600|courier-prime:400,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-paper font-sans text-ink antialiased" x-data>
        <a href="#main-content"
           class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-md focus:border focus:border-forest-900 focus:bg-forest-700 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
            Skip to content
        </a>

        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            <main id="main-content" tabindex="-1" class="flex-1">
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    @include('partials.flash')
                </div>

                {{ $slot }}
            </main>

            <footer class="mt-20 border-t-2 border-ink bg-paper-light">
                <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-8 border-b border-line pb-10 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-sm">
                            <a href="{{ route('home') }}" class="inline-flex items-baseline gap-2">
                                <span class="font-display text-2xl font-semibold tracking-tight">Farmers Market</span>
                                <span class="stamp stamp-green !rotate-0 !px-1.5 !py-0.5 !text-[8px]">Est. 2021</span>
                            </a>
                            <p class="mt-3 text-sm leading-relaxed text-ink-soft">
                                A small, direct marketplace for local farms. No warehouses, no middlemen —
                                just the harvest, the grower and your kitchen.
                            </p>
                            <p class="hand mt-4 text-xl text-clay-600">Picked this morning, with care.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-8 text-sm sm:grid-cols-3 lg:gap-14">
                            <div>
                                <p class="eyebrow">The market</p>
                                <ul class="mt-3 space-y-2 text-ink-soft">
                                    <li><a href="{{ route('home') }}" class="hover:text-forest-700">All produce</a></li>
                                    <li><a href="{{ route('home') }}#catalogue" class="hover:text-forest-700">Today's harvest</a></li>
                                    @auth
                                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-forest-700">Saved items</a></li>
                                    @endauth
                                </ul>
                            </div>
                            <div>
                                <p class="eyebrow">Your stall</p>
                                <ul class="mt-3 space-y-2 text-ink-soft">
                                    @auth
                                        <li><a href="{{ route('dashboard') }}" class="hover:text-forest-700">Dashboard</a></li>
                                        <li><a href="{{ route('orders.index') }}" class="hover:text-forest-700">Orders</a></li>
                                        <li><a href="{{ route('profile.edit') }}" class="hover:text-forest-700">Account settings</a></li>
                                    @else
                                        <li><a href="{{ route('login') }}" class="hover:text-forest-700">Log in</a></li>
                                        <li><a href="{{ route('register') }}" class="hover:text-forest-700">Create an account</a></li>
                                    @endauth
                                </ul>
                            </div>
                            <div>
                                <p class="eyebrow">On the farm</p>
                                <ul class="mt-3 space-y-2 text-ink-soft">
                                    <li><a href="{{ route('register') }}" class="hover:text-forest-700">Sell with us</a></li>
                                    <li><span class="text-ink-faint">Delivery: Tue &amp; Fri</span></li>
                                    <li><span class="text-ink-faint">Dhaka, Bangladesh</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 pt-6 font-mono text-[10px] uppercase tracking-label text-ink-faint sm:flex-row sm:items-center sm:justify-between">
                        <p>&copy; {{ now()->year }} Farmers Market &middot; Grown local, sold honest</p>
                        <p class="flex items-center gap-2">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-forest-600"></span>
                            Market open &middot; {{ now()->format('D, j M') }}
                        </p>
                    </div>
                </div>
            </footer>
        </div>

        @auth
            @if (auth()->user()->isCustomer())
                <div x-cloak x-show="$store.cart.open" class="fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Shopping basket"
                     x-effect="document.body.classList.toggle('overflow-y-hidden', $store.cart.open)"
                     x-on:keydown.escape.window="$store.cart.open = false"
                     x-init="$watch('$store.cart.open', open => { if (open) { $nextTick(() => $refs.cartClose?.focus()) } })">
                    <div x-show="$store.cart.open" x-transition.opacity
                         class="absolute inset-0 bg-ink/50"
                         x-on:click="$store.cart.open = false"></div>

                    <aside x-show="$store.cart.open"
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="translate-x-full"
                           x-transition:enter-end="translate-x-0"
                           x-transition:leave="transition ease-in duration-200"
                           x-transition:leave-start="translate-x-0"
                           x-transition:leave-end="translate-x-full"
                           class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-l-2 border-ink bg-paper-light shadow-2xl">
                        <header class="flex items-start justify-between gap-3 border-b border-dashed border-line-dark px-5 py-4">
                            <div>
                                <p class="eyebrow">In your basket</p>
                                <h2 class="mt-1 font-display text-2xl font-semibold">Fresh picks</h2>
                                <p class="font-mono text-[11px] text-ink-soft" x-text="$store.cart.count + ' ' + ($store.cart.count === 1 ? 'item' : 'items')"></p>
                            </div>
                            <button type="button" x-on:click="$store.cart.open = false" x-ref="cartClose"
                                    class="grid h-9 w-9 place-items-center border border-ink/30 text-ink-soft transition hover:border-ink hover:bg-paper-deep hover:text-ink"
                                    aria-label="Close basket">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto">
                            <template x-if="$store.cart.html === ''">
                                <div class="flex h-full items-center justify-center p-8">
                                    <p class="hand text-2xl text-ink-faint">Packing your basket&hellip;</p>
                                </div>
                            </template>
                            <div x-html="$store.cart.html"></div>
                        </div>
                    </aside>
                </div>

                <div class="pointer-events-none fixed inset-x-0 bottom-4 z-[70] flex justify-center px-4 sm:justify-end sm:px-6">
                    <div x-cloak x-show="$store.cart.toast" x-transition
                         class="pointer-events-auto flex max-w-sm items-start gap-3 border-2 border-ink bg-white px-4 py-3 text-sm font-medium text-ink shadow-xl"
                         role="status" aria-live="polite">
                        <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-forest-700 text-[10px] font-bold text-white">✓</span>
                        <span x-text="$store.cart.toast"></span>
                        <button type="button" x-on:click="$store.cart.toast = ''" class="ml-auto text-ink-faint transition hover:text-ink" aria-label="Dismiss notification">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        @endauth

        @stack('scripts')
    </body>
</html>
