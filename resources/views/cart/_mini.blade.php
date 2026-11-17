@php($currency = config('marketplace.currency.symbol'))

@if ($items->isEmpty())
    <div class="flex h-full flex-col items-center justify-center px-6 py-16 text-center">
        <span class="grid h-14 w-14 place-items-center rounded-full bg-emerald-50 text-emerald-700">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.4 5.25m0 0 2.05 7.18a1.5 1.5 0 0 0 1.44 1.09h8.72a1.5 1.5 0 0 0 1.44-1.09L20.4 5.25H5.4Z" />
            </svg>
        </span>
        <p class="mt-4 font-display text-lg font-semibold text-slate-900">Your cart is empty</p>
        <p class="mt-1 text-sm text-slate-500">Add something fresh from today's harvest.</p>
        <a href="{{ route('home') }}" class="btn-primary mt-5">Browse produce</a>
    </div>
@else
    <ul class="divide-y divide-slate-100">
        @foreach ($items as $item)
            @php($product = $item->product)
            <li class="flex gap-3 px-4 py-3">
                <a href="{{ $product ? route('products.show', $product) : '#' }}" class="shrink-0">
                    <img src="{{ $product?->image_url ?? asset('images/product-placeholder.svg') }}"
                         alt="" class="h-16 w-16 rounded-xl object-cover ring-1 ring-slate-200">
                </a>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <p class="line-clamp-2 text-sm font-semibold text-slate-900">
                            {{ $product?->name ?? 'Unavailable product' }}
                        </p>
                        <form method="POST" action="{{ $product ? route('cart.destroy', $product) : '#' }}" data-ajax-cart>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 transition hover:text-rose-600" aria-label="Remove {{ $product?->name }} from cart">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $currency }}{{ number_format((float) ($product?->price ?? 0), 2) }} / {{ $product?->unit }}
                    </p>

                    <div class="mt-2 flex items-center justify-between gap-2">
                        <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white">
                            <form method="POST" action="{{ $product ? route('cart.update', $product) : '#' }}" data-ajax-cart>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                                <button type="submit" class="grid h-8 w-8 place-items-center text-slate-500 transition hover:text-emerald-700 disabled:opacity-40"
                                        aria-label="Decrease quantity" @disabled($item->quantity <= 1)>&minus;</button>
                            </form>

                            <span class="min-w-7 text-center text-sm font-semibold text-slate-900">{{ $item->quantity }}</span>

                            <form method="POST" action="{{ $product ? route('cart.update', $product) : '#' }}" data-ajax-cart>
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit" class="grid h-8 w-8 place-items-center text-slate-500 transition hover:text-emerald-700"
                                        aria-label="Increase quantity">+</button>
                            </form>
                        </div>

                        <p class="text-sm font-bold text-slate-900">
                            {{ $currency }}{{ number_format((float) ($product?->price ?? 0) * $item->quantity, 2) }}
                        </p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
        <dl class="space-y-1.5 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-slate-500">Subtotal</dt>
                <dd class="font-semibold text-slate-900">{{ $currency }}{{ number_format($totals['subtotal'], 2) }}</dd>
            </div>
            @if ($totals['discount'] > 0)
                <div class="flex items-center justify-between text-emerald-700">
                    <dt>Discount</dt>
                    <dd class="font-semibold">&minus;{{ $currency }}{{ number_format($totals['discount'], 2) }}</dd>
                </div>
            @endif
            <div class="flex items-center justify-between">
                <dt class="text-slate-500">Platform fee</dt>
                <dd class="font-medium text-slate-900">{{ $currency }}{{ number_format($totals['fee'], 2) }}</dd>
            </div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base">
                <dt class="font-semibold text-slate-900">Total</dt>
                <dd class="font-bold text-slate-900">{{ $currency }}{{ number_format($totals['total'], 2) }}</dd>
            </div>
        </dl>

        <div class="mt-4 grid gap-2">
            <a href="{{ route('checkout.index') }}" class="btn-primary w-full">Checkout</a>
            <a href="{{ route('cart.index') }}" class="btn-secondary w-full">View full cart</a>
        </div>
    </div>
@endif
