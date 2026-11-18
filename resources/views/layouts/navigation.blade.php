@php
    $navUser = auth()->user();
    $cartCount = $navUser?->isCustomer() ? $navUser->cartCount() : 0;
    $wishlistCount = $navUser?->isCustomer() ? app(\App\Services\WishlistService::class)->count(request()) : 0;
    $navNotifications = $navUser?->notifications()->latest()->limit(6)->get() ?? collect();
    $navUnread = $navUser?->unreadNotifications()->count() ?? 0;

    $links = match (true) {
        $navUser === null => [],
        $navUser->isAdmin() => [
            ['label' => 'Overview', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
            ['label' => 'Produce', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
            ['label' => 'Farmers', 'route' => 'admin.farmers.index', 'active' => 'admin.farmers.*'],
            ['label' => 'Customers', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*'],
            ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*'],
        ],
        $navUser->isFarmer() => [
            ['label' => 'My stall', 'route' => 'farmer.dashboard', 'active' => 'farmer.dashboard'],
            ['label' => 'Produce', 'route' => 'farmer.products.index', 'active' => 'farmer.products.*'],
            ['label' => 'Sales', 'route' => 'farmer.orders.index', 'active' => 'farmer.orders.*'],
        ],
        default => [
            ['label' => 'The market', 'route' => 'home', 'active' => 'home'],
            ['label' => 'My orders', 'route' => 'orders.index', 'active' => 'orders.*'],
        ],
    };
@endphp

<div class="border-b border-line-dark/70 bg-paper-deep/80">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-1.5 font-mono text-[10px] uppercase tracking-label text-ink-soft sm:px-6 lg:px-8">
        <p class="flex items-center gap-2">
            <span class="inline-block h-1.5 w-1.5 rounded-full bg-clay-500"></span>
            <span class="hidden sm:inline">A direct market for small farms</span>
            <span class="sm:hidden">Grown local</span>
        </p>
        <p class="hidden items-center gap-4 sm:flex">
            <span>Delivery Tue &amp; Fri</span>
            <span class="text-line-dark">/</span>
            <span>5% flat platform fee</span>
            @guest
                <a href="{{ route('register') }}" class="text-clay-600 underline-offset-4 hover:underline">Sell with us</a>
            @endguest
        </p>
    </div>
</div>

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-ink bg-paper/95">
    <div class="mx-auto flex h-[4.5rem] max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="Farmers Market home">
            <span class="grid h-10 w-10 place-items-center rounded-md border border-forest-900 bg-forest-700 text-white shadow-sm">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5 3.2-8.2 8-8.5-.4 4.8-3.5 8-8 8.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-5-3.2-8.2-8-8.5.4 4.8 3.5 8 8 8.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 17.5V9" />
                </svg>
            </span>
            <span class="flex flex-col leading-none">
                <span class="font-display text-[1.35rem] font-semibold tracking-tight">Farmers Market</span>
                <span class="mt-1 font-mono text-[8px] uppercase tracking-[0.28em] text-ink-faint">Field to table</span>
            </span>
        </a>

        <form action="{{ route('home') }}" method="GET" class="ml-2 hidden max-w-sm flex-1 md:block">
            <label for="nav-search" class="sr-only">Search produce</label>
            <div class="flex items-stretch">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                    </svg>
                    <input id="nav-search" name="q" value="{{ request('q') }}" placeholder="tomato, mango, koi fish…"
                           class="w-full rounded-l-md border border-r-0 border-line bg-white py-2 pl-9 pr-3 text-sm text-ink placeholder:text-ink-faint focus:border-forest-600 focus:ring-0">
                </div>
                <button type="submit" class="rounded-r-md border border-forest-900 bg-forest-700 px-3 font-mono text-[10px] font-bold uppercase tracking-label text-white transition hover:bg-forest-800">
                    Find
                </button>
            </div>
        </form>

        <nav class="ml-auto hidden items-center gap-5 lg:flex" aria-label="Main">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @if (request()->routeIs($link['active'])) aria-current="page" @endif
                   class="nav-link">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2 lg:ml-0">
            @auth
                <x-dropdown align="right" width="80" contentClasses="bg-paper-light">
                    <x-slot name="trigger">
                        <button type="button"
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-line bg-white text-ink-soft transition hover:border-ink hover:text-ink"
                                aria-label="Notifications">
                            <svg class="h-[1.15rem] w-[1.15rem]" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if ($navUnread > 0)
                                <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full border border-white bg-clay-600 px-1 font-mono text-[10px] font-bold text-white">
                                    {{ $navUnread > 9 ? '9+' : $navUnread }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="flex items-center justify-between gap-3 border-b border-dashed border-line-dark px-4 py-3">
                            <p class="eyebrow">Notice board</p>
                            @if ($navUnread > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="font-mono text-[10px] font-bold uppercase tracking-label text-forest-700 hover:underline">
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse ($navNotifications as $notification)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit"
                                            @class([
                                                'flex w-full gap-3 border-b border-line/60 px-4 py-3 text-left transition hover:bg-paper',
                                                'bg-white' => $notification->read_at === null,
                                            ])>
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at === null ? 'bg-forest-600' : 'bg-line-dark' }}"></span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-ink">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                            <span class="mt-0.5 block text-xs leading-relaxed text-ink-soft">{{ $notification->data['body'] ?? '' }}</span>
                                            <span class="mt-1 block font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ $notification->created_at->diffForHumans() }}</span>
                                        </span>
                                    </button>
                                </form>
                            @empty
                                <p class="hand px-4 py-6 text-center text-xl text-ink-faint">You're all caught up.</p>
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>

                @if ($navUser->isCustomer())
                    <a href="{{ route('wishlist.index') }}"
                       class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-line bg-white text-ink-soft transition hover:border-ink hover:text-clay-600"
                       aria-label="Wishlist{{ $wishlistCount > 0 ? ' ('.$wishlistCount.' saved)' : '' }}">
                        <svg class="h-[1.15rem] w-[1.15rem]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        @if ($wishlistCount > 0)
                            <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full border border-white bg-clay-600 px-1 font-mono text-[10px] font-bold text-white">{{ $wishlistCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('cart.index') }}"
                       x-on:click.prevent="$store.cart.open = true; $store.cart.ensureLoaded()"
                       x-bind:aria-expanded="$store.cart.open ? 'true' : 'false'"
                       class="relative inline-flex h-10 items-center gap-2 rounded-md border border-forest-900 bg-forest-700 px-3 text-white shadow-sm transition hover:bg-forest-800"
                       aria-label="Open basket">
                        <svg class="h-[1.15rem] w-[1.15rem]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.4 5.25m0 0 2.05 7.18a1.5 1.5 0 0 0 1.44 1.09h8.72a1.5 1.5 0 0 0 1.44-1.09L20.4 5.25H5.4Zm3.35 12.75a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Zm9 0a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Z" />
                        </svg>
                        <span class="hidden font-mono text-[10px] font-bold uppercase tracking-label sm:inline">Basket</span>
                        <span data-cart-count="{{ $cartCount }}"
                              x-cloak x-show="$store.cart.count > 0"
                              x-text="$store.cart.count > 99 ? '99+' : $store.cart.count"
                              class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 font-mono text-[10px] font-bold text-forest-800">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    </a>
                @endif

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-md border border-line bg-white py-1.5 pl-1.5 pr-3 text-sm font-semibold text-ink transition hover:border-ink">
                            <span class="grid h-7 w-7 place-items-center rounded-sm bg-forest-700 font-mono text-[11px] font-bold text-white">{{ $navUser->initials }}</span>
                            <span class="hidden max-w-28 truncate sm:inline">{{ $navUser->name }}</span>
                            <svg class="h-4 w-4 text-ink-faint" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-dashed border-line-dark px-4 py-3">
                            <p class="truncate font-display text-base font-semibold text-ink">{{ $navUser->name }}</p>
                            <p class="truncate font-mono text-[10px] text-ink-soft">{{ $navUser->email }}</p>
                            <p class="mt-2 inline-flex border border-forest-700/30 bg-forest-50 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label text-forest-700">{{ $navUser->role->label() }}</p>
                        </div>

                        <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">Account settings</x-dropdown-link>

                        @if ($navUser->isCustomer())
                            <x-dropdown-link :href="route('orders.index')">My orders</x-dropdown-link>
                            <x-dropdown-link :href="route('wishlist.index')">Saved items ({{ $wishlistCount }})</x-dropdown-link>
                            <x-dropdown-link :href="route('addresses.index')">Delivery addresses</x-dropdown-link>
                        @endif

                        @if ($navUser->isFarmer())
                            <x-dropdown-link :href="route('farmer.profile.edit')">Storefront &amp; payouts</x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="hidden px-2 font-mono text-[11px] font-bold uppercase tracking-label text-ink-soft transition hover:text-ink sm:inline-flex">
                    Log in
                </a>
                <a href="{{ route('register') }}" class="btn-primary hidden sm:inline-flex">Join the market</a>
            @endguest

            <button type="button" @click="open = ! open"
                    x-bind:aria-expanded="open ? 'true' : 'false'" aria-controls="mobile-navigation"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-line bg-white text-ink lg:hidden"
                    aria-label="Toggle navigation">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-navigation" x-show="open" x-cloak class="border-t border-dashed border-line-dark bg-paper-light px-4 py-5 lg:hidden">
        <form action="{{ route('home') }}" method="GET" class="mb-4">
            <label for="mobile-search" class="sr-only">Search produce</label>
            <div class="flex">
                <input id="mobile-search" name="q" value="{{ request('q') }}" placeholder="Search the market…"
                       class="w-full rounded-l-md border border-r-0 border-line bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-faint focus:border-forest-600 focus:ring-0">
                <button type="submit" class="rounded-r-md border border-forest-900 bg-forest-700 px-4 font-mono text-[10px] font-bold uppercase tracking-label text-white">Find</button>
            </div>
        </form>

        <div class="grid gap-1">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @if (request()->routeIs($link['active'])) aria-current="page" @endif
                   @class([
                       'flex items-center justify-between border-b border-line/70 px-2 py-3 font-display text-lg font-medium',
                       'text-clay-600' => request()->routeIs($link['active']),
                       'text-ink' => ! request()->routeIs($link['active']),
                   ])>
                    {{ $link['label'] }}
                    <span aria-hidden="true" class="font-mono text-xs text-ink-faint">&rarr;</span>
                </a>
            @endforeach

            @if ($navUser?->isCustomer())
                <a href="{{ route('wishlist.index') }}" class="border-b border-line/70 px-2 py-3 font-display text-lg font-medium text-ink">
                    Saved items{{ $wishlistCount > 0 ? ' ('.$wishlistCount.')' : '' }}
                </a>
                <a href="{{ route('cart.index') }}" class="border-b border-line/70 px-2 py-3 font-display text-lg font-medium text-ink">
                    Basket{{ $cartCount > 0 ? ' ('.$cartCount.')' : '' }}
                </a>
            @endif

            @guest
                <a href="{{ route('login') }}" class="border-b border-line/70 px-2 py-3 font-display text-lg font-medium text-ink">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary mt-3 w-full">Join the market</a>
            @endguest
        </div>
    </div>
</header>
