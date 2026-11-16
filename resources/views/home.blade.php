<x-app-layout title="Fresh produce from local farms">
    <section class="relative overflow-hidden bg-emerald-900 text-white">
        <div class="pointer-events-none absolute -left-20 -top-24 h-80 w-80 rounded-full bg-emerald-500/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-lime-400/20 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:px-8 lg:py-20">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider ring-1 ring-inset ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-lime-300"></span>
                    Direct from {{ $categories->count() }} categories of local produce
                </span>

                <h1 class="mt-5 font-display text-4xl font-medium leading-tight sm:text-5xl">
                    Farm-fresh food, from the people who actually grow it.
                </h1>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-emerald-100/90 sm:text-base">
                    Browse today's harvest, add it to your cart and pay a single transparent platform fee.
                    Every farmer keeps their own storefront, stock levels and earnings.
                </p>

                <form action="{{ route('home') }}" method="GET" class="mt-8 max-w-xl">
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                            </svg>
                            <label for="hero-search" class="sr-only">Search products</label>
                            <input id="hero-search" name="q" value="{{ $filters['q'] }}"
                                   placeholder="Try 'tomato', 'banana' or 'koi fish'"
                                   class="input h-12 !rounded-xl border-0 pl-11 text-slate-900 shadow-lg">
                        </div>
                        <button type="submit" class="btn-primary h-12 !rounded-xl !bg-white !px-6 !text-emerald-800 hover:!bg-emerald-50">
                            Search
                        </button>
                    </div>
                </form>

                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium uppercase tracking-wider text-emerald-200/80">Popular:</span>
                    @foreach ($categories->take(4) as $category)
                        <a href="{{ route('home', ['category' => $category->slug]) }}"
                           class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white ring-1 ring-inset ring-white/20 transition hover:bg-white/20">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">Listed today</p>
                    <p class="mt-1 font-display text-3xl font-medium">{{ number_format($products->total()) }}</p>
                    <p class="text-sm text-emerald-100/80">products across {{ $categories->count() }} categories</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200">How pricing works</p>
                    <p class="mt-1 font-display text-3xl font-medium">{{ (int) (config('marketplace.platform_fee') * 100) }}%</p>
                    <p class="text-sm text-emerald-100/80">flat platform fee, shown before you pay</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur sm:col-span-2 lg:col-span-1">
                    <p class="text-sm text-emerald-100/90">
                        Farmers see live orders, stock and earnings — no spreadsheets, no guessing.
                    </p>
                    @guest
                        <a href="{{ route('register') }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-white underline-offset-4 hover:underline">
                            Create a free account
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <section id="catalogue" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-3 flex -mx-1 gap-2 overflow-x-auto px-1 pb-1">
            <a href="{{ route('home', ['q' => $filters['q'] ?: null]) }}"
               @class([
                   'shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition',
                   'border-emerald-600 bg-emerald-600 text-white' => $filters['category'] === '',
                   'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' => $filters['category'] !== '',
               ])>
                All produce
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('home', array_filter(['q' => $filters['q'] ?: null, 'category' => $category->slug])) }}"
                   @class([
                       'shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition',
                       'border-emerald-600 bg-emerald-600 text-white' => $filters['category'] === $category->slug,
                       'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' => $filters['category'] !== $category->slug,
                   ])>
                    {{ $category->name }}
                    <span @class([
                        'ml-1 text-xs',
                        'text-emerald-100' => $filters['category'] === $category->slug,
                        'text-slate-400' => $filters['category'] !== $category->slug,
                    ])>{{ $category->products_count }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">
                    @if ($filters['q'])
                        Results for &ldquo;{{ $filters['q'] }}&rdquo;
                    @elseif ($filters['category'])
                        {{ $categories->firstWhere('slug', $filters['category'])?->name ?? 'Produce' }}
                    @else
                        Today's harvest
                    @endif
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($products->total()) }} {{ Str::plural('product', $products->total()) }} available
                </p>
            </div>

            <form method="GET" action="{{ route('home') }}" class="flex flex-wrap items-center gap-2">
                @if ($filters['q'])
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                @endif
                @if ($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif

                <label for="sort" class="sr-only">Sort products</label>
                <select id="sort" name="sort" class="input w-auto py-2">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: high to low</option>
                    <option value="rating" @selected($filters['sort'] === 'rating')>Top rated</option>
                </select>
                <button type="submit" class="btn-secondary py-2">Apply</button>
                @if ($filters['q'] || $filters['category'] || $filters['sort'] !== 'newest')
                    <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-900">Reset</a>
                @endif
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state
                    title="No produce matched your search"
                    description="Try a different keyword or browse another category.">
                    <a href="{{ route('home') }}" class="btn-secondary">Clear filters</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
