<x-app-layout title="Your cart">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Your cart" :subtitle="number_format($totals['count']).' '.\Illuminate\Support\Str::plural('item', $totals['count']).' ready for checkout'">
            <a href="{{ route('home') }}" class="btn-secondary">Continue shopping</a>
        </x-page-header>

        @if ($items->isEmpty())
            <div class="mt-6">
                <x-empty-state title="Your cart is empty" description="Browse today's harvest and add something fresh to your basket.">
                    <a href="{{ route('home') }}" class="btn-primary">Browse produce</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
                <div class="card divide-y divide-slate-100">
                    @foreach ($items as $item)
                        @php($product = $item->product)
                        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                            <a href="{{ route('products.show', $product) }}" class="shrink-0">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                     onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                                     class="h-24 w-24 rounded-xl object-cover">
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 hover:text-emerald-700">
                                            {{ $product->name }}
                                        </a>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $product->category?->name }} &middot; <x-money :value="$product->price" /> per {{ $product->unit }}
                                        </p>
                                    </div>
                                    <button type="submit" form="remove-{{ $item->id }}" class="text-xs font-medium text-slate-400 transition hover:text-rose-600">
                                        Remove
                                    </button>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <form id="update-{{ $item->id }}" method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="qty-{{ $item->id }}" class="sr-only">Quantity for {{ $product->name }}</label>
                                        <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ $product->stock }}"
                                               value="{{ $item->quantity }}"
                                               class="input w-20 py-1.5 text-center">
                                        <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">Update</button>
                                    </form>

                                    <p class="ml-auto text-sm font-semibold text-slate-900">
                                        <x-money :value="(float) $product->price * $item->quantity" />
                                    </p>
                                </div>
                            </div>

                            <form id="remove-{{ $item->id }}" method="POST" action="{{ route('cart.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    @endforeach
                </div>

                <aside class="card sticky top-24 p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Order summary</h2>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        @if ($coupon)
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-emerald-800">Coupon {{ $coupon->code }}</p>
                                    <p class="mt-0.5 text-xs text-emerald-700">
                                        {{ $coupon->type->describe((float) $coupon->value) }} applied to this order.
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('coupons.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-slate-500 transition hover:text-rose-600">Remove</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('coupons.store') }}" class="flex gap-2">
                                @csrf
                                <label for="coupon_code" class="sr-only">Coupon code</label>
                                <input id="coupon_code" name="code" type="text" value="{{ old('code') }}"
                                       class="input py-1.5 uppercase placeholder:normal-case" placeholder="Coupon code" autocomplete="off">
                                <button type="submit" class="btn-secondary shrink-0 !py-1.5">Apply</button>
                            </form>
                            @error('code') <p class="field-error">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-medium text-slate-900"><x-money :value="$totals['subtotal']" /></dd>
                        </div>
                        @if ($totals['discount'] > 0)
                            <div class="flex items-center justify-between text-emerald-700">
                                <dt>Discount @if ($coupon) ({{ $coupon->code }}) @endif</dt>
                                <dd class="font-semibold">−<x-money :value="$totals['discount']" /></dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Platform fee ({{ (int) (config('marketplace.platform_fee') * 100) }}%)</dt>
                            <dd class="font-medium text-slate-900"><x-money :value="$totals['fee']" /></dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base">
                            <dt class="font-semibold text-slate-900">Total</dt>
                            <dd class="font-bold text-slate-900"><x-money :value="$totals['total']" /></dd>
                        </div>
                    </dl>

                    <a href="{{ route('checkout.index') }}" class="btn-primary mt-5 w-full">Proceed to checkout</a>

                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Your cart is re-priced and stock is verified again when the order is placed.
                    </p>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
