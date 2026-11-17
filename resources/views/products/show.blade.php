<x-app-layout :title="$product->name">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-emerald-700">Shop</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="hover:text-emerald-700">
                {{ $product->category->name }}
            </a>
            <span aria-hidden="true">/</span>
            <span class="truncate font-medium text-slate-700">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2">
            <div class="card overflow-hidden">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                     class="aspect-square w-full object-cover">
            </div>

            <div class="flex flex-col">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">{{ $product->category->name }}</span>
                    @if ($product->inStock())
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                            {{ $product->stock }} {{ $product->unit }} in stock
                        </span>
                    @else
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 font-semibold text-rose-700">Out of stock</span>
                    @endif
                </div>

                <h1 class="mt-3 font-display text-3xl font-medium leading-tight text-slate-900 sm:text-4xl">
                    {{ $product->name }}
                </h1>

                <div class="mt-2 flex items-center gap-3">
                    <x-stars :rating="$product->average_rating" :count="$product->reviews_count" size="lg" />
                    <span class="text-sm text-slate-400">|</span>
                    <span class="text-sm text-slate-500">{{ $product->reviews->count() }} {{ \Illuminate\Support\Str::plural('review', $product->reviews->count()) }}</span>
                </div>

                <p class="mt-5 text-3xl font-bold tracking-tight text-slate-900">
                    <x-money :value="$product->price" />
                    <span class="text-sm font-normal text-slate-500">per {{ $product->unit }}</span>
                </p>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sold by</p>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-600 text-sm font-bold text-white">
                                {{ $product->farmer->initials }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $product->farmer->storefront_name }}</p>
                                @if ($product->farmer->address)
                                    <p class="truncate text-xs text-slate-500">{{ $product->farmer->address }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($product->farmer->slug)
                            <a href="{{ route('farmers.show', $product->farmer) }}"
                               class="shrink-0 text-sm font-semibold text-emerald-700 hover:underline">
                                Visit storefront
                            </a>
                        @endif
                    </div>
                </div>

                @if ($product->description)
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $product->description }}</p>
                @endif

                <div class="mt-6">
                    @auth
                        @if (auth()->user()->isCustomer())
                            @inject('wishlist', 'App\Services\WishlistService')
                            @php($savedToWishlist = $wishlist->has(request(), $product))

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <form method="POST" action="{{ route('cart.store', $product) }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                                    @csrf
                                    <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-sm">
                                        <label for="quantity" class="px-3 text-sm font-medium text-slate-600">Qty</label>
                                        <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}"
                                               value="1" @disabled(! $product->inStock())
                                               class="w-20 border-0 bg-transparent py-2.5 text-sm focus:ring-0">
                                        <span class="px-3 text-xs text-slate-500">{{ $product->unit }}</span>
                                    </div>
                                    <button type="submit" class="btn-primary flex-1" @disabled(! $product->inStock())>
                                        {{ $product->inStock() ? 'Add to cart' : 'Currently unavailable' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route($savedToWishlist ? 'wishlist.destroy' : 'wishlist.store', $product) }}" class="shrink-0">
                                    @csrf
                                    @if ($savedToWishlist)
                                        @method('DELETE')
                                    @endif
                                    <button type="submit" class="btn-secondary w-full sm:w-auto" aria-pressed="{{ $savedToWishlist ? 'true' : 'false' }}">
                                        <svg class="h-5 w-5 {{ $savedToWishlist ? 'text-rose-600' : 'text-slate-500' }}" viewBox="0 0 24 24"
                                             fill="{{ $savedToWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                        {{ $savedToWishlist ? 'Saved' : 'Save' }}
                                    </button>
                                </form>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                A {{ (int) (config('marketplace.platform_fee') * 100) }}% platform fee is added at checkout. Stock is reserved when you place the order.
                            </p>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                You are signed in as a {{ auth()->user()->role->label() }}. Switch to a customer account to place orders.
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-primary w-full sm:w-auto">Sign in to buy</a>
                        <p class="mt-3 text-xs text-slate-500">New here? <a href="{{ route('register') }}" class="link">Create a customer account</a>.</p>
                    @endauth
                </div>
            </div>
        </div>

        <section class="mt-12 grid gap-8 lg:grid-cols-[1.4fr_0.6fr]">
            <div>
                <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Reviews</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($product->reviews as $review)
                        <article class="card p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">
                                        {{ \Illuminate\Support\Str::of($review->user->name)->explode(' ')->filter()->take(2)->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))->implode('') }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $review->user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <x-stars :rating="$review->rating" />
                            </div>
                            @if ($review->body)
                                <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $review->body }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="card px-5 py-8 text-center text-sm text-slate-500">
                            No reviews yet. Be the first to share your experience.
                        </div>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">
                    {{ $myReview ? 'Update your review' : 'Leave a review' }}
                </h2>

                @auth
                    @if (auth()->user()->isCustomer())
                        <form method="POST" action="{{ route('reviews.store', $product) }}" class="card mt-4 space-y-4 p-5">
                            @csrf
                            <div>
                                <label for="rating" class="label">Rating</label>
                                <select id="rating" name="rating" class="input mt-1">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected(old('rating', $myReview->rating ?? 5) == $i)>
                                            {{ str_repeat('★', $i) }} ({{ $i }})
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="body" class="label">Comment <span class="text-slate-400">(optional)</span></label>
                                <textarea id="body" name="body" rows="4" class="input mt-1"
                                          placeholder="How was the quality and freshness?">{{ old('body', $myReview->body ?? '') }}</textarea>
                            </div>
                            <button type="submit" class="btn-primary w-full">
                                {{ $myReview ? 'Update review' : 'Post review' }}
                            </button>
                        </form>
                    @else
                        <div class="card mt-4 p-5 text-sm text-slate-500">
                            Only customer accounts can leave reviews.
                        </div>
                    @endif
                @else
                    <div class="card mt-4 p-5 text-sm text-slate-500">
                        <a href="{{ route('login') }}" class="link">Sign in</a> as a customer to leave a review.
                    </div>
                @endauth
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section class="mt-14">
                <div class="flex items-end justify-between gap-4">
                    <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">More from {{ $product->category->name }}</h2>
                    <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="text-sm font-medium text-emerald-700 hover:underline">View all</a>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($recentlyViewed->isNotEmpty())
            <section class="mt-14">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Recently viewed</h2>
                        <p class="mt-1 text-sm text-slate-500">Pick up where you left off.</p>
                    </div>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('wishlist.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Your wishlist</a>
                        @endif
                    @endauth
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recentlyViewed as $recentProduct)
                        <x-product-card :product="$recentProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
