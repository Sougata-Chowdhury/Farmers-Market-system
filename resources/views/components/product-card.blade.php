@props(['product'])

<article class="group flex flex-col">
    <div class="relative border border-line bg-white p-2 shadow-sm transition duration-200 group-hover:shadow-md">
        <a href="{{ route('products.show', $product) }}" class="block aspect-[4/3] overflow-hidden border border-line/70 bg-paper-deep">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
        </a>

        @if (! $product->inStock())
            <span class="absolute left-4 top-4 border border-ink bg-ink px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-label text-paper">Sold out</span>
        @elseif ($product->stock <= 5)
            <span class="absolute left-4 top-4 border border-clay-600 bg-paper-light px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-label text-clay-700">
                Only {{ $product->stock }} left
            </span>
        @endif

        @auth
            @if (auth()->user()->isCustomer())
                @inject('wishlistCards', 'App\Services\WishlistService')
                @php($savedToWishlist = $wishlistCards->has(request(), $product))

                <form method="POST" action="{{ route($savedToWishlist ? 'wishlist.destroy' : 'wishlist.store', $product) }}"
                      class="absolute right-3 top-3">
                    @csrf
                    @if ($savedToWishlist)
                        @method('DELETE')
                    @endif
                    <button type="submit"
                            class="grid h-8 w-8 place-items-center border border-line bg-paper-light/95 text-ink-soft shadow-sm transition hover:border-clay-500 hover:text-clay-600"
                            aria-label="{{ $savedToWishlist ? 'Remove '.$product->name.' from wishlist' : 'Save '.$product->name.' to wishlist' }}"
                            title="{{ $savedToWishlist ? 'Remove from saved items' : 'Save for later' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $savedToWishlist ? 'currentColor' : 'none' }}"
                             stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </button>
                </form>
            @endif
        @endauth
    </div>

    <div class="flex flex-1 flex-col gap-1.5 pt-3">
        <div class="flex items-center justify-between gap-2 font-mono text-[9px] uppercase tracking-label">
            <span class="text-clay-600">{{ $product->category?->name }}</span>
            @if ($product->farmer?->slug)
                <a href="{{ route('farmers.show', $product->farmer) }}"
                   class="truncate text-ink-faint transition hover:text-forest-700"
                   title="Visit {{ $product->farmer->name }}'s storefront">&mdash; {{ $product->farmer->name }}</a>
            @else
                <span class="truncate text-ink-faint">{{ $product->farmer?->name }}</span>
            @endif
        </div>

        <h3 class="font-display text-[1.35rem] font-medium leading-snug text-ink">
            <a href="{{ route('products.show', $product) }}" class="decoration-clay-500/50 decoration-2 underline-offset-4 hover:underline">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex items-end justify-between gap-2 border-t border-dashed border-line-dark pt-2.5">
            <p class="font-display text-xl font-semibold text-ink">
                <x-money :value="$product->price" />
                <span class="font-mono text-[9px] font-normal uppercase tracking-label text-ink-faint">/ {{ $product->unit }}</span>
            </p>
            <x-stars :rating="$product->reviews_avg_rating ?? 0" />
        </div>

        @auth
            @if (auth()->user()->isCustomer())
                <form method="POST" action="{{ route('cart.store', $product) }}" data-ajax-cart class="mt-1">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-primary w-full !py-2 !text-[10px]" @disabled(! $product->inStock())>
                        {{ $product->inStock() ? 'Add to basket' : 'Out of stock' }}
                    </button>
                </form>
            @else
                <a href="{{ route('products.show', $product) }}" class="btn-secondary mt-1 w-full !py-2 !text-[10px]">View product</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn-secondary mt-1 w-full !py-2 !text-[10px]">Sign in to buy</a>
        @endauth
    </div>
</article>
