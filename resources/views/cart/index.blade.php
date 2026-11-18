<x-app-layout title="Your basket">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Your basket" :subtitle="number_format($totals['count']).' '.\Illuminate\Support\Str::plural('item', $totals['count']).' picked and waiting at the counter.'">
            <a href="{{ route('home') }}" class="btn-secondary">Keep browsing</a>
        </x-page-header>

        @if ($items->isEmpty())
            <div class="mt-6">
                <x-empty-state title="Your basket is empty" description="Wander through today's harvest and tuck something fresh in here.">
                    <a href="{{ route('home') }}" class="btn-primary">Browse produce</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-7 grid gap-8 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
                <div class="border border-line bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-line px-5 py-3">
                        <p class="eyebrow">At the counter</p>
                        <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ number_format($totals['count']) }} {{ \Illuminate\Support\Str::plural('item', $totals['count']) }}</p>
                    </div>

                    <div class="divide-y divide-dashed divide-line-dark/70">
                        @foreach ($items as $item)
                            @php($product = $item->product)
                            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                                <a href="{{ route('products.show', $product) }}" class="shrink-0">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                         onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';"
                                         class="h-24 w-24 border border-line object-cover">
                                </a>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <a href="{{ route('products.show', $product) }}" class="font-display text-lg font-semibold leading-tight text-ink hover:text-clay-600">
                                                {{ $product->name }}
                                            </a>
                                            <p class="mt-1 font-mono text-[10px] uppercase tracking-label text-ink-faint">
                                                {{ $product->category?->name }} &middot; <x-money :value="$product->price" /> / {{ $product->unit }}
                                            </p>
                                        </div>
                                        <button type="submit" form="remove-{{ $item->id }}" class="font-mono text-[10px] uppercase tracking-label text-ink-faint transition hover:text-clay-600">
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
                                                   class="input w-20 !rounded-sm border-ink/40 py-1.5 text-center">
                                            <button type="submit" class="font-mono text-[10px] font-bold uppercase tracking-label text-forest-700 underline-offset-4 hover:underline">Update</button>
                                        </form>

                                        <p class="ml-auto font-display text-lg font-semibold text-ink">
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
                </div>

                <aside class="sticky top-28 border-2 border-ink bg-paper-light shadow-xl">
                    <div class="flex items-start justify-between border-b border-dashed border-line-dark px-5 py-4">
                        <div>
                            <p class="eyebrow">Order slip</p>
                            <h2 class="mt-1 font-display text-2xl font-semibold">The reckoning</h2>
                        </div>
                        <span class="stamp">No. {{ str_pad((string) $items->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="px-5 py-4">
                        <div class="border border-dashed border-line-dark bg-white p-3">
                            @if ($coupon)
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-mono text-[10px] font-bold uppercase tracking-label text-forest-700">Coupon {{ $coupon->code }}</p>
                                        <p class="mt-1 text-xs leading-relaxed text-ink-soft">
                                            {{ $coupon->type->describe((float) $coupon->value) }} applied to this order.
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('coupons.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-mono text-[9px] uppercase tracking-label text-ink-faint transition hover:text-clay-600">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('coupons.store') }}" class="flex gap-2">
                                    @csrf
                                    <label for="coupon_code" class="sr-only">Coupon code</label>
                                    <input id="coupon_code" name="code" type="text" value="{{ old('code') }}"
                                           class="input !rounded-sm py-1.5 uppercase placeholder:normal-case" placeholder="Coupon code" autocomplete="off">
                                    <button type="submit" class="btn-secondary shrink-0 !py-1.5">Apply</button>
                                </form>
                                @error('code') <p class="field-error">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        <dl class="mt-5 space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-ink-soft">Subtotal</dt>
                                <dd class="font-medium text-ink"><x-money :value="$totals['subtotal']" /></dd>
                            </div>
                            @if ($totals['discount'] > 0)
                                <div class="flex items-center justify-between text-forest-700">
                                    <dt>Discount @if ($coupon) ({{ $coupon->code }}) @endif</dt>
                                    <dd class="font-semibold">&minus;<x-money :value="$totals['discount']" /></dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <dt class="text-ink-soft">Platform fee ({{ (int) (config('marketplace.platform_fee') * 100) }}%)</dt>
                                <dd class="font-medium text-ink"><x-money :value="$totals['fee']" /></dd>
                            </div>
                            <div class="flex items-center justify-between border-t-2 border-ink pt-3">
                                <dt class="font-display text-lg font-semibold text-ink">Total</dt>
                                <dd class="font-display text-xl font-bold text-clay-600"><x-money :value="$totals['total']" /></dd>
                            </div>
                        </dl>

                        <a href="{{ route('checkout.index') }}" class="btn-primary mt-5 w-full">Proceed to checkout</a>

                        <p class="mt-3 font-mono text-[9px] uppercase leading-relaxed tracking-label text-ink-faint">
                            Your basket is re-priced and stock is checked again when the order is placed.
                        </p>
                    </div>

                    <p class="border-t border-dashed border-line-dark px-5 py-3 text-center font-mono text-[9px] uppercase tracking-label text-ink-faint">
                        Thank you &middot; grown local, sold honest
                    </p>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
