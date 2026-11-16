@php
    $navUser = auth()->user();
    $cartCount = $navUser?->isCustomer() ? $navUser->cartCount() : 0;
    $navNotifications = $navUser?->notifications()->latest()->limit(6)->get() ?? collect();
    $navUnread = $navUser?->unreadNotifications()->count() ?? 0;

    $links = match (true) {
        $navUser === null => [],
        $navUser->isAdmin() => [
            ['label' => 'Admin home', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
            ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
            ['label' => 'Farmers', 'route' => 'admin.farmers.index', 'active' => 'admin.farmers.*'],
        ],
        $navUser->isFarmer() => [
            ['label' => 'Dashboard', 'route' => 'farmer.dashboard', 'active' => 'farmer.dashboard'],
            ['label' => 'My products', 'route' => 'farmer.products.index', 'active' => 'farmer.products.*'],
            ['label' => 'Sales', 'route' => 'farmer.orders.index', 'active' => 'farmer.orders.*'],
        ],
        default => [
            ['label' => 'Shop', 'route' => 'home', 'active' => 'home'],
            ['label' => 'My orders', 'route' => 'orders.index', 'active' => 'orders.*'],
        ],
    };
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-600 text-white shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C8.5 4.5 5 8 5 12.5A7 7 0 0 0 12 19.5a7 7 0 0 0 7-7C19 8 15.5 4.5 12 2Z" opacity=".25" />
                    <path d="M12 21.5c-.4-4.2.6-7.4 3.2-10.1a.75.75 0 1 1 1.1 1c-2.3 2.5-3 5.2-2.7 9a.75.75 0 0 1-1.5.1Z" />
                    <path d="M12 21.5c.4-4.2-.6-7.4-3.2-10.1a.75.75 0 1 0-1.1 1c2.3 2.5 3 5.2 2.7 9a.75.75 0 0 0 1.5.1Z" />
                </svg>
            </span>
            <span class="text-base font-semibold tracking-tight text-slate-900 sm:text-lg">
                Farmers<span class="text-emerald-600">Market</span>
            </span>
        </a>

        <form action="{{ route('home') }}" method="GET" class="ml-2 hidden max-w-md flex-1 md:block">
            <label for="nav-search" class="sr-only">Search products</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                </svg>
                <input id="nav-search" name="q" value="{{ request('q') }}" placeholder="Search fresh produce..."
                       class="input !rounded-full py-2 pl-9">
            </div>
        </form>

        <nav class="ml-auto hidden items-center gap-1 lg:flex">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'rounded-lg px-3 py-2 text-sm font-medium transition',
                       'bg-emerald-50 text-emerald-700' => request()->routeIs($link['active']),
                       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($link['active']),
                   ])>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2 lg:ml-0">
            @auth
                <x-dropdown align="right" width="80" contentClasses="bg-white">
                    <x-slot name="trigger">
                        <button type="button"
                                class="relative inline-flex items-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:bg-slate-50"
                                aria-label="Notifications">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if ($navUnread > 0)
                                <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[11px] font-bold text-white">
                                    {{ $navUnread > 9 ? '9+' : $navUnread }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                            @if ($navUnread > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">
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
                                                'flex w-full gap-3 px-4 py-3 text-left transition hover:bg-slate-50',
                                                'bg-emerald-50/60' => $notification->read_at === null,
                                            ])>
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at === null ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                            <span class="mt-0.5 block text-xs leading-relaxed text-slate-500">{{ $notification->data['body'] ?? '' }}</span>
                                            <span class="mt-1 block text-[11px] uppercase tracking-wide text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                        </span>
                                    </button>
                                </form>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-slate-500">You're all caught up.</p>
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>

                @if ($navUser->isCustomer())
                    <a href="{{ route('cart.index') }}"
                       class="relative inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.4 5.25m0 0 2.05 7.18a1.5 1.5 0 0 0 1.44 1.09h8.72a1.5 1.5 0 0 0 1.44-1.09L20.4 5.25H5.4Zm3.35 12.75a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Zm9 0a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Z" />
                        </svg>
                        <span class="hidden sm:inline">Cart</span>
                        @if ($cartCount > 0)
                            <span class="absolute -right-2 -top-2 grid h-5 min-w-5 place-items-center rounded-full bg-emerald-600 px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">{{ $navUser->initials }}</span>
                            <span class="hidden max-w-28 truncate sm:inline">{{ $navUser->name }}</span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $navUser->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $navUser->email }}</p>
                            <p class="mt-1 inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $navUser->role->label() }}</p>
                        </div>

                        <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">Account settings</x-dropdown-link>

                        @if ($navUser->isCustomer())
                            <x-dropdown-link :href="route('orders.index')">My orders</x-dropdown-link>
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
                <a href="{{ route('login') }}" class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 sm:inline-flex">
                    Log in
                </a>
                <a href="{{ route('register') }}" class="btn-primary">Get started</a>
            @endguest

            <button type="button" @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm lg:hidden"
                    aria-label="Toggle navigation">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden">
        <form action="{{ route('home') }}" method="GET" class="mb-3">
            <label for="mobile-search" class="sr-only">Search products</label>
            <input id="mobile-search" name="q" value="{{ request('q') }}" placeholder="Search fresh produce..." class="input">
        </form>

        <div class="space-y-1">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @class([
                       'block rounded-lg px-3 py-2 text-sm font-medium',
                       'bg-emerald-50 text-emerald-700' => request()->routeIs($link['active']),
                       'text-slate-600 hover:bg-slate-100' => ! request()->routeIs($link['active']),
                   ])>
                    {{ $link['label'] }}
                </a>
            @endforeach

            @guest
                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Log in</a>
            @endguest
        </div>
    </div>
</header>
