<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#047857">

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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased" x-data>
        <a href="#main-content"
           class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[80] focus:rounded-lg focus:bg-emerald-700 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
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

            <footer class="mt-16 border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Farmers Market</p>
                        <p class="mt-1 max-w-md text-sm text-slate-500">
                            A direct-to-consumer marketplace connecting local farmers with households in Bangladesh.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                        <a href="{{ route('home') }}" class="hover:text-emerald-700">Browse products</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-emerald-700">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-emerald-700">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-emerald-700">Create account</a>
                        @endauth
                        <span class="text-slate-400">&copy; {{ now()->year }} Farmers Market</span>
                    </div>
                </div>
            </footer>
        </div>

        @auth
            @if (auth()->user()->isCustomer())
                <div x-cloak x-show="$store.cart.open" class="fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Shopping cart"
                     x-effect="document.body.classList.toggle('overflow-y-hidden', $store.cart.open)"
                     x-on:keydown.escape.window="$store.cart.open = false"
                     x-init="$watch('$store.cart.open', open => { if (open) { $nextTick(() => $refs.cartClose?.focus()) } })">
                    <div x-show="$store.cart.open" x-transition.opacity
                         class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                         x-on:click="$store.cart.open = false"></div>

                    <aside x-show="$store.cart.open"
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="translate-x-full"
                           x-transition:enter-end="translate-x-0"
                           x-transition:leave="transition ease-in duration-200"
                           x-transition:leave-start="translate-x-0"
                           x-transition:leave-end="translate-x-full"
                           class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl">
                        <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4">
                            <div>
                                <h2 class="font-display text-lg font-semibold text-slate-900">Your cart</h2>
                                <p class="text-xs text-slate-500" x-text="$store.cart.count + ' ' + ($store.cart.count === 1 ? 'item' : 'items')"></p>
                            </div>
                            <button type="button" x-on:click="$store.cart.open = false" x-ref="cartClose"
                                    class="grid h-9 w-9 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                                    aria-label="Close cart">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </header>

                        <div class="flex-1 overflow-y-auto">
                            <template x-if="$store.cart.html === ''">
                                <div class="flex h-full items-center justify-center p-8 text-sm text-slate-500">
                                    Loading your cart&hellip;
                                </div>
                            </template>
                            <div x-html="$store.cart.html"></div>
                        </div>
                    </aside>
                </div>

                <div class="pointer-events-none fixed inset-x-0 bottom-4 z-[70] flex justify-center px-4 sm:justify-end sm:px-6">
                    <div x-cloak x-show="$store.cart.toast" x-transition
                         class="pointer-events-auto flex items-center gap-3 rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white shadow-xl"
                         role="status" aria-live="polite">
                        <span x-text="$store.cart.toast"></span>
                        <button type="button" x-on:click="$store.cart.toast = ''" class="text-slate-400 transition hover:text-white" aria-label="Dismiss notification">
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
