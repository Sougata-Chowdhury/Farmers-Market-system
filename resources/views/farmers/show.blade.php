<x-app-layout :title="$farmer->storefront_name.' — Farmer storefront'"
               :description="\Illuminate\Support\Str::limit(strip_tags($farmer->farmerProfile?->bio ?: 'Fresh produce grown by '.$farmer->name.' in '.($farmer->farmerProfile?->location ?: $farmer->address ?: 'Bangladesh').'. Order direct from the farm.'), 155)">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-5 flex flex-wrap items-center gap-2 font-mono text-[10px] uppercase tracking-label text-ink-faint" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-clay-600">The market</a>
            <span aria-hidden="true">/</span>
            <span>Farmers</span>
            <span aria-hidden="true">/</span>
            <span class="truncate text-ink">{{ $farmer->storefront_name }}</span>
        </nav>

        <section class="relative overflow-hidden border-2 border-ink bg-forest-900 text-white">
            <div class="pointer-events-none absolute inset-0 bg-grain opacity-25" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full border-[22px] border-ochre-300/10" aria-hidden="true"></div>

            <div class="relative flex flex-col gap-8 p-6 sm:p-10 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex items-start gap-5">
                    <span class="hidden h-20 w-20 shrink-0 place-items-center border border-white/40 bg-white/10 font-display text-3xl font-medium text-white sm:grid">
                        {{ $farmer->initials }}
                    </span>

                    <div class="min-w-0">
                        <p class="eyebrow !text-ochre-300">Farmer storefront</p>
                        <h1 class="mt-2 font-display text-3xl font-medium leading-tight text-white sm:text-5xl">{{ $farmer->storefront_name }}</h1>
                        <p class="mt-3 text-sm text-forest-100/85">
                            Grown by {{ $farmer->name }}
                            @if ($location = $farmer->farmerProfile?->location ?: $farmer->address)
                                &middot; {{ $location }}
                            @endif
                        </p>

                        <div class="mt-5 flex flex-wrap items-center gap-2 font-mono text-[9px] uppercase tracking-label">
                            @if ($farmer->email_verified_at)
                                <span class="inline-flex items-center gap-1.5 border border-white/30 px-2.5 py-1 text-forest-50">
                                    <svg class="h-3.5 w-3.5 text-ochre-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Verified farmer
                                </span>
                            @endif

                            <span class="border border-white/30 px-2.5 py-1 text-forest-50">
                                <span class="text-ochre-300" aria-hidden="true">★</span>
                                {{ number_format($rating, 1) }} &middot;
                                {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}
                            </span>

                            <span class="border border-white/30 px-2.5 py-1 text-forest-50">
                                Farming since {{ $farmer->created_at->format('F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <dl class="grid shrink-0 grid-cols-3 gap-3 border-t border-white/25 pt-5 sm:gap-4 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                    <div>
                        <dt class="font-mono text-[9px] uppercase tracking-label text-forest-200">Rating</dt>
                        <dd class="mt-1 font-display text-2xl font-medium text-white">{{ number_format($rating, 1) }}</dd>
                    </div>
                    <div>
                        <dt class="font-mono text-[9px] uppercase tracking-label text-forest-200">Delivered</dt>
                        <dd class="mt-1 font-display text-2xl font-medium text-white">{{ number_format($ordersFulfilled) }}</dd>
                    </div>
                    <div>
                        <dt class="font-mono text-[9px] uppercase tracking-label text-forest-200">Listings</dt>
                        <dd class="mt-1 font-display text-2xl font-medium text-white">{{ number_format($products->total()) }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <section>
                <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink pb-4">
                    <div>
                        <p class="eyebrow">At the stall</p>
                        <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">Fresh from the farm</h2>
                        <p class="mt-1 text-sm text-ink-soft">
                            {{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} available today
                        </p>
                    </div>
                    <a href="{{ route('home') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 underline-offset-4 hover:underline">Browse all produce</a>
                </div>

                @if ($products->isEmpty())
                    <div class="mt-6">
                        <x-empty-state
                            title="This farmer has nothing listed right now"
                            description="Check back soon, or wander the rest of the market.">
                            <a href="{{ route('home') }}" class="btn-primary">Browse the market</a>
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-8 grid gap-x-5 gap-y-8 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10 border-t border-dashed border-line-dark pt-6">{{ $products->links() }}</div>
                @endif
            </section>

            <aside class="space-y-5">
                <div class="relative border border-line bg-white p-5 shadow-sm">
                    <span class="tape -top-3 right-8 rotate-3" aria-hidden="true"></span>
                    <p class="eyebrow">The people</p>
                    <h2 class="mt-1 font-display text-2xl font-semibold">About this farm</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink-soft">
                        {{ $farmer->farmerProfile?->bio ?: 'This farmer has been selling fresh produce on the market since '.$farmer->created_at->format('F Y').'.' }}
                    </p>

                    <dl class="mt-4 space-y-3 border-t border-dashed border-line-dark pt-4 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Location</dt>
                            <dd class="text-right font-medium text-ink">
                                {{ $farmer->farmerProfile?->location ?: ($farmer->address ?: 'Bangladesh') }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Member since</dt>
                            <dd class="text-right font-medium text-ink">{{ $farmer->created_at->format('d M Y') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Reviews</dt>
                            <dd class="text-right font-medium text-ink">{{ number_format($reviewCount) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="border border-forest-800 bg-forest-800 p-5 text-forest-50">
                    <p class="eyebrow !text-ochre-300">Why buy direct?</p>
                    <h2 class="mt-1 font-display text-xl font-semibold text-white">The shorter the road, the fresher the food.</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li class="flex gap-2"><span class="text-ochre-300" aria-hidden="true">✓</span> Harvested to order, not warehoused</li>
                        <li class="flex gap-2"><span class="text-ochre-300" aria-hidden="true">✓</span> The farmer keeps the full product price</li>
                        <li class="flex gap-2"><span class="text-ochre-300" aria-hidden="true">✓</span> One transparent 5% platform fee</li>
                    </ul>
                </div>
            </aside>
        </div>

        @if ($reviews->isNotEmpty())
            <section class="mt-14 border-t-2 border-ink pt-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="eyebrow">From the table</p>
                        <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">What customers say</h2>
                        <p class="mt-1 text-sm text-ink-soft">Verified reviews for produce from {{ $farmer->storefront_name }}.</p>
                    </div>
                    <x-stars :rating="$rating" :count="$reviewCount" size="lg" />
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($reviews as $review)
                        <article class="border border-line bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid h-8 w-8 place-items-center rounded-full border border-forest-700/40 bg-forest-50 font-mono text-[10px] font-bold text-forest-800">
                                        {{ $review->user?->initials }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink">{{ $review->user?->name }}</p>
                                        <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ $review->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <x-stars :rating="$review->rating" />
                            </div>

                            @if ($review->body)
                                <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ $review->body }}</p>
                            @endif

                            @if ($review->product)
                                <a href="{{ route('products.show', $review->product) }}" class="mt-3 inline-flex font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">
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
