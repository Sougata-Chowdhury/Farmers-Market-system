<x-app-layout title="Fresh produce from local farms"
               description="Shop farm-fresh vegetables, fruit, fish, rice and dairy from verified Bangladeshi farmers. Transparent pricing, a flat platform fee and delivery to your door.">
    @php
        $heroProducts = collect($products->items())->take(3);
        $heroLead = $heroProducts->first();
        $heroSide = $heroProducts->get(1) ?? $heroProducts->first();
        $seasonalTotal = $categories->sum('products_count');
    @endphp

    <section class="relative overflow-hidden border-b-2 border-ink bg-paper-light">
        <div class="pointer-events-none absolute inset-0 bg-paper-dots bg-dots opacity-40" aria-hidden="true"></div>

        <div class="relative mx-auto grid max-w-7xl gap-14 px-4 py-14 sm:px-6 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:gap-10 lg:px-8 lg:py-20">
            <div>
                <p class="flex items-center gap-3 eyebrow">
                    <span class="inline-block h-px w-10 bg-clay-500"></span>
                    A market for small farms &middot; Dhaka
                </p>

                <h1 class="mt-5 max-w-xl font-display text-[2.85rem] font-medium leading-[1.03] tracking-tight text-balance sm:text-6xl">
                    Farm food, grown by people you could
                    <span class="relative inline-block">
                        actually
                        <svg class="absolute -bottom-2 left-0 h-3 w-full text-ochre-400" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M2 8c34-5 64-6 98-4 34 2 63 1 98-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                        </svg>
                    </span>
                    meet.
                </h1>

                <p class="mt-6 max-w-lg text-[15px] leading-relaxed text-ink-soft sm:text-base">
                    Browse the morning's harvest from {{ $categories->count() }} kinds of produce, buy straight
                    from the farm, and pay one honest platform fee. The farmer keeps the rest.
                </p>

                <form action="{{ route('home') }}" method="GET" class="mt-8 max-w-lg">
                    <div class="relative border-2 border-ink bg-white p-2 shadow-lg">
                        <span class="tape -top-3 left-6 -rotate-3" aria-hidden="true"></span>
                        <label for="hero-search" class="sr-only">Search products</label>
                        <div class="flex items-stretch">
                            <div class="relative flex-1">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-faint" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                                </svg>
                                <input id="hero-search" name="q" value="{{ $filters['q'] }}"
                                       placeholder="What are you cooking this week?"
                                       class="w-full border-0 bg-transparent py-3 pl-10 pr-3 text-[15px] text-ink placeholder:text-ink-faint focus:ring-0">
                            </div>
                            <button type="submit" class="btn-primary shrink-0">Search</button>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                        <span class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Try</span>
                        @foreach ($categories->take(4) as $category)
                            <a href="{{ route('home', ['category' => $category->slug]) }}"
                               class="font-medium text-ink-soft underline decoration-line-dark decoration-1 underline-offset-4 transition hover:text-clay-600 hover:decoration-clay-500">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </form>

                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 font-mono text-[10px] uppercase tracking-label text-ink-soft">
                    <li class="flex items-center gap-2"><span class="text-forest-600" aria-hidden="true">✓</span> Harvested to order</li>
                    <li class="flex items-center gap-2"><span class="text-forest-600" aria-hidden="true">✓</span> One flat fee</li>
                    <li class="flex items-center gap-2"><span class="text-forest-600" aria-hidden="true">✓</span> No middlemen</li>
                </ul>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="relative h-[24rem] sm:h-[28rem]">
                    @if ($heroLead)
                        <figure class="absolute left-0 top-2 w-[72%] -rotate-2 border-[7px] border-white bg-white shadow-xl">
                            <img src="{{ $heroLead->image_url }}" alt="{{ $heroLead->name }}" loading="eager"
                                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                                 class="aspect-[4/5] w-full object-cover">
                            <figcaption class="flex items-center justify-between gap-2 px-1 pb-0.5 pt-2 font-mono text-[9px] uppercase tracking-label text-ink-soft">
                                <span>{{ $heroLead->name }}</span>
                                <span class="text-clay-600">from {{ $heroLead->farmer?->name }}</span>
                            </figcaption>
                            <span class="tape -top-2 left-1/2 -translate-x-1/2 -rotate-2" aria-hidden="true"></span>
                        </figure>
                    @endif

                    @if ($heroSide)
                        <figure class="absolute bottom-0 right-0 w-[52%] rotate-3 border-[6px] border-white bg-white shadow-xl">
                            <img src="{{ $heroSide->image_url }}" alt="{{ $heroSide->name }}" loading="lazy"
                                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                                 class="aspect-square w-full object-cover">
                            <figcaption class="px-1 py-1.5 text-center font-mono text-[9px] uppercase tracking-label text-ink-soft">
                                {{ $heroSide->name }}
                            </figcaption>
                            <span class="tape -top-2 right-4 rotate-6" aria-hidden="true"></span>
                        </figure>
                    @endif

                    <div class="absolute -right-2 top-6 grid h-24 w-24 -rotate-6 place-items-center rounded-full border-[3px] border-double border-clay-600 bg-paper-light/90 text-center shadow-md sm:right-2">
                        <div>
                            <p class="font-mono text-[8px] font-bold uppercase tracking-[0.18em] text-clay-700">Harvested</p>
                            <p class="font-display text-lg font-semibold leading-none text-clay-700">this week</p>
                            <p class="mt-1 font-mono text-[8px] uppercase tracking-[0.18em] text-clay-600">{{ now()->format('M j') }}</p>
                        </div>
                    </div>

                    <div class="absolute bottom-10 left-2 -rotate-2 bg-paper-deep px-3 py-2 shadow-md">
                        <p class="hand text-xl leading-tight text-ink">Straight from {{ $heroLead?->farmer?->name ?? 'the farm' }}'s plot</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative border-t border-line-dark">
            <div class="mx-auto grid max-w-7xl grid-cols-3 divide-x divide-line-dark/70 px-4 sm:px-6 lg:px-8">
                <div class="px-2 py-5 sm:px-6">
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">On the stall</p>
                    <p class="mt-1 font-display text-2xl font-semibold sm:text-3xl">{{ number_format($products->total()) }}</p>
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-soft">products today</p>
                </div>
                <div class="px-2 py-5 sm:px-6">
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">Produce aisles</p>
                    <p class="mt-1 font-display text-2xl font-semibold sm:text-3xl">{{ $categories->count() }}</p>
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-soft">categories</p>
                </div>
                <div class="px-2 py-5 sm:px-6">
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">Our cut</p>
                    <p class="mt-1 font-display text-2xl font-semibold sm:text-3xl">{{ (int) (config('marketplace.platform_fee') * 100) }}%</p>
                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-soft">flat, always</p>
                </div>
            </div>
        </div>
    </section>

    <nav aria-label="Produce categories" class="border-b border-line bg-paper">
        <div class="mx-auto flex max-w-7xl items-center gap-5 overflow-x-auto px-4 py-4 sm:px-6 lg:px-8">
            <span class="shrink-0 font-mono text-[10px] font-bold uppercase tracking-label text-clay-600">In season</span>
            <span class="h-4 w-px shrink-0 bg-line-dark" aria-hidden="true"></span>
            @foreach ($categories as $category)
                <a href="{{ route('home', array_filter(['q' => $filters['q'] ?: null, 'category' => $category->slug, 'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : null])) }}"
                   @if ($filters['category'] === $category->slug) aria-current="page" @endif
                   @class([
                       'group flex shrink-0 items-baseline gap-1.5 whitespace-nowrap font-display text-[15px] transition',
                       'text-clay-600 underline decoration-clay-500 decoration-2 underline-offset-[6px]' => $filters['category'] === $category->slug,
                       'text-ink-soft hover:text-ink' => $filters['category'] !== $category->slug,
                   ])>
                    {{ $category->name }}
                    <span class="font-mono text-[9px] text-ink-faint">{{ $category->products_count }}</span>
                </a>
            @endforeach
            @if ($seasonalTotal > 0)
                <span class="ml-auto hidden shrink-0 font-mono text-[9px] uppercase tracking-label text-ink-faint sm:block">
                    {{ $seasonalTotal }} listings &middot; picked near you
                </span>
            @endif
        </div>
    </nav>

    <section id="catalogue" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
             x-data="{ filtersOpen: false }" x-on:keydown.escape.window="filtersOpen = false">
        @php
            $activeFilters = ($filters['q'] !== '' ? 1 : 0) + ($filters['category'] !== '' ? 1 : 0) + ($filters['sort'] !== 'newest' ? 1 : 0);
            $currentCategory = $filters['category'] ? $categories->firstWhere('slug', $filters['category']) : null;
        @endphp

        <div class="mb-3 flex items-center justify-between gap-3 sm:hidden">
            <button type="button" x-on:click="filtersOpen = true"
                    x-bind:aria-expanded="filtersOpen ? 'true' : 'false'" aria-haspopup="dialog"
                    class="btn-secondary !py-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M6 12h12M10 18h4" />
                </svg>
                Filter
                @if ($activeFilters > 0)
                    <span class="ml-1 grid h-5 min-w-5 place-items-center rounded-full bg-clay-600 px-1 font-mono text-[10px] font-bold text-white">{{ $activeFilters }}</span>
                @endif
            </button>
            <p class="truncate font-mono text-[10px] uppercase tracking-label text-ink-soft">{{ $currentCategory?->name ?? 'All produce' }}</p>
        </div>

        <div class="mt-2 flex flex-wrap items-end justify-between gap-4 border-b-2 border-ink pb-4">
            <div>
                <p class="eyebrow">
                    @if ($filters['q'])
                        Search results
                    @elseif ($filters['category'])
                        The {{ $currentCategory?->name ?? 'produce' }} aisle
                    @else
                        The stall
                    @endif
                </p>
                <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight sm:text-4xl">
                    @if ($filters['q'])
                        Results for &ldquo;{{ $filters['q'] }}&rdquo;
                    @elseif ($filters['category'])
                        {{ $currentCategory?->name ?? 'Produce' }}
                    @else
                        Today's harvest
                    @endif
                </h2>
                <p class="mt-1 text-sm text-ink-soft">
                    {{ number_format($products->total()) }} {{ Str::plural('product', $products->total()) }} available
                    @if ($filters['q'] || $filters['category'])
                        &middot; <a href="{{ route('home') }}" class="font-semibold text-clay-600 hover:underline">clear the table</a>
                    @endif
                </p>
            </div>

            <form method="GET" action="{{ route('home') }}" class="flex flex-wrap items-center gap-2">
                @if ($filters['q'])
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                @endif
                @if ($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif

                <label for="sort" class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Arrange</label>
                <select id="sort" name="sort" class="input w-auto border-ink/40 py-2 text-sm">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: high to low</option>
                    <option value="rating" @selected($filters['sort'] === 'rating')>Top rated</option>
                </select>
                <button type="submit" class="btn-secondary !py-2">Apply</button>
            </form>
        </div>

        <div x-cloak x-show="filtersOpen" class="fixed inset-0 z-50 sm:hidden" role="dialog" aria-modal="true" aria-label="Filter produce">
            <div x-show="filtersOpen" x-transition.opacity
                 class="absolute inset-0 bg-ink/50"
                 x-on:click="filtersOpen = false"></div>

            <div x-show="filtersOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto border-t-2 border-ink bg-paper-light p-5 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="eyebrow">Narrow the stall</p>
                        <h2 class="mt-1 font-display text-2xl font-semibold">Filter produce</h2>
                    </div>
                    <button type="button" x-on:click="filtersOpen = false"
                            class="grid h-9 w-9 place-items-center border border-ink/30 text-ink-soft transition hover:border-ink hover:text-ink"
                            aria-label="Close filters">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-5 font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Category</p>
                <div class="mt-2 grid gap-1">
                    <a href="{{ route('home', array_filter(['q' => $filters['q'] ?: null, 'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : null])) }}"
                       x-on:click="filtersOpen = false"
                       @class([
                           'flex items-center justify-between border-b border-line/70 px-1 py-2.5 text-sm font-medium',
                           'text-clay-600' => $filters['category'] === '',
                           'text-ink-soft' => $filters['category'] !== '',
                       ])>
                        All produce
                        @if ($filters['category'] === '')
                            <span aria-hidden="true">&check;</span>
                        @endif
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('home', array_filter(['q' => $filters['q'] ?: null, 'category' => $category->slug, 'sort' => $filters['sort'] !== 'newest' ? $filters['sort'] : null])) }}"
                           x-on:click="filtersOpen = false"
                           @class([
                               'flex items-center justify-between border-b border-line/70 px-1 py-2.5 text-sm font-medium',
                               'text-clay-600' => $filters['category'] === $category->slug,
                               'text-ink-soft' => $filters['category'] !== $category->slug,
                           ])>
                            <span>{{ $category->name }}</span>
                            <span class="flex items-center gap-2 font-mono text-[10px] text-ink-faint">
                                {{ $category->products_count }}
                                @if ($filters['category'] === $category->slug)
                                    <span class="text-clay-600" aria-hidden="true">&check;</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('home') }}" class="mt-5 space-y-3 border-t border-dashed border-line-dark pt-5">
                    @if ($filters['q'])
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif
                    @if ($filters['category'])
                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                    @endif

                    <label for="mobile-sort" class="label">Arrange by</label>
                    <select id="mobile-sort" name="sort" class="input">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: high to low</option>
                        <option value="rating" @selected($filters['sort'] === 'rating')>Top rated</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-primary flex-1">Show results</button>
                        <a href="{{ route('home') }}" class="btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state
                    title="No produce matched your search"
                    description="Try a different keyword, or wander over to another aisle.">
                    <a href="{{ route('home') }}" class="btn-secondary">Clear filters</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-8 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-10 border-t border-dashed border-line-dark pt-6">
                {{ $products->links() }}
            </div>
        @endif
    </section>

    <section class="border-y-2 border-ink bg-forest-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow !text-ochre-300">How the market works</p>
                    <h2 class="mt-2 max-w-xl font-display text-3xl font-medium leading-tight text-white sm:text-4xl">
                        No warehouse. No middleman. Just a fair handshake.
                    </h2>
                </div>
                @guest
                    <a href="{{ route('register') }}" class="border border-white/40 px-4 py-2.5 font-mono text-[10px] font-bold uppercase tracking-label text-white transition hover:bg-white hover:text-forest-900">
                        Open an account
                    </a>
                @endguest
            </div>

            <div class="mt-10 grid gap-8 sm:grid-cols-3">
                <div class="border-t border-white/25 pt-5">
                    <p class="font-display text-4xl font-medium text-ochre-300">01</p>
                    <h3 class="mt-3 font-display text-xl text-white">Pick from the board</h3>
                    <p class="mt-2 text-sm leading-relaxed text-forest-100/85">
                        Everything listed is picked to order by the farmer who grew it. Stock counts are live, so what you see is what's left.
                    </p>
                </div>
                <div class="border-t border-white/25 pt-5">
                    <p class="font-display text-4xl font-medium text-ochre-300">02</p>
                    <h3 class="mt-3 font-display text-xl text-white">Pay one plain fee</h3>
                    <p class="mt-2 text-sm leading-relaxed text-forest-100/85">
                        A single {{ (int) (config('marketplace.platform_fee') * 100) }}% platform fee, shown before you confirm. The farmer keeps the full price of the produce.
                    </p>
                </div>
                <div class="border-t border-white/25 pt-5">
                    <p class="font-display text-4xl font-medium text-ochre-300">03</p>
                    <h3 class="mt-3 font-display text-xl text-white">Meet it at the door</h3>
                    <p class="mt-2 text-sm leading-relaxed text-forest-100/85">
                        Your order is packed at the farm and delivered on the next market run. Track every status change from your account.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <p class="hand text-3xl leading-snug text-clay-600 sm:text-4xl">
            &ldquo;I know most of my customers by name now. That's the whole point, isn't it?&rdquo;
        </p>
        <p class="mt-5 font-mono text-[10px] uppercase tracking-label text-ink-faint">
            Ayesha Begum &middot; leafy greens &amp; herbs &middot; Savar
        </p>
        <div class="mx-auto mt-8 h-px w-24 bg-line-dark" aria-hidden="true"></div>
        @guest
            <p class="mt-8 text-sm text-ink-soft">
                Have a farm of your own?
                <a href="{{ route('register') }}" class="link">Open a stall on the market</a>.
            </p>
        @endguest
    </section>
</x-app-layout>
