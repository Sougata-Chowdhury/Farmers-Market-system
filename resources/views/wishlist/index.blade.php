<x-app-layout title="Wishlist">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header :title="'Wishlist'" :subtitle="$products->count().' '.\Illuminate\Support\Str::plural('product', $products->count()).' saved for later'">
            <a href="{{ route('home') }}" class="btn-secondary">Continue shopping</a>
            @if ($products->isNotEmpty())
                <a href="{{ route('cart.index') }}" class="btn-primary">Go to cart</a>
            @endif
        </x-page-header>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state
                    title="Your wishlist is empty"
                    description="Tap the heart on any product to keep it here for when you are ready to order.">
                    <a href="{{ route('home') }}" class="btn-primary">Browse today's harvest</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif

        @if ($recentlyViewed->isNotEmpty())
            <section class="mt-12 border-t border-slate-200 pt-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Recently viewed</h2>
                        <p class="mt-1 text-sm text-slate-500">The last few products you looked at.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($recentlyViewed as $recentProduct)
                        <x-product-card :product="$recentProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
