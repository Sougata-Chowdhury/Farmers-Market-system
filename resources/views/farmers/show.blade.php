<x-app-layout :title="$farmer->storefront_name.' — Farmer storefront'">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-emerald-700">Shop</a>
            <span aria-hidden="true">/</span>
            <span>Farmers</span>
            <span aria-hidden="true">/</span>
            <span class="truncate font-medium text-slate-700">{{ $farmer->storefront_name }}</span>
        </nav>

        <section class="relative overflow-hidden rounded-3xl bg-emerald-950 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute -left-16 -top-20 h-64 w-64 rounded-full bg-emerald-500/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 right-0 h-72 w-72 rounded-full bg-lime-400/15 blur-3xl"></div>

            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-5">
                    <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-white/10 font-display text-3xl font-medium text-white ring-1 ring-inset ring-white/20">
                        {{ $farmer->initials }}
                    </span>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Farmer storefront</p>
                        <h1 class="mt-2 font-display text-3xl font-medium leading-tight sm:text-4xl">{{ $farmer->storefront_name }}</h1>
                        <p class="mt-2 text-sm text-emerald-100/80">
                            Grown by {{ $farmer->name }}
                            @if ($location = $farmer->farmerProfile?->location ?: $farmer->address)
                                &middot; {{ $location }}
                            @endif
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                            @if ($farmer->email_verified_at)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 font-semibold text-emerald-100 ring-1 ring-inset ring-white/20">
                                    <svg class="h-3.5 w-3.5 text-lime-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Verified farmer
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-emerald-100 ring-1 ring-inset ring-white/20">
                                <span class="text-amber-300" aria-hidden="true">★</span>
                                {{ number_format($rating, 1) }} &middot;
                                {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-emerald-100 ring-1 ring-inset ring-white/20">
                                Farming since {{ $farmer->created_at->format('F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <dl class="grid shrink-0 grid-cols-3 gap-3 text-center sm:gap-4">
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">Rating</dt>
                        <dd class="mt-1 font-display text-2xl font-medium">{{ number_format($rating, 1) }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">Delivered</dt>
                        <dd class="mt-1 font-display text-2xl font-medium">{{ number_format($ordersFulfilled) }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">Listings</dt>
                        <dd class="mt-1 font-display text-2xl font-medium">{{ number_format($products->total()) }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <section>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Fresh from the farm</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} available today
                        </p>
                    </div>
                    <a href="{{ route('home') }}" class="text-sm font-medium text-emerald-700 hover:underline">Browse all produce</a>
                </div>

                @if ($products->isEmpty())
                    <div class="mt-5">
                        <x-empty-state
                            title="This farmer has nothing listed right now"
                            description="Check back soon, or browse the rest of the market.">
                            <a href="{{ route('home') }}" class="btn-primary">Browse the market</a>
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </section>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">About this farm</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        {{ $farmer->farmerProfile?->bio ?: 'This farmer has been selling fresh produce on the market since '.$farmer->created_at->format('F Y').'.' }}
                    </p>

                    <dl class="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-slate-500">Location</dt>
                            <dd class="text-right font-medium text-slate-900">
                                {{ $farmer->farmerProfile?->location ?: ($farmer->address ?: 'Bangladesh') }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-slate-500">Member since</dt>
                            <dd class="text-right font-medium text-slate-900">{{ $farmer->created_at->format('d M Y') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-slate-500">Reviews</dt>
                            <dd class="text-right font-medium text-slate-900">{{ number_format($reviewCount) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                    <h2 class="font-display text-base font-semibold text-emerald-900">Why buy direct?</h2>
                    <ul class="mt-3 space-y-2 text-sm text-emerald-800">
                        <li class="flex gap-2"><span aria-hidden="true">✓</span> Harvested to order, not warehoused</li>
                        <li class="flex gap-2"><span aria-hidden="true">✓</span> The farmer keeps the full product price</li>
                        <li class="flex gap-2"><span aria-hidden="true">✓</span> One transparent 5% platform fee</li>
                    </ul>
                </div>
            </aside>
        </div>

        @if ($reviews->isNotEmpty())
            <section class="mt-12">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">What customers say</h2>
                        <p class="mt-1 text-sm text-slate-500">Verified reviews for produce from {{ $farmer->storefront_name }}.</p>
                    </div>
                    <x-stars :rating="$rating" :count="$reviewCount" size="lg" />
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($reviews as $review)
                        <article class="card p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">
                                        {{ $review->user?->initials }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $review->user?->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <x-stars :rating="$review->rating" />
                            </div>

                            @if ($review->body)
                                <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $review->body }}</p>
                            @endif

                            @if ($review->product)
                                <a href="{{ route('products.show', $review->product) }}" class="mt-3 inline-flex text-xs font-medium text-emerald-700 hover:underline">
                                    {{ $review->product->name }} &rarr;
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
