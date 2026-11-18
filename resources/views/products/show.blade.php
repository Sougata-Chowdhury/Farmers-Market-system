<x-app-layout :title="$product->name"
               :description="\Illuminate\Support\Str::limit(strip_tags($product->description ?: $product->name.' from '.$product->farmer->storefront_name), 155)"
               :image="$product->image_url"
               type="product">
    @push('meta')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 300),
                'image' => $product->image_url,
                'sku' => 'product-'.$product->id,
                'category' => $product->category?->name,
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (float) $product->price,
                    'priceCurrency' => config('marketplace.currency.code', 'BDT'),
                    'availability' => $product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url' => route('products.show', $product),
                ],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $product->farmer?->storefront_name ?? config('app.name'),
                ],
                ...($product->reviews_count > 0 ? [
                    'aggregateRating' => [
                        '@type' => 'AggregateRating',
                        'ratingValue' => round((float) $product->average_rating, 1),
                        'reviewCount' => (int) $product->reviews_count,
                    ],
                ] : []),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    @endpush
    @php($images = $product->galleryImages())

    <div class="mx-auto max-w-7xl px-4 pb-28 pt-8 sm:px-6 lg:px-8 lg:pb-8">
        <nav class="mb-7 flex flex-wrap items-center gap-2 font-mono text-[10px] uppercase tracking-label text-ink-faint" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-clay-600">The market</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="hover:text-clay-600">
                {{ $product->category->name }}
            </a>
            <span aria-hidden="true">/</span>
            <span class="truncate text-ink">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-[1.02fr_0.98fr] lg:gap-14">
            <div x-data="{ active: 0, images: @js($images) }" class="space-y-4">
                <div class="relative border-2 border-ink bg-white p-3 shadow-xl">
                    <span class="tape -top-3 left-8 -rotate-3" aria-hidden="true"></span>
                    <div class="overflow-hidden border border-line bg-paper-deep">
                        <img src="{{ $images[0]['full'] }}" :src="images[active]?.full" :alt="images[active]?.alt ?? @js($product->name)"
                             onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                             class="aspect-square w-full object-cover">
                    </div>
                    <p class="hand mt-2 px-1 pb-0.5 text-lg leading-tight text-ink-soft">
                        {{ $product->inStock() ? 'Picked to order — ' . $product->stock . ' ' . $product->unit . ' left' : 'Sold out for now' }}
                    </p>
                </div>

                @if (count($images) > 1)
                    <div class="grid grid-cols-5 gap-2" role="group" aria-label="Product photos">
                        <template x-for="(image, index) in images" :key="index">
                            <button type="button"
                                    x-on:click="active = index"
                                    class="overflow-hidden border bg-white p-1 transition focus:outline-none"
                                    :class="active === index ? 'border-2 border-clay-600' : 'border-line hover:border-ink'"
                                    :aria-label="'Show photo ' + (index + 1)"
                                    :aria-pressed="active === index">
                                <img :src="image.thumb" :alt="image.alt" class="aspect-square w-full object-cover" loading="lazy">
                            </button>
                        </template>
                    </div>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="tag !border-clay-600/50 !bg-clay-50 !text-clay-700">{{ $product->category->name }}</span>
                    @if ($product->inStock())
                        <span class="tag">{{ $product->stock }} {{ $product->unit }} in stock</span>
                    @else
                        <span class="tag !border-clay-600 !bg-clay-100 !text-clay-800">Out of stock</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-[2.3rem] font-semibold leading-[1.08] tracking-tight text-ink sm:text-[2.8rem]">
                    {{ $product->name }}
                </h1>

                <div class="mt-3 flex items-center gap-3">
                    <x-stars :rating="$product->average_rating" :count="$product->reviews_count" size="lg" />
                    <span class="text-line-dark" aria-hidden="true">|</span>
                    <span class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $product->reviews->count() }} {{ \Illuminate\Support\Str::plural('note', $product->reviews->count()) }}</span>
                </div>

                <div class="mt-5 flex flex-wrap items-baseline gap-x-3 gap-y-1 border-y border-dashed border-line-dark py-4">
                    <p class="font-display text-4xl font-semibold text-clay-600">
                        <x-money :value="$product->price" />
                    </p>
                    <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">per {{ $product->unit }} &middot; farmer's own price</p>
                </div>

                <div class="mt-5 border border-line bg-paper-light p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center border border-forest-900 bg-forest-700 font-mono text-sm font-bold text-white">
                                {{ $product->farmer->initials }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">Grown by</p>
                                <p class="truncate font-display text-lg font-semibold leading-tight text-ink">{{ $product->farmer->storefront_name }}</p>
                                @if ($product->farmer->address)
                                    <p class="truncate text-xs text-ink-soft">{{ $product->farmer->address }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($product->farmer->slug)
                            <a href="{{ route('farmers.show', $product->farmer) }}"
                               class="shrink-0 font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 underline decoration-clay-400 decoration-2 underline-offset-4 hover:text-forest-700">
                                Visit the farm
                            </a>
                        @endif
                    </div>
                </div>

                @if ($product->description)
                    <p class="mt-5 text-[15px] leading-relaxed text-ink-soft">{{ $product->description }}</p>
                @endif

                <div class="mt-6">
                    @auth
                        @if (auth()->user()->isCustomer())
                            @inject('wishlist', 'App\Services\WishlistService')
                            @php($savedToWishlist = $wishlist->has(request(), $product))

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                                <form id="buy-form" method="POST" action="{{ route('cart.store', $product) }}" data-ajax-cart class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-stretch">
                                    @csrf
                                    <div class="flex items-stretch border border-ink bg-white">
                                        <label for="quantity" class="flex items-center border-r border-line px-3 font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">Qty</label>
                                        <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}"
                                               value="1" @disabled(! $product->inStock())
                                               class="w-16 border-0 bg-transparent py-2.5 text-center text-sm font-semibold focus:ring-0">
                                        <span class="flex items-center border-l border-line px-3 font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $product->unit }}</span>
                                    </div>
                                    <button type="submit" class="btn-primary flex-1 !py-3" @disabled(! $product->inStock())>
                                        {{ $product->inStock() ? 'Add to basket' : 'Currently unavailable' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route($savedToWishlist ? 'wishlist.destroy' : 'wishlist.store', $product) }}" class="shrink-0">
                                    @csrf
                                    @if ($savedToWishlist)
                                        @method('DELETE')
                                    @endif
                                    <button type="submit" class="btn-secondary h-full w-full sm:w-auto" aria-pressed="{{ $savedToWishlist ? 'true' : 'false' }}">
                                        <svg class="h-4 w-4 {{ $savedToWishlist ? 'text-clay-600' : 'text-ink-faint' }}" viewBox="0 0 24 24"
                                             fill="{{ $savedToWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                        {{ $savedToWishlist ? 'Saved' : 'Save' }}
                                    </button>
                                </form>
                            </div>

                            <p class="mt-3 font-mono text-[10px] uppercase leading-relaxed tracking-label text-ink-faint">
                                A {{ (int) (config('marketplace.platform_fee') * 100) }}% platform fee is added at checkout. Stock is reserved when you order.
                            </p>
                        @else
                            <div class="border border-ochre-500/40 border-l-4 border-l-ochre-500 bg-ochre-50 px-4 py-3 text-sm text-ochre-900">
                                You are signed in as a {{ auth()->user()->role->label() }}. Switch to a customer account to place orders.
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary w-full sm:w-auto">Sign in to buy</a>
                        <p class="mt-3 text-xs text-ink-soft">New here? <a href="{{ route('register') }}" class="link">Create a customer account</a>.</p>
                    @endauth
                </div>
            </div>
        </div>

        <section class="mt-16 border-t-2 border-ink pt-8">
            <div class="grid gap-10 lg:grid-cols-[1.35fr_0.65fr]">
                <div>
                    <p class="eyebrow">From the table</p>
                    <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">What people are saying</h2>

                    <div class="mt-5 space-y-4">
                        @forelse ($product->reviews as $review)
                            <article class="relative border border-line bg-white p-5 shadow-sm">
                                <span class="absolute -top-2 left-6 h-4 w-12 -rotate-2 bg-paper-deep/80" aria-hidden="true"></span>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="grid h-8 w-8 place-items-center rounded-full border border-forest-700/40 bg-forest-50 font-mono text-[10px] font-bold text-forest-800">
                                            {{ \Illuminate\Support\Str::of($review->user->name)->explode(' ')->filter()->take(2)->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))->implode('') }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-ink">{{ $review->user->name }}</p>
                                            <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <x-stars :rating="$review->rating" />
                                </div>
                                @if ($review->body)
                                    <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ $review->body }}</p>
                                @endif
                            </article>
                        @empty
                            <div class="border border-dashed border-line-dark bg-paper-light px-5 py-10 text-center">
                                <p class="hand text-2xl text-ink-faint">No notes yet — be the first to write one.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="eyebrow">Add your note</p>
                    <h2 class="mt-1 font-display text-2xl font-semibold tracking-tight">
                        {{ $myReview ? 'Update your review' : 'Leave a review' }}
                    </h2>

                    @auth
                        @if (auth()->user()->isCustomer())
                            <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-4 space-y-4 border border-line bg-paper-light p-5 shadow-sm">
                                @csrf
                                <div>
                                    <label for="rating" class="label">Rating</label>
                                    <select id="rating" name="rating" class="input mt-1.5">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" @selected(old('rating', $myReview->rating ?? 5) == $i)>
                                                {{ str_repeat('★', $i) }} ({{ $i }})
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="body" class="label">Your note <span class="normal-case tracking-normal text-ink-faint">(optional)</span></label>
                                    <textarea id="body" name="body" rows="4" class="input mt-1.5"
                                              placeholder="How was the quality and freshness?">{{ old('body', $myReview->body ?? '') }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary w-full">
                                    {{ $myReview ? 'Update review' : 'Post review' }}
                                </button>
                            </form>
                        @else
                            <p class="mt-4 border border-line bg-paper-light p-5 text-sm text-ink-soft">
                                Only customer accounts can leave reviews.
                            </p>
                        @endif
                    @else
                        <p class="mt-4 border border-line bg-paper-light p-5 text-sm text-ink-soft">
                            <a href="{{ route('login') }}" class="link">Sign in</a> as a customer to leave a review.
                        </p>
                    @endauth
                </div>
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section class="mt-16 border-t border-line pt-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Same aisle</p>
                        <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">More {{ $product->category->name }}</h2>
                    </div>
                    <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 underline-offset-4 hover:underline">See the aisle</a>
                </div>

                <div class="mt-6 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($recentlyViewed->isNotEmpty())
            <section class="mt-16 border-t border-line pt-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Pick up where you left off</p>
                        <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight">Recently viewed</h2>
                    </div>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('wishlist.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 underline-offset-4 hover:underline">Your saved items</a>
                        @endif
                    @endauth
                </div>

                <div class="mt-6 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recentlyViewed as $recentProduct)
                        <x-product-card :product="$recentProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @auth
        @if (auth()->user()->isCustomer())
            <div class="fixed inset-x-0 bottom-0 z-30 border-t-2 border-ink bg-paper-light px-4 py-3 shadow-xl lg:hidden"
                 role="region" aria-label="Quick purchase">
                <div class="flex items-center gap-3">
                    <img src="{{ $images[0]['thumb'] }}" alt=""
                         onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                         class="h-11 w-11 shrink-0 border border-line object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-display text-sm font-semibold text-ink">{{ $product->name }}</p>
                        <p class="font-mono text-[10px] text-ink-soft">
                            <x-money :value="$product->price" />
                            <span class="text-ink-faint">/ {{ $product->unit }}</span>
                        </p>
                    </div>
                    <button type="submit" form="buy-form" class="btn-primary shrink-0" @disabled(! $product->inStock())>
                        {{ $product->inStock() ? 'Add to basket' : 'Sold out' }}
                    </button>
                </div>
            </div>
        @endif
    @endauth
</x-app-layout>
