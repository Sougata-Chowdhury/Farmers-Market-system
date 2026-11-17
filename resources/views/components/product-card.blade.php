@props(['product'])

<article class="card group flex flex-col overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
    <div class="relative">
        <a href="{{ route('products.show', $product) }}" class="block aspect-[4/3] overflow-hidden bg-slate-100">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            @if (! $product->inStock())
                <span class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-semibold text-white">Out of stock</span>
            @elseif ($product->stock <= 5)
                <span class="absolute left-3 top-3 rounded-full bg-amber-500/90 px-2.5 py-1 text-xs font-semibold text-white">Only {{ $product->stock }} left</span>
            @endif
        </a>

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
                            class="grid h-9 w-9 place-items-center rounded-full bg-white/95 text-slate-600 shadow-sm ring-1 ring-slate-200 backdrop-blur transition hover:text-rose-600 hover:ring-rose-200"
                            aria-label="{{ $savedToWishlist ? 'Remove '.$product->name.' from wishlist' : 'Save '.$product->name.' to wishlist' }}"
                            title="{{ $savedToWishlist ? 'Remove from wishlist' : 'Save to wishlist' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $savedToWishlist ? 'currentColor' : 'none' }}"
                             stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </button>
                </form>
            @endif
        @endauth
    </div>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">{{ $product->category?->name }}</span>
            @if ($product->farmer?->slug)
                <a href="{{ route('farmers.show', $product->farmer) }}"
                   class="truncate text-slate-500 transition hover:text-emerald-700"
                   title="Visit {{ $product->farmer->name }}'s storefront">{{ $product->farmer->name }}</a>
            @else
                <span class="truncate text-slate-500">{{ $product->farmer?->name }}</span>
            @endif
        </div>

        <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-slate-900">
            <a href="{{ route('products.show', $product) }}" class="hover:text-emerald-700">{{ $product->name }}</a>
        </h3>

        <div class="mt-auto flex items-end justify-between gap-2">
            <p class="text-lg font-bold tracking-tight text-slate-900">
                <x-money :value="$product->price" />
                <span class="text-xs font-normal text-slate-500">/ {{ $product->unit }}</span>
            </p>
            <x-stars :rating="$product->reviews_avg_rating ?? 0" />
        </div>

        @auth
            @if (auth()->user()->isCustomer())
                <form method="POST" action="{{ route('cart.store', $product) }}" data-ajax-cart>
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-primary w-full" @disabled(! $product->inStock())>
                        {{ $product->inStock() ? 'Add to cart' : 'Out of stock' }}
                    </button>
                </form>
            @else
                <a href="{{ route('products.show', $product) }}" class="btn-secondary w-full">View product</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn-secondary w-full">Sign in to buy</a>
        @endauth
    </div>
</article>
